import os
import xgboost as xgb
import tensorflow as tf

# =========================
# Paths
# =========================
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MODELS_DIR = os.path.join(BASE_DIR, "models")

# =========================
# CNN Feature Extractor
# =========================
CNN_MODEL_PATH = os.path.join(MODELS_DIR, "efficientnet_feature_extractor.keras")

cnn_model = tf.keras.models.load_model(
    CNN_MODEL_PATH,
    compile=False
)
cnn_model.trainable = False
print("✅ CNN feature extractor loaded")

# =========================
# XGBoost loaders (BOOSTER API)
# =========================
def load_booster(path: str) -> xgb.Booster:
    if not os.path.exists(path):
        raise FileNotFoundError(f"Missing model: {path}")

    booster = xgb.Booster()
    booster.load_model(path)
    return booster


# ---------- KC MODEL ----------
kc_model = load_booster(
    os.path.join(MODELS_DIR, "kc_xgboost_model.json")
)
print("✅ KC booster loaded")


# ---------- SURGERY MODELS ----------
procedure_models = {}

for name in ["lasik", "prk", "intralase", "no_surgery"]:
    path = os.path.join(MODELS_DIR, f"{name}_suitability_xgb.json")
    procedure_models[name.upper()] = load_booster(path)
    print(f"✅ {name.upper()} booster loaded")




# from __future__ import annotations

# import os
# import numpy as np
# import xgboost as xgb
# import tensorflow as tf

# # =========================================================
# # Paths
# # =========================================================
# APP_DIR = os.path.dirname(os.path.abspath(__file__))
# PROJECT_ROOT = os.path.dirname(APP_DIR)
# MODELS_DIR = os.path.join(PROJECT_ROOT, "models")

# CNN_MODEL_PATH = os.path.join(MODELS_DIR, "efficientnet_feature_extractor.keras")
# KC_MODEL_PATH = os.path.join(MODELS_DIR, "kc_xgboost_model.json")

# PROC_MODEL_PATHS = {
#     "LASIK": os.path.join(MODELS_DIR, "lasik_suitability_xgb.json"),
#     "PRK": os.path.join(MODELS_DIR, "prk_suitability_xgb.json"),
#     "IntraLase": os.path.join(MODELS_DIR, "intralase_suitability_xgb.json"),
#     "NO_SURGERY": os.path.join(MODELS_DIR, "no_surgery_suitability_xgb.json"),
# }

# # =========================================================
# # CNN Feature Extractor (SAFE)
# # =========================================================
# cnn_model = tf.keras.models.load_model(
#     CNN_MODEL_PATH,
#     compile=False
# )
# cnn_model.trainable = False
# print("✅ CNN feature extractor loaded")

# # =========================================================
# # XGBoost models → LOAD AS BOOSTERS (CORRECT)
# # =========================================================
# kc_model = xgb.Booster()
# kc_model.load_model(KC_MODEL_PATH)
# print("✅ KC booster loaded")

# procedure_models: dict[str, xgb.Booster] = {}

# for name, path in PROC_MODEL_PATHS.items():
#     booster = xgb.Booster()
#     booster.load_model(path)
#     procedure_models[name] = booster
#     print(f"✅ {name} booster loaded")

# # =========================================================
# # Prediction helpers (THIS IS IMPORTANT)
# # =========================================================
# def predict_proba_booster(model: xgb.Booster, X: np.ndarray) -> np.ndarray:
#     """
#     X: shape (N, D)
#     returns probabilities
#     """
#     dmat = xgb.DMatrix(X)
#     preds = model.predict(dmat)

#     # Binary → convert to [P(class0), P(class1)]
#     if preds.ndim == 1:
#         return np.stack([1 - preds, preds], axis=1)

#     return preds



# # import xgboost as xgb
# # import tensorflow as tf
# # import os
# # from tensorflow.keras.applications import EfficientNetB0

# # cnn_model = EfficientNetB0(
# #     include_top=False,
# #     weights="imagenet",
# #     input_shape=(512, 512, 3),
# #     pooling="avg"
# # )

# # cnn_model.trainable = False


# # # ---------- CNN ----------
# # MODEL_DIR = os.path.join(os.path.dirname(__file__), "..", "models")

# # cnn_model = tf.keras.models.load_model(
# #     os.path.join(MODEL_DIR, "efficientnet_feature_extractor.keras"),
# #     compile=False
# # )
# # # cnn_model = tf.keras.applications.EfficientNetB0(
# # #     include_top=False,
# # #     weights="imagenet",
# # #     pooling="avg",
# # #     input_shape=(512, 512, 3)
# # # )

# # # ---------- XGBoost ----------
# # kc_model = xgb.XGBClassifier()
# # kc_model.load_model("models/kc_xgboost_model.json")

# # procedure_models = {}

# # for name in ["lasik", "prk", "intralase", "no_surgery"]:
# #     model = xgb.XGBClassifier()
# #     model.load_model(f"models/{name}_suitability_xgb.json")
# #     procedure_models[name.upper()] = model
