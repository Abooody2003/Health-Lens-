import numpy as np
import cv2
from skimage.feature import graycomatrix, graycoprops
import tensorflow as tf
from app.models import cnn_model

def extract_cnn_features(img):
    x = np.expand_dims(img, axis=0)
    x = tf.keras.applications.efficientnet.preprocess_input(x * 255.0)
    return cnn_model.predict(x, verbose=0)[0]

def extract_classical_features(img):
    feats = []
    gray = cv2.cvtColor((img * 255).astype(np.uint8), cv2.COLOR_RGB2GRAY)

    glcm = graycomatrix(
        gray,
        distances=[1,2,4],
        angles=[0, np.pi/4, np.pi/2, 3*np.pi/4],
        symmetric=True,
        normed=True
    )

    for p in ["contrast","dissimilarity","homogeneity","ASM","energy","correlation"]:
        feats.extend(graycoprops(glcm, p).flatten())

    for ch in cv2.split((img * 255).astype(np.uint8)):
        hist = cv2.calcHist([ch],[0],None,[32],[0,256]).flatten()
        feats.extend(hist)
        feats += [np.mean(ch), np.std(ch), np.max(ch), np.min(ch)]

    return np.array(feats, dtype=np.float32)
