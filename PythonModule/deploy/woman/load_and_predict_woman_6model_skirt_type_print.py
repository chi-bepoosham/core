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

model_balted = load_modelll('F://chibeposham_team_git//6model//belted.h5',class_num=2, base_model="resnet101")
model_cowl = load_modelll('F://chibeposham_team_git//6model//cowl.h5',class_num=2, base_model="resnet101")
model_skirt_print = load_modelll('F://chibeposham_team_git//skirt_print//models//skirt_print.h5',class_num=5, base_model="resnet101_30_unit")
model_skirt_type = load_modelll('F://chibeposham_team_git//skirt_type//models//skirttt_types.h5',class_num=7, base_model="resnet101_30_unit")






predict_class(image,model=model_balted,
              class_names=["balted","_"],reso=300)


predict_class(image,model=model_cowl,
              class_names=["cowl","_"],reso=300)

predict_class(image,model=model_skirt_print,
              class_names=["skirtamudi","skirtdorosht","skirtofoghi","skirtriz","skirtsade"],reso=300)

predict_class(image,model=model_skirt_type,
              class_names=["alineskirt","balloonskirt","mermaidskirt","miniskirt","pencilskirt","shortskirt","wrapskirt"],reso=300)




