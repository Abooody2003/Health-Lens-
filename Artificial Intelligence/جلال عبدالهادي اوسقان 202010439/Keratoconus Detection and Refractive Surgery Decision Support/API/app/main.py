from __future__ import annotations

import os
from typing import Dict, Optional, Tuple

import numpy as np
import cv2
import xgboost as xgb
from fastapi import FastAPI, File, Form, UploadFile, HTTPException
from pydantic import BaseModel
from app.features import extract_cnn_features, extract_classical_features

# =========================
# Config
# =========================
ROOT_DIR = os.path.dirname(os.path.abspath(__file__))
MODELS_DIR = os.path.join(os.path.dirname(ROOT_DIR), "models")

KC_MODEL_PATH = os.path.join(MODELS_DIR, "kc_xgboost_model.json")

PROC_MODEL_PATHS = {
    "LASIK": os.path.join(MODELS_DIR, "lasik_suitability_xgb.json"),
    "PRK": os.path.join(MODELS_DIR, "prk_suitability_xgb.json"),
    "IntraLase": os.path.join(MODELS_DIR, "intralase_suitability_xgb.json"),
    "NO_SURGERY": os.path.join(MODELS_DIR, "no_surgery_suitability_xgb.json"),
}

MAP_TYPES = ["anterior", "axial", "posterior", "pachy"]


# =========================
# Utilities
# =========================
async def _read_image_to_rgb(upload: UploadFile) -> np.ndarray:
    """
    Read UploadFile -> RGB uint8 image.
    (Async-safe)
    """
    data = await upload.read()
    if not data:
        raise ValueError("Empty image file")

    arr = np.frombuffer(data, dtype=np.uint8)
    img_bgr = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    if img_bgr is None:
        raise ValueError("Could not decode image")

    return cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)


def preprocess_orbscan_image(img_rgb: np.ndarray, target_size: Tuple[int, int] = (512, 512)) -> np.ndarray:
    """
    Output: float32 RGB in [0,1], shape (512,512,3)
    """
    img = cv2.resize(img_rgb, target_size)

    # denoise
    img = cv2.GaussianBlur(img, (3, 3), 0)
    img = cv2.bilateralFilter(img, 5, 75, 75)

    img_norm = img.astype(np.float32) / 255.0

    # auto-crop cornea region via threshold mask
    gray = cv2.cvtColor((img_norm * 255).astype(np.uint8), cv2.COLOR_RGB2GRAY)
    _, mask = cv2.threshold(gray, 10, 255, cv2.THRESH_BINARY)
    x, y, w, h = cv2.boundingRect(mask)

    # safety: if mask is tiny, keep original
    if w < 20 or h < 20:
        cropped = img_norm
    else:
        cropped = img_norm[y:y + h, x:x + w]

    cropped = cv2.resize(cropped, target_size)

    # CLAHE (LAB)
    lab = cv2.cvtColor((cropped * 255).astype(np.uint8), cv2.COLOR_RGB2LAB)
    L, A, B = cv2.split(lab)
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    L_eq = clahe.apply(L)
    lab_eq = cv2.merge([L_eq, A, B])
    enhanced = cv2.cvtColor(lab_eq, cv2.COLOR_LAB2RGB).astype(np.float32) / 255.0

    return enhanced


def munnerlyn_ablation(diopters: float, optical_zone_mm: float = 6.0) -> float:
    return (diopters * (optical_zone_mm ** 2)) / 3.0


def entropy_from_probs(p: np.ndarray) -> float:
    p = np.clip(p, 1e-8, 1.0)
    p = p / float(p.sum())
    return float(-np.sum(p * np.log(p)))


def validate_orbscan_image(img_uint8_rgb: np.ndarray) -> bool:
    """
    Heuristic Orbscan validation (NOT ML).
    Returns False if image is obviously not an Orbscan heatmap/map.
    """
    if img_uint8_rgb is None or img_uint8_rgb.ndim != 3 or img_uint8_rgb.shape[2] != 3:
        return False

    h, w, _ = img_uint8_rgb.shape

    # Orbscan maps are usually square-ish + decent resolution
    if h < 400 or w < 400:
        return False

    # Heatmaps are typically colorful (high variance)
    color_std = np.std(img_uint8_rgb.reshape(-1, 3), axis=0).mean()
    if color_std < 15:
        return False

    return True


# =========================
# Feature extraction
# =========================
def extract_fused_features(images_processed: Dict[str, np.ndarray], clinical: Dict[str, float]) -> np.ndarray:
    cnn_feats = []
    classical_feats = []

    for mt in MAP_TYPES:
        img = images_processed[mt]  # float32 RGB [0,1]
        cnn_feats.append(extract_cnn_features(img))
        classical_feats.append(extract_classical_features(img))

    cnn_vec = np.concatenate(cnn_feats)
    classical_vec = np.concatenate(classical_feats)

    clinical_vec = np.array(
        [
            clinical["age_years"],
            clinical["astig_value_D"],
            clinical["kmax_value_D"],
            clinical["pachy_central_um"],
        ],
        dtype=np.float32,
    )

    return np.concatenate([cnn_vec, classical_vec, clinical_vec])


# =========================
# Models (loaded once)
# =========================
def load_booster(path: str) -> xgb.Booster:
    if not os.path.exists(path):
        raise FileNotFoundError(f"Missing model file: {path}")
    booster = xgb.Booster()
    booster.load_model(path)
    return booster


kc_model: xgb.Booster = load_booster(KC_MODEL_PATH)
procedure_models: Dict[str, xgb.Booster] = {name: load_booster(p) for name, p in PROC_MODEL_PATHS.items()}


# =========================
# Response Schemas
# =========================
class ConfidenceOut(BaseModel):
    max_score: float
    margin: float
    entropy: float


class SurgeryResponse(BaseModel):
    patient_code: Optional[str] = None
    eye: Optional[str] = None
    kc_probability: float
    decision_features: Dict[str, float]
    scores: Dict[str, float]
    recommended: str
    final_decision: str
    confidence: ConfidenceOut
    reason: str


# =========================
# API
# =========================
app = FastAPI(title="CornOrb Surgery Recommendation API", version="1.0.0")


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/predict/surgery", response_model=SurgeryResponse)
async def predict_surgery(
    # 4 images
    anterior: UploadFile = File(..., description="Orbscan anterior map image (png/jpg)"),
    axial: UploadFile = File(..., description="Orbscan axial map image (png/jpg)"),
    posterior: UploadFile = File(..., description="Orbscan posterior map image (png/jpg)"),
    pachy: UploadFile = File(..., description="Orbscan pachymetry map image (png/jpg)"),
    # clinical fields
    patient_code: Optional[str] = Form(None),
    eye: Optional[str] = Form(None, description="OD or OS"),
    age_years: float = Form(...),
    astig_value_D: float = Form(...),
    kmax_value_D: float = Form(...),
    pachy_central_um: float = Form(...),
    # optional parameters used ONLY as continuous features
    diopters: float = Form(3.0),
    optical_zone_mm: float = Form(6.0),
    flap_thickness_um: float = Form(110.0),
    # confidence thresholds
    min_confidence: float = Form(0.65),
    min_margin: float = Form(0.10),
    max_entropy: float = Form(1.20),
):
    # ---- read images ----
    try:
        imgs_raw = {
            "anterior": await _read_image_to_rgb(anterior),
            "axial": await _read_image_to_rgb(axial),
            "posterior": await _read_image_to_rgb(posterior),
            "pachy": await _read_image_to_rgb(pachy),
        }
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Image decode failed: {str(e)}")

    # ---- validate raw images BEFORE preprocessing ----
    for name, img_rgb in imgs_raw.items():
        if not validate_orbscan_image(img_rgb):
            raise HTTPException(
                status_code=422,
                detail={
                    "error": "INVALID_ORBSCAN_IMAGE",
                    "message": f"{name} image does not appear to be a valid Orbscan map. Please upload a correct Orbscan map image.",
                },
            )

    # ---- preprocess ----
    try:
        imgs_proc = {k: preprocess_orbscan_image(v) for k, v in imgs_raw.items()}
    except Exception as e:
        raise HTTPException(status_code=400, detail=f"Image preprocessing failed: {str(e)}")

    # ---- clinical dict ----
    clinical = {
        "age_years": float(age_years),
        "astig_value_D": float(astig_value_D),
        "kmax_value_D": float(kmax_value_D),
        "pachy_central_um": float(pachy_central_um),
    }

    # ---- fused features ----
    try:
        fused_vec = extract_fused_features(imgs_proc, clinical)
        fused_vec = np.asarray(fused_vec, dtype=np.float32).reshape(1, -1)
        # print("FUSED SHAPE:", fused_vec.shape)  # optional debug
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Feature extraction failed: {str(e)}")

    # ---- KC probability (Booster) ----
    dmatrix = xgb.DMatrix(fused_vec)
    kc_prob = float(kc_model.predict(dmatrix)[0])

    # ---- decision features (6-dim) ----
    cct = float(pachy_central_um)
    kmax = float(kmax_value_D)
    age = float(age_years)
    astig = float(astig_value_D)

    ablation = float(munnerlyn_ablation(diopters, optical_zone_mm=optical_zone_mm))
    rsb = float(cct - (flap_thickness_um + ablation))

    x_dec = np.array([[kc_prob, cct, kmax, rsb, age, astig]], dtype=np.float32)

    decision_features = {
        "kc_prob": kc_prob,
        "cct_um": cct,
        "kmax_D": kmax,
        "rsb_um": rsb,
        "age_years": age,
        "astig_D": astig,
    }

    # ---- per-procedure suitability (Booster) ----
    dmat_dec = xgb.DMatrix(x_dec)

    scores: Dict[str, float] = {}
    for proc_name, model in procedure_models.items():
        scores[proc_name] = float(model.predict(dmat_dec)[0])

    sorted_scores = sorted(scores.items(), key=lambda kv: kv[1], reverse=True)
    best_proc, best_score = sorted_scores[0]
    second_score = sorted_scores[1][1] if len(sorted_scores) > 1 else 0.0

    margin = float(best_score - second_score)
    ent = entropy_from_probs(np.array([s for _, s in sorted_scores], dtype=np.float32))

    # ---- abstain ----
    abstain = (best_score < min_confidence) or (margin < min_margin) or (ent > max_entropy)

    if abstain:
        final_decision = "REFER_TO_SPECIALIST"
        reason = "Low confidence (abstained)."
    else:
        final_decision = best_proc
        reason = "High confidence ML recommendation."

    return SurgeryResponse(
        patient_code=patient_code,
        eye=eye,
        kc_probability=kc_prob,
        decision_features=decision_features,
        scores=scores,
        recommended=best_proc,
        final_decision=final_decision,
        confidence=ConfidenceOut(max_score=float(best_score), margin=float(margin), entropy=float(ent)),
        reason=reason,
    )



# from __future__ import annotations

# import io
# import os
# from typing import Dict, Optional, Tuple

# import numpy as np
# import cv2
# import xgboost as xgb
# from fastapi import FastAPI, File, Form, UploadFile, HTTPException
# from pydantic import BaseModel, Field
# from app.features import extract_cnn_features, extract_classical_features



# # =========================
# # Config
# # =========================
# ROOT_DIR = os.path.dirname(os.path.abspath(__file__))
# MODELS_DIR = os.path.join(os.path.dirname(ROOT_DIR), "models")

# KC_MODEL_PATH = os.path.join(MODELS_DIR, "kc_xgboost_model.json")

# PROC_MODEL_PATHS = {
#     "LASIK": os.path.join(MODELS_DIR, "lasik_suitability_xgb.json"),
#     "PRK": os.path.join(MODELS_DIR, "prk_suitability_xgb.json"),
#     "IntraLase": os.path.join(MODELS_DIR, "intralase_suitability_xgb.json"),
#     "NO_SURGERY": os.path.join(MODELS_DIR, "no_surgery_suitability_xgb.json"),
# }

# MAP_TYPES = ["anterior", "axial", "posterior", "pachy"]


# # =========================
# # Utilities
# # =========================
# def _read_image_to_rgb(upload: UploadFile) -> np.ndarray:
#     """
#     Read UploadFile -> RGB uint8 image.
#     """
#     data = upload.file.read()
#     if not data:
#         raise ValueError("Empty image file")

#     arr = np.frombuffer(data, dtype=np.uint8)
#     img_bgr = cv2.imdecode(arr, cv2.IMREAD_COLOR)
#     if img_bgr is None:
#         raise ValueError("Could not decode image")

#     img_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
#     return img_rgb


# def preprocess_orbscan_image(img_rgb: np.ndarray, target_size: Tuple[int, int] = (512, 512)) -> np.ndarray:
#     """
#     Mirrors your notebook preprocessing (in spirit).
#     Output: float32 RGB in [0,1], shape (512,512,3).
#     """
#     img = cv2.resize(img_rgb, target_size)

#     # denoise (same idea as notebook)
#     img = cv2.GaussianBlur(img, (3, 3), 0)
#     img = cv2.bilateralFilter(img, 5, 75, 75)

#     img_norm = img.astype(np.float32) / 255.0

#     # auto-crop cornea region via threshold mask
#     gray = cv2.cvtColor((img_norm * 255).astype(np.uint8), cv2.COLOR_RGB2GRAY)
#     _, mask = cv2.threshold(gray, 10, 255, cv2.THRESH_BINARY)
#     x, y, w, h = cv2.boundingRect(mask)
#     cropped = img_norm[y:y + h, x:x + w]
#     cropped = cv2.resize(cropped, target_size)

#     # CLAHE (LAB)
#     lab = cv2.cvtColor((cropped * 255).astype(np.uint8), cv2.COLOR_RGB2LAB)
#     L, A, B = cv2.split(lab)
#     clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
#     L_eq = clahe.apply(L)
#     lab_eq = cv2.merge([L_eq, A, B])
#     enhanced = cv2.cvtColor(lab_eq, cv2.COLOR_LAB2RGB).astype(np.float32) / 255.0
#     return enhanced


# def munnerlyn_ablation(diopters: float, optical_zone_mm: float = 6.0) -> float:
#     """
#     Engineering feature. Not a rule. Used to compute rsb feature.
#     """
#     return (diopters * (optical_zone_mm ** 2)) / 3.0


# def entropy_from_probs(p: np.ndarray) -> float:
#     p = np.clip(p, 1e-8, 1.0)
#     p = p / float(p.sum())
#     return float(-np.sum(p * np.log(p)))

#     def validate_orbscan_image(img: np.ndarray) -> bool:
#     """
#     Heuristic Orbscan validation (NOT ML).
#     Returns False if image is obviously not an Orbscan map.
#     """
#     h, w, _ = img.shape

#     # Orbscan maps are square-ish and high resolution
#     if h < 400 or w < 400:
#         return False

#     # Orbscan maps are color-coded heatmaps (high color variance)
#     color_std = np.std(img.reshape(-1, 3), axis=0).mean()
#     if color_std < 15:   # tuned threshold
#         return False

#     return True



# # =========================
# # Feature extraction plug-in
# # =========================
# MAP_TYPES = ["anterior", "axial", "posterior", "pachy"]

# def extract_fused_features(images_processed, clinical):
#     cnn_feats = []
#     classical_feats = []

#     for mt in MAP_TYPES:
#         img = images_processed[mt]  # float32 RGB [0,1]

#         cnn_feats.append(extract_cnn_features(img))
#         classical_feats.append(extract_classical_features(img))

#     cnn_vec = np.concatenate(cnn_feats)
#     classical_vec = np.concatenate(classical_feats)

#     clinical_vec = np.array([
#         clinical["age_years"],
#         clinical["astig_value_D"],
#         clinical["kmax_value_D"],
#         clinical["pachy_central_um"],
#     ], dtype=np.float32)

#     fused = np.concatenate([cnn_vec, classical_vec, clinical_vec])

#     return fused

# # def extract_fused_features(
# #     images_processed: Dict[str, np.ndarray],
# #     clinical: Dict[str, float],
# # ) -> np.ndarray:
# #     """
# #     IMPORTANT:
# #     This must return the SAME 5857-dim fused vector shape you trained with.

# #     You already have this logic in the notebook (CNN features + classical + clinical).
# #     In FastAPI you will implement it by:
# #       - loading your CNN feature extractor (EfficientNetB0 include_top=False pooling='avg')
# #       - extracting 1280-d features per map type
# #       - extracting classical features per map type
# #       - concatenating + clinical columns (same order as training)
# #     """
# #     raise NotImplementedError(
# #         "You must implement extract_fused_features() using your trained pipeline. "
# #         "I kept it as a clean plug-in to avoid you rewriting everything."
# #     )


# # =========================
# # Models (loaded once)
# # =========================
# def load_booster(path: str) -> xgb.Booster:
#     if not os.path.exists(path):
#         raise FileNotFoundError(f"Missing model file: {path}")
#     booster = xgb.Booster()
#     booster.load_model(path)
#     return booster

# kc_model: xgb.Booster = load_booster(KC_MODEL_PATH)

# procedure_models: Dict[str, xgb.Booster] = {
#     name: load_booster(path)
#     for name, path in PROC_MODEL_PATHS.items()
# }



# # =========================
# # Response Schemas
# # =========================
# class ConfidenceOut(BaseModel):
#     max_score: float
#     margin: float
#     entropy: float


# class SurgeryResponse(BaseModel):
#     patient_code: Optional[str] = None
#     eye: Optional[str] = None
#     kc_probability: float
#     decision_features: Dict[str, float]
#     scores: Dict[str, float]
#     recommended: str
#     final_decision: str
#     confidence: ConfidenceOut
#     reason: str


# # =========================
# # API
# # =========================
# app = FastAPI(title="CornOrb Surgery Recommendation API", version="1.0.0")


# @app.get("/health")
# def health():
#     return {"status": "ok"}


# @app.post("/predict/surgery", response_model=SurgeryResponse)
# async def predict_surgery(
#     # 4 images
#     anterior: UploadFile = File(..., description="Orbscan anterior map image (png/jpg)"),
#     axial: UploadFile = File(..., description="Orbscan axial map image (png/jpg)"),
#     posterior: UploadFile = File(..., description="Orbscan posterior map image (png/jpg)"),
#     pachy: UploadFile = File(..., description="Orbscan pachymetry map image (png/jpg)"),

#     # clinical fields (Form = easiest with multipart)
#     patient_code: Optional[str] = Form(None),
#     eye: Optional[str] = Form(None, description="OD or OS"),
#     age_years: float = Form(...),
#     astig_value_D: float = Form(...),
#     kmax_value_D: float = Form(...),
#     pachy_central_um: float = Form(...),

#     # optional parameters used ONLY as continuous features (not rules)
#     diopters: float = Form(3.0),
#     optical_zone_mm: float = Form(6.0),
#     flap_thickness_um: float = Form(110.0),

#     # confidence-aware abstention thresholds
#     min_confidence: float = Form(0.65),
#     min_margin: float = Form(0.10),
#     max_entropy: float = Form(1.20),
# ):
#     # ---- read + preprocess 4 images ----
#     try:
#         imgs_raw = {
#             "anterior": _read_image_to_rgb(anterior),
#             "axial": _read_image_to_rgb(axial),
#             "posterior": _read_image_to_rgb(posterior),
#             "pachy": _read_image_to_rgb(pachy),
#         }
#         imgs_proc = {k: preprocess_orbscan_image(v) for k, v in imgs_raw.items()}
#     except Exception as e:
#         raise HTTPException(status_code=400, detail=f"Image processing failed: {str(e)}")

#     # ---- build clinical dict ----
#     clinical = {
#         "age_years": float(age_years),
#         "astig_value_D": float(astig_value_D),
#         "kmax_value_D": float(kmax_value_D),
#         "pachy_central_um": float(pachy_central_um),
#     }

#     # ---- fused features (5857-dim) ----
#     # Plug your pipeline here.
#     try:
#             fused_vec = extract_fused_features(imgs_proc, clinical)  # shape (5857,)
#             # 🔴 ADD THIS LINE (TEMPORARY DEBUG)
#             print("FUSED SHAPE:", fused_vec.shape)

        
#             fused_vec = np.asarray(fused_vec, dtype=np.float32).reshape(1, -1)
#     except NotImplementedError as e:
#         # If you haven’t plugged the extractor yet, fail clearly.
#         raise HTTPException(status_code=501, detail=str(e))
#     except Exception as e:
#         raise HTTPException(status_code=500, detail=f"Feature extraction failed: {str(e)}")

#         for name, img in imgs_proc.items():
#     if not validate_orbscan_image((img * 255).astype(np.uint8)):
#         raise HTTPException(
#             status_code=422,
#             detail={
#                 "error": "INVALID_ORBSCAN_IMAGE",
#                 "message": f"{name} image does not appear to be a valid Orbscan map."
#             }
#         )


#     # ---- KC probability ----
#     # kc_prob = float(kc_model.predict_proba(fused_vec)[0, 1])
#     import xgboost as xgb

#     dmatrix = xgb.DMatrix(fused_vec)
#     kc_prob = float(kc_model.predict(dmatrix)[0])

#     # ---- decision features (6-dim) ----
#     cct = float(pachy_central_um)
#     kmax = float(kmax_value_D)
#     age = float(age_years)
#     astig = float(astig_value_D)

#     ablation = float(munnerlyn_ablation(diopters, optical_zone_mm=optical_zone_mm))
#     rsb = float(cct - (flap_thickness_um + ablation))

#     x_dec = np.array([[kc_prob, cct, kmax, rsb, age, astig]], dtype=np.float32)

#     dec_feat_names = ["kc_prob", "cct_um", "kmax_D", "rsb_um", "age_years", "astig_D"]
#     dec_feat_vals = [kc_prob, cct, kmax, rsb, age, astig]
#     decision_features = {k: float(v) for k, v in zip(dec_feat_names, dec_feat_vals)}

#     # ---- per-procedure suitability scoring (PURE ML) ----
#     scores: Dict[str, float] = {}

#     dmat_dec = xgb.DMatrix(x_dec)

#     for proc_name, model in procedure_models.items():
#         scores[proc_name] = float(model.predict(dmat_dec)[0])


#     # recommended = argmax score
#     sorted_scores = sorted(scores.items(), key=lambda kv: kv[1], reverse=True)
#     best_proc, best_score = sorted_scores[0]
#     second_score = sorted_scores[1][1] if len(sorted_scores) > 1 else 0.0

#     margin = float(best_score - second_score)
#     ent = entropy_from_probs(np.array([s for _, s in sorted_scores], dtype=np.float32))

#     # ---- confidence-aware abstention ----
#     abstain = (best_score < min_confidence) or (margin < min_margin) or (ent > max_entropy)

#     if abstain:
#         final_decision = "REFER_TO_SPECIALIST"
#         reason = "Low confidence (abstained)."
#     else:
#         final_decision = best_proc
#         reason = "High confidence ML recommendation."

#     return SurgeryResponse(
#         patient_code=patient_code,
#         eye=eye,
#         kc_probability=kc_prob,
#         decision_features=decision_features,
#         scores=scores,
#         recommended=best_proc,
#         final_decision=final_decision,
#         confidence=ConfidenceOut(
#             max_score=float(best_score),
#             margin=float(margin),
#             entropy=float(ent),
#         ),
#         reason=reason,
#     )










# # ==================================================================================
# # from fastapi import FastAPI, UploadFile, File, Form
# # from app.preprocessing import preprocess_image
# # from app.features import extract_cnn_features, extract_classical_features
# # from app.decision import recommend_surgery
# # import numpy as np

# # app = FastAPI(title="CornOrb AI API")

# # @app.get("/")
# # def root():
# #     return {"status": "CornOrb API running"}

# # @app.post("/predict")
# # async def predict(
# #     anterior: UploadFile = File(...),
# #     axial: UploadFile = File(...),
# #     posterior: UploadFile = File(...),
# #     pachy: UploadFile = File(...),

# #     age: float = Form(...),
# #     astig: float = Form(...),
# #     cct: float = Form(...),
# #     kmax: float = Form(...)
# # ):
# #     imgs = []
# #     for f in [anterior, axial, posterior, pachy]:
# #         imgs.append(preprocess_image(await f.read()))

# #     cnn_feats = []
# #     classical_feats = []

# #     for img in imgs:
# #         cnn_feats.append(extract_cnn_features(img))
# #         classical_feats.append(extract_classical_features(img))

# #     fused = np.concatenate(
# #         cnn_feats + classical_feats + [[age, astig, kmax, cct]],
# #         axis=0
# #     ).astype(np.float32)

# #     result = recommend_surgery(
# #         fused,
# #         clinical={
# #             "age": age,
# #             "astig": astig,
# #             "cct": cct,
# #             "kmax": kmax
# #         }
# #     )

# #     return result
