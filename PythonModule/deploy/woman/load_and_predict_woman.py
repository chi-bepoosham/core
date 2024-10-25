import tensorflow as tf
from keras.models import Model
import cv2
from keras.layers import Dense, GlobalAveragePooling2D
from keras.applications.resnet import ResNet101, preprocess_input
from keras.optimizers import SGD
from tensorflow.keras.utils import load_img, img_to_array  # تغییر این قسمت
import numpy as np
from main import load_modelll ,predict_class,get_color_tone
from  yolo import yolo

crop_image = yolo("test_folder",model="astin")
image  = cv2.imread( "F://chibeposham_team_git//astin//astinman//longsleeve//0ad79b50af0d9d398010f134fa5b91b8_crop_0_5.jpg")
tone = get_color_tone(image)
model_astin = load_modelll('F://chibeposham_team_git//astin//models//astinwoman.h5',class_num=6,base_model="resnet101")
model_patern = load_modelll('F://chibeposham_team_git//patern//models//petternwoman.h5',class_num=5 , base_model="resnet101")
model_paintane = load_modelll('F://chibeposham_team_git//paintane//models//zan.h5',class_num=3 , base_model="mobilenet")
model_rise = load_modelll('F://chibeposham_team_git//rise//models//riseeeeef.h5',class_num=2 , base_model="resnet152")
model_shalvar = load_modelll('F://chibeposham_team_git//shalvar//models//womenpants.h5',class_num=8, base_model="resnet101")








#astin_woman

predict_class(crop_image,model=model_astin,
              class_names=['bottompuffy',"fhalfsleeve","flongsleeve","fshortsleeve","fsleevess","toppuffy"],reso=300)


#patern_woman

predict_class(image,model=model_patern,
              class_names=["dorosht","rahrahamudi","rahrahofoghi","riz","sade"],reso=300)

#paintanezan
predict_class(image,model=model_paintane,
              class_names=["fbalatane","fpaintane","ftamamtane"],reso=224)



#rise
predict_class(image,model=model_rise,
              class_names=["highrise","lowrise"],reso=300)

#shalvar
predict_class(image,model=model_shalvar,
              class_names=["wbaggy","wbootcut","wcargo","wcargoshorts","wmom","wshorts","wskinny","wstraight"],reso=300)


