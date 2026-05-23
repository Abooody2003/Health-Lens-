import numpy as np
from app.models import kc_model, procedure_models
import xgboost as xgb
import numpy as np

def munnerlyn_ablation(D, OZ=6.0):
    return (D * (OZ ** 2)) / 3.0

# def recommend_surgery(
#     fused_features,
#     clinical,
#     diopters=3.0,
#     confidence_threshold=0.65
# ):
    dmat = xgb.DMatrix(fused_features)
    kc_prob = float(kc_model.predict(dmat)[0])


    ablation = munnerlyn_ablation(diopters)
    rsb = clinical["cct"] - (110 + ablation)

    x_dec = np.array([[
        kc_prob,
        clinical["cct"],
        clinical["kmax"],
        rsb,
        clinical["age"],
        clinical["astig"]
    ]], dtype=np.float32)


    scores = {}

    dmatrix = xgb.DMatrix(x_dec)

    for name, model in procedure_models.items():
        prob = float(model.predict(dmatrix)[0])
    scores[name] = prob

    best, best_score = max(scores.items(), key=lambda x: x[1])

    if best_score < confidence_threshold:
        decision = "REFER_TO_SPECIALIST"
    else:
        decision = best

    return {
        "kc_probability": float(kc_prob),
        "procedure_scores": scores,
        "final_decision": decision,
        "max_confidence": best_score
    }
