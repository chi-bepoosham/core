import cv2
from .core import load_modelll, predict_class

def get_man_body_type(image_path):

    image = cv2.imread(image_path)


    model_body_type = load_modelll('/var/www/deploy/models/body_type/models/man_body_type.h5', class_num=3, base_model="resnet101")


    body_type = predict_class(
        image,
        model=model_body_type,
        class_names=["0", "2", "5"],
        reso=300,
        model_name="bodytype"
    )

    return body_type

#test
#result = get_man_body_type("astinboland.jpg")
#print("Predicted body type:", result)
