import tensorflow as tf
import numpy as np
import cv2

def detect_human(image_path):

    model_path = '/var/www/deploy/models/human_detection.tflite'
    interpreter = tf.lite.Interpreter(model_path=model_path)
    interpreter.allocate_tensors()

    input_details = interpreter.get_input_details()
    output_details = interpreter.get_output_details()

    image = cv2.imread(image_path)
    resized_image = cv2.resize(image, (257, 257))
    normalized_image = resized_image / 255.0
    input_data = np.expand_dims(normalized_image, axis=0).astype(np.float32)

    interpreter.set_tensor(input_details[0]['index'], input_data)
    interpreter.invoke()
    output_mask = interpreter.get_tensor(output_details[0]['index'])

    class_mask = np.argmax(output_mask[0], axis=-1)
    if 15 in np.unique(class_mask):
        return True
    else:
        return False


