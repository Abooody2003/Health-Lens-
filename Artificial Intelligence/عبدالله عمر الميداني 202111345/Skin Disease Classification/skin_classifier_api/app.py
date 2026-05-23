from fastapi import FastAPI, UploadFile, File
import tensorflow as tf
import numpy as np
from PIL import Image
import io

# --------------------
# CONFIG
# --------------------
IMG_SIZE = 224
MODEL_PATH = "ham10000_mobilenetv3_phone.keras"

CLASS_NAMES = [
    "Actinic keratoses",
    "Basal cell carcinoma",
    "Benign keratosis-like lesions",
    "Dermatofibroma",
    "Melanoma",
    "Melanocytic nevi",
    "Vascular lesions"
]

# --------------------
# LOAD MODEL
# --------------------
model = tf.keras.models.load_model(MODEL_PATH)
print("Model loaded successfully")

# --------------------
# FASTAPI APP
# --------------------
app = FastAPI(title="Skin Cancer Classifier API")

# --------------------
# IMAGE PREPROCESS
# --------------------
def preprocess_image(image_bytes):
    image = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    image = image.resize((IMG_SIZE, IMG_SIZE))
    image = np.array(image).astype(np.float32)

    image = tf.keras.applications.mobilenet_v3.preprocess_input(image)
    image = np.expand_dims(image, axis=0)
    return image

# --------------------
# ROUTES
# --------------------
@app.get("/")
def root():
    return {"message": "Skin classifier API is running"}

@app.post("/predict")
async def predict(file: UploadFile = File(...)):
    image_bytes = await file.read()
    image = preprocess_image(image_bytes)

    preds = model.predict(image)[0]

    top3_idx = np.argsort(preds)[-3:][::-1]

    results = []
    for idx in top3_idx:
        results.append({
            "disease": CLASS_NAMES[idx],
            "confidence": float(preds[idx])
        })

    return {
        "top_1": results[0],
        "top_3": results
    }
