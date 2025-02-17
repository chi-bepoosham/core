import tensorflow as tf
from keras.models import Model
from keras.layers import Dense, GlobalAveragePooling2D
from keras.applications.resnet import ResNet101, preprocess_input
from keras.optimizers import SGD
from tensorflow.keras.utils import load_img, img_to_array
import numpy as np
from keras.applications import ResNet152
import cv2
import keras
from keras.models import Sequential
from keras.layers import Dense, Dropout, Flatten
from keras.layers import Conv2D, MaxPooling2D, BatchNormalization




def load_modelll(model_path,class_num,base_model):

        if base_model=="resnet101":

            reso = 300
            input_shape = (reso, reso, 3)


            input_tensor = tf.keras.layers.Input(shape=input_shape)
            base_model = ResNet101(weights=None, include_top=False, input_shape=input_shape, input_tensor=input_tensor)


            x = base_model.output
            x = GlobalAveragePooling2D()(x)
            x = Dense(300, activation='relu')(x)  # لایه Dense
            predictions = Dense(class_num, activation='softmax')(x)

            # ساخت مدل کامل
            model = Model(inputs=base_model.input, outputs=predictions)

            # 2. لود وزن‌های ذخیره‌شده
            model.load_weights("{0}".format(model_path))
            return model

        if base_model=="mobilenet":


            base_model = tf.keras.applications.MobileNet(
            include_top=False,
            weights=None,  # استفاده از وزن‌های پیش‌ساخته
            input_shape=(224, 224, 3)
            )

            # افزودن لایه‌های جدید به مدل
            x = base_model.output
            x = tf.keras.layers.GlobalAveragePooling2D()(x)
            x = tf.keras.layers.Dropout(0.2)(x)
            x = tf.keras.layers.Dense(256, activation='relu')(x)
            x = tf.keras.layers.Dropout(0.5)(x)
            predictions = tf.keras.layers.Dense(class_num, activation='softmax')(x)

            model = tf.keras.Model(inputs=base_model.input, outputs=predictions)
            model.load_weights("{0}".format(model_path))
            return model


        if base_model=="resnet152":


        # مشخصات ورودی‌ها
            reso = 300
            input_shape = (reso, reso, 3)

            # 1. بازسازی معماری مدل ResNet101
            input_tensor = tf.keras.layers.Input(shape=input_shape)
            base_model = ResNet152(weights=None, include_top=False, input_shape=input_shape, input_tensor=input_tensor)

            # افزودن لایه‌های بالا به مدل
            x = base_model.output
            x = GlobalAveragePooling2D()(x)
            x = Dense(300, activation='relu')(x)  # لایه Dense
            x = Dense(30,activation="relu")(x)
            predictions = Dense(class_num, activation='softmax')(x)  # لایه خروجی برای 11 کلاس

            # ساخت مدل کامل
            model = Model(inputs=base_model.input, outputs=predictions)

            # 2. لود وزن‌های ذخیره‌شده
            model.load_weights("{0}".format(model_path))
            return model
        if base_model=="mnist":

            model = mnist_sequential((28,28,1))
            model.load_weights("{0}".format(model_path))
            return model








def predict_class(img, model,class_names,reso,model_name=None):

    reso = reso
    # آماده‌سازی تصویر
    img_array = prepare_image(img, target_size=(reso, reso))

    # انجام پیش‌بینی
    predictions = model.predict(img_array)

    # دریافت کلاس پیش‌بینی‌شده
    predicted_class = np.argmax(predictions, axis=-1)

    # لیست کلاس‌ها (این لیست باید با کلاس‌های دیتاست شما همخوانی داشته باشد)


    # نمایش نام کلاس پیش‌بینی‌شده
    predicted_label = class_names[predicted_class[0]]

    if model_name==None:

        print(f"{model.name}:class prediction_name: {predicted_label}")
    else:
        print(f"{model_name}:class prediction_name: {predicted_label}")

    return predicted_label



def mnist_prepar(image):
    image_fasion_mnist = image
    image_fasion_mnist = cv2.cvtColor(image_fasion_mnist,code=cv2.COLOR_BGR2GRAY)
    image_fasion_mnist = cv2.resize(image_fasion_mnist,(28,28))
    normalaze = image_fasion_mnist/255.0
    normalaze = np.expand_dims(normalaze, axis=0)
    return normalaze



def predict_mnist(prepare_output,model,class_names):


    predictions = model.predict(prepare_output)

    # دریافت کلاس پیش‌بینی‌شده
    predicted_class = np.argmax(predictions, axis=-1)

    # لیست کلاس‌ها (این لیست باید با کلاس‌های دیتاست شما همخوانی داشته باشد)


    # نمایش نام کلاس پیش‌بینی‌شده
    predicted_label = class_names[predicted_class[0]]
    print(f"mnist :class prediction_name: {predicted_label}")
    return predicted_label





def prepare_image(img, target_size):
    img = cv2.resize(img,target_size)
    img_array = img_to_array(img)  # تبدیل به آرایه
    img_array = np.expand_dims(img_array, axis=0)  # افزودن بعد اضافی برای Batch
    img_array = preprocess_input(img_array)  # پیش‌پردازش تصویر برای ResNet
    return img_array




def get_color_tone(image):


    # تغییر اندازه تصویر به 255x255
    image = cv2.resize(image, (255, 255))

    # تبدیل تصویر به مدل رنگی HSV
    hsv_image = cv2.cvtColor(image, cv2.COLOR_BGR2HSV)

    # جدا کردن کانال‌های Hue, Saturation, Value
    hue_channel = hsv_image[:, :, 0]
    saturation_channel = hsv_image[:, :, 1]
    value_channel = hsv_image[:, :, 2]

    # محاسبه میانگین برای هر کانال
    avg_hue = np.mean(hue_channel)
    avg_saturation = np.mean(saturation_channel)
    avg_value = np.mean(value_channel)

    # تشخیص طیف رنگ بر اساس Hue (رنگ اصلی)
    if 0 <= avg_hue <= 15 or 160 <= avg_hue <= 180:
        color = 'Red'
        color_bgr = (0, 0, 255)  # قرمز
    elif 15 < avg_hue <= 35:
        color = 'Yellow'
        color_bgr = (0, 255, 255)  # زرد
    elif 35 < avg_hue <= 85:
        color = 'Green'
        color_bgr = (0, 255, 0)  # سبز
    elif 85 < avg_hue <= 125:
        color = 'Blue'
        color_bgr = (255, 0, 0)  # آبی
    elif 125 < avg_hue <= 160:
        color = 'Purple'
        color_bgr = (255, 0, 255)  # بنفش
    else:
        color = 'Unknown'
        color_bgr = (255, 255, 255)  # سفید برای ناشناخته

    # تعیین نوع رنگ بر اساس Saturation (اشباع) و Value (روشنایی)
    if avg_value > 128:  # روشنایی بالا
        if avg_saturation > 128:  # اشباع بالا
            tone = 'light_bright'
        else:  # اشباع پایین
            tone = 'light_muted'
    else:  # روشنایی پایین
        if avg_saturation > 128:  # اشباع بالا
            tone = 'dark_bright'
        else:  # اشباع پایین
            tone = 'dark_muted'
    return tone



def mnist_sequential(input_shape):
    cnn4 = Sequential()
    cnn4.add(Conv2D(32, kernel_size=(3, 3), activation='relu', input_shape=input_shape))
    cnn4.add(BatchNormalization())

    cnn4.add(Conv2D(32, kernel_size=(3, 3), activation='relu'))
    cnn4.add(BatchNormalization())
    cnn4.add(MaxPooling2D(pool_size=(2, 2)))
    cnn4.add(Dropout(0.25))

    cnn4.add(Conv2D(64, kernel_size=(3, 3), activation='relu'))
    cnn4.add(BatchNormalization())
    cnn4.add(Dropout(0.25))

    cnn4.add(Conv2D(128, kernel_size=(3, 3), activation='relu'))
    cnn4.add(BatchNormalization())
    cnn4.add(MaxPooling2D(pool_size=(2, 2)))
    cnn4.add(Dropout(0.25))

    cnn4.add(Flatten())

    cnn4.add(Dense(512, activation='relu'))
    cnn4.add(BatchNormalization())
    cnn4.add(Dropout(0.5))

    cnn4.add(Dense(128, activation='relu'))
    cnn4.add(BatchNormalization())
    cnn4.add(Dropout(0.5))

    cnn4.add(Dense(10, activation='softmax'))
    return cnn4


