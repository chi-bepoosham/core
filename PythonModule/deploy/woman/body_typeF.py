import cv2
from .core import load_modelll, predict_class
from deploy.human_detection import detect_human

def get_body_type_female(image_path):

    #human detection
    if not detect_human(image_path):
        return "No human detected"
    else:
        image = cv2.imread(image_path)


        model_body_type = load_modelll('/var/www/deploy/models/body_type/models/woman_body_type.h5', class_num=5, base_model="resnet101")

        body_type = predict_class(
            image,
            model=model_body_type,
            class_names=["11", "21", "31", "41", "51"],
            reso=300,
            model_name="bodytype"
        )

        return body_type

# test
#result = get_body_type_female("/root/chibeposham-Docker/deploy/01cca88ca885d54133b6420dccdce804_crop_172_21.jpg")
#print("Predicted body type:", result)
