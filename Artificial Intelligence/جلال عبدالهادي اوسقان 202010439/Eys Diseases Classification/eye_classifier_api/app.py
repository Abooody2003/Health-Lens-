import io
import numpy as np
from fastapi import FastAPI, File, UploadFile
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from PIL import Image

import torch
import torch.nn.functional as F
import timm
import albumentations as A
from albumentations.pytorch import ToTensorV2


# -----------------------------
# Config
# -----------------------------
DEVICE = torch.device("cuda" if torch.cuda.is_available() else "cpu")
MODEL_PATH = "best_model.pth"

# IMPORTANT: keep order stable & explicit
# CLASSES = [
#     "Glaucoma",
#     "Cataract",
#     "Normal",
#     "Diabetic retinopathy",
# ]

CLASSES = ["Cataract", "Diabetic retinopathy", "Glaucoma", "Normal"]


IMG_SIZE = 384


# -----------------------------
# Model
# -----------------------------
num_classes = len(CLASSES)

model = timm.create_model(
    "efficientnet_b3",
    pretrained=False,
    num_classes=num_classes
)

state = torch.load(MODEL_PATH, map_location=DEVICE)
model.load_state_dict(state)
model.to(DEVICE)
model.eval()


# -----------------------------
# Preprocess (same as notebook)
# -----------------------------
transform = A.Compose([
    A.Resize(IMG_SIZE, IMG_SIZE),
    A.Normalize(
        mean=(0.485, 0.456, 0.406),
        std=(0.229, 0.224, 0.225)
    ),
    ToTensorV2()
])


# -----------------------------
# FastAPI
# -----------------------------
app = FastAPI(title="Eye Disease Prediction API")

# allow your frontend to call it
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # tighten later
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
def home():
    return {"status": "API is running"}

@app.post("/predict")
async def predict(file: UploadFile = File(...)):
    try:
        content = await file.read()
        img = Image.open(io.BytesIO(content)).convert("RGB")
        img = np.array(img)

        x = transform(image=img)["image"].unsqueeze(0).to(DEVICE)

        with torch.no_grad():
            outputs = model(x)
            probs = F.softmax(outputs, dim=1)
            conf, pred = torch.max(probs, 1)
            pred_idx = int(pred.item())

        return {
            "predicted_class": CLASSES[pred_idx],
            "confidence": float(conf.item())
        }

    except Exception as e:
        return JSONResponse(content={"error": str(e)}, status_code=500)
