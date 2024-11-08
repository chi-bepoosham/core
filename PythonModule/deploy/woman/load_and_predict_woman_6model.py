import cv2
from .core import load_modelll, predict_class

def process_six_model_predictions(image_path):
    image = cv2.imread(image_path)
    
    # Load models
    model_balted = load_modelll('/var/www/deploy/models/6model/belted.h5', class_num=2, base_model="resnet101")
    model_cowl = load_modelll('/var/www/deploy/models/6model/cowl.h5', class_num=2, base_model="resnet101")
    model_empire = load_modelll('/var/www/deploy/models/6model/empire.h5', class_num=2, base_model="resnet101")
    model_loose = load_modelll('/var/www/deploy/models/6model/loose.h5', class_num=2, base_model="resnet101")
    model_peplum = load_modelll('/var/www/deploy/models/6model/peplum.h5', class_num=2, base_model="resnet101")
    model_wrap = load_modelll('/var/www/deploy/models/6model/wrap.h5', class_num=2, base_model="resnet101")

    # Perform predictions
    results = {
        "balted": predict_class(image, model=model_balted, class_names=["balted", "notbalted"], reso=300, model_name="balted"),
        "cowl": predict_class(image, model=model_cowl, class_names=["cowl", "notcowl"], reso=300, model_name="cowl"),
        "empire": predict_class(image, model=model_empire, class_names=["empire", "notempire"], reso=300, model_name="empire"),
        "loose": predict_class(image, model=model_loose, class_names=["losse", "snatched"], reso=300, model_name="loose"),
        "wrap": predict_class(image, model=model_wrap, class_names=["notwrap", "wrap"], reso=300, model_name="wrap"),
        "peplum": predict_class(image, model=model_peplum, class_names=["notpeplum", "peplum"], reso=300, model_name="peplum")
    }
    
    return results

# Test
# result = process_six_model_predictions("chibeposham-Docker/deploy/man/astinboland.jpg")
# print(result)
