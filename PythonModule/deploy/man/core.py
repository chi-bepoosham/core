import tensorflow as tf
from keras.models import Model
from keras.layers import Dense, GlobalAveragePooling2D
from keras.applications.resnet import ResNet101, preprocess_input
from keras.optimizers import SGD
from tensorflow.keras.utils import load_img, img_to_array
import numpy as np
from keras.applications import ResNet152, MobileNetV2
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
        
        if base_model=="resnet152_600":


        # مشخصات ورودی‌ها
            reso = 300
            input_shape = (reso, reso, 3)

            # 1. بازسازی معماری مدل ResNet101
            input_tensor = tf.keras.layers.Input(shape=input_shape)
            base_model = ResNet152(weights=None, include_top=False, input_shape=input_shape, input_tensor=input_tensor)

            # افزودن لایه‌های بالا به مدل
            x = base_model.output
            x = GlobalAveragePooling2D()(x)
            x = Dense(600, activation='relu')(x)  # لایه Dense
            x = Dense(30,activation="relu")(x)
            predictions = Dense(class_num, activation='softmax')(x)  # لایه خروجی برای 11 کلاس

            # ساخت مدل کامل
            model = Model(inputs=base_model.input, outputs=predictions)

            # 2. لود وزن‌های ذخیره‌شده
            model.load_weights("{0}".format(model_path))
            return model

                
        if base_model=="mobilenet-v2":
        
            base_model = MobileNetV2(
            include_top=False,
            weights=None,  # Using trained weights from model_path
            input_shape=(224, 224, 3)
            )
            
            # Freezing the first 50 layers as in training
            for layer in base_model.layers[:50]:
                layer.trainable = False
            for layer in base_model.layers[50:]:
                layer.trainable = True

            # Adding custom layers
            x = base_model.output
            x = GlobalAveragePooling2D()(x)
            x = Dense(128, activation="relu")(x)
            x = Dropout(0.3)(x)
            output_layer = Dense(1, activation="sigmoid")(x)  # Change to 'sigmoid' if binary classification

            model = Model(inputs=base_model.input, outputs=output_layer)
            model.load_weights(model_path)
            
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
    image = cv2.resize(image, (224, 224))  # تغییر اندازه به 224x224
    if len(image.shape) == 2:  # اگر تصویر سیاه و سفید باشد
        image = np.expand_dims(image, axis=-1)  # اضافه کردن بعد کانالی
        image = np.repeat(image, 3, axis=-1)  # تبدیل به تصویر 3 کانالی
    image = np.expand_dims(image, axis=0)  # افزودن بعد batch
    return image.astype(np.float32) / 255.0  # نرمال‌سازی



def predict_mnist(prepare_output,model,class_names):
    predictions = model.predict(prepare_output)

    # Get predicted class
    predicted_class = np.argmax(predictions, axis=-1)

    # Display predicted class name
    predicted_label = class_names[predicted_class[0]]
    print(f"mnist: class prediction_name: {predicted_label}")
    return predicted_label


def prepare_image(img, target_size):
    img = cv2.resize(img,target_size)
    img_array = img_to_array(img)  # تبدیل به آرایه
    img_array = np.expand_dims(img_array, axis=0)  # افزودن بعد اضافی برای Batch
    img_array = preprocess_input(img_array)  # پیش‌پردازش تصویر برای ResNet
    return img_array


def get_color_tone(image):
    """
    Analyzes the lightness and saturation of the dominant color in an image.

    Parameters:
    - image: image object

    Returns:
    - tone
    """

    # Step 1: Convert the image from BGR to HSV color space
    hsv_image = cv2.cvtColor(image, cv2.COLOR_BGR2HSV)

    # Step 2: Calculate the dominant color
    pixels = hsv_image.reshape(-1, hsv_image.shape[-1])
    unique, counts = np.unique(pixels, axis=0, return_counts=True)
    dominant_index = np.argmax(counts)
    central_pixel_hsv = unique[dominant_index]

    # Step 3: Use the dominant color's saturation and value
    target_saturation = central_pixel_hsv[1]
    target_value = central_pixel_hsv[2]

    # Step 4: Define thresholds for lightness and saturation
    lightness_threshold = 127  # Adjust as needed
    saturation_threshold = 127  # Adjust as needed

    # Step 5: Determine lightness
    lightness = 'light' if target_value > lightness_threshold else 'dark'

    # Step 6: Determine saturation
    saturation = 'bright' if target_saturation > saturation_threshold else 'muted'

    tone = f"{lightness}_{saturation}"

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


