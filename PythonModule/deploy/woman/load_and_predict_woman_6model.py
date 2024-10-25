import tensorflow as tf
from keras.models import Model
import cv2
from keras.layers import Dense, GlobalAveragePooling2D
from keras.applications.resnet import ResNet101, preprocess_input
from keras.optimizers import SGD
from tensorflow.keras.utils import load_img, img_to_array  # تغییر این قسمت
import numpy as np
from main import load_modelll ,predict_class
image  = cv2.imread( "F://chibeposham_team_git//astin//astinman//longsleeve//0ad79b50af0d9d398010f134fa5b91b8_crop_0_5.jpg")


model_empire = load_modelll('F://chibeposham_team_git//6model//empire.h5',class_num=2, base_model="resnet101")
model_loose = load_modelll('F://chibeposham_team_git//6model//loose.h5',class_num=2, base_model="resnet101")
model_peplumm = load_modelll('F://chibeposham_team_git//6model//peplumm.h5',class_num=2, base_model="resnet101")
model_wrap = load_modelll('F://chibeposham_team_git//6model//wrap.h5',class_num=2, base_model="resnet101")





predict_class(image,model=model_empire,
              class_names=["empire","_"],reso=300)

predict_class(image,model=model_loose,
              class_names=["losse","snatched"],reso=300)

predict_class(image,model=model_wrap,
              class_names=["_","wrap"],reso=300)

predict_class(image,model=model_peplumm,
              class_names=["_","peplum"],reso=300)




