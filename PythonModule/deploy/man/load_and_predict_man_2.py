import tensorflow as tf
from keras.models import Model
import cv2
from keras.layers import Dense, GlobalAveragePooling2D
from keras.applications.resnet import ResNet101, preprocess_input
from keras.optimizers import SGD
from tensorflow.keras.utils import load_img, img_to_array  # تغییر این قسمت
import numpy as np
from main import load_modelll ,predict_class
from yolo import yolo
image  = cv2.imread( "F://chibeposham_team_git//astin//astinman//longsleeve//0ad79b50af0d9d398010f134fa5b91b8_crop_0_5.jpg")
crop_image = yolo("test_folder",model="yaghe")
model_tarh_shalvar = load_modelll('tarh_shalvar//models//mmpantsprint.h5',class_num=5, base_model="resnet101")
model_skirt_pants = load_modelll('F://chibeposham_team_git//skirt_pants//models//skirt_pants.h5',class_num=2, base_model="resnet101")
model_yaghe = load_modelll('F://chibeposham_team_git//yaghe//models//man_yaghe.h5',class_num=5, base_model="resnet101")
model_body_type = load_modelll('F://chibeposham_team_git//body_type//models//man_body_type.h5',class_num=3, base_model="resnet101")





#tarh_shalvar

predict_class(image,model=model_tarh_shalvar,
              class_names=["mpamudi","mpdorosht","mpofoghi","mpriz","mpsade"],reso=300)
#skirt_and_pants
predict_class(image,model=model_skirt_pants,
              class_names=["pants","skirt"],reso=300)
#yaghe
predict_class(crop_image,model=model_yaghe,
              class_names=["classic","hoodie","round","turtleneck","v_neck"],reso=300)
#body_type
predict_class(image,model=model_body_type,
              class_names=["0","2","5"],reso=300)





