import cv2
import numpy as np
from PIL import Image
import io

def preprocess_image(image_bytes):
    img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    img = img.resize((512, 512))
    img = np.array(img).astype(np.float32) / 255.0

    gray = cv2.cvtColor((img * 255).astype(np.uint8), cv2.COLOR_RGB2GRAY)
    _, mask = cv2.threshold(gray, 10, 255, cv2.THRESH_BINARY)
    x, y, w, h = cv2.boundingRect(mask)
    img = img[y:y+h, x:x+w]
    img = cv2.resize(img, (512, 512))

    return img
