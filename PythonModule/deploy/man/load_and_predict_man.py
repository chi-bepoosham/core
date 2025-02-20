import os
import cv2
from .core import load_modelll, predict_class, get_color_tone, mnist_prepar, predict_mnist
from .yolo import yolo

# Define model paths
# For Docker
# model_astin_path_docker = '/var/www/deploy/models/astin/astinman.h5'
# model_patern_path_docker = '/var/www/deploy/models/patern/models/petternman.h5'
# model_paintane_path_docker = '/var/www/deploy/models/paintane/models/mard.h5'
# model_rise_path_docker = '/var/www/deploy/models/rise/models/riseeeeef.h5'
# model_shalvar_path_docker = '/var/www/deploy/models/shalvar/models/menpants.h5'
# model_mnist_path_docker = '/var/www/deploy/models/fasionmnist/mnist.h5'
# model_tarh_shalvar_path_docker = '/var/www/deploy/models/tarh_shalvar/models/mmpantsprint.h5'
# model_skirt_pants_path_docker = '/var/www/deploy/models/skirt_pants/models/skirt_pants.h5'
# model_yaghe_path_docker = '/var/www/deploy/models/yaghe/models/man_yaghe.h5'

# For Local
base_path = os.path.dirname(__file__)
model_astin_path = os.path.join(base_path, '../../models/astin/astinman.h5')
model_patern_path = os.path.join(base_path, '../../models/pattern/petternman.h5')
model_paintane_path = os.path.join(base_path, '../../models/paintane/mard.h5')
model_rise_path = os.path.join(base_path, '../../models/rise/riseeeeef.h5')  
model_shalvar_path = os.path.join(base_path, '../../models/shalvar/menpants.h5')
model_mnist_path = os.path.join(base_path, '../../models/under_over/under_over_mobilenet_final.h5')
model_tarh_shalvar_path = os.path.join(base_path, '../../models/tarh_shalvar/mmpantsprint.h5')
model_skirt_pants_path = os.path.join(base_path, '../../models/skirt_pants/skirt_pants.h5')
model_yaghe_path = os.path.join(base_path, '../../models/yaghe/man_yaghe.h5')  # FIXME: not available


def process_clothing_image(img_path):
    img = cv2.imread(img_path)
    mnist_image = mnist_prepar(image=img)

    # Determine the color tone of the image
    tone = get_color_tone(img)

    # Use YOLO to detect and crop specific parts of the image
    crop_image_astin, crop_image_yaghe = yolo(img_path)

    # Load various models for prediction
    model_astin = load_modelll(model_astin_path, class_num=3, base_model="resnet101")
    model_patern = load_modelll(model_patern_path, class_num=5, base_model="resnet101")
    model_paintane = load_modelll(model_paintane_path, class_num=2, base_model="mobilenet")
    model_rise = load_modelll(model_rise_path, class_num=2, base_model="resnet152_600")
    model_shalvar = load_modelll(model_shalvar_path, class_num=7, base_model="resnet101")
    model_mnist = load_modelll(model_mnist_path, class_num=2, base_model="mobilenet-v2")
    model_tarh_shalvar = load_modelll(model_tarh_shalvar_path, class_num=5, base_model="resnet101")
    model_skirt_pants = load_modelll(model_skirt_pants_path, class_num=2, base_model="resnet101")
    model_yaghe = load_modelll(model_yaghe_path, class_num=5, base_model="resnet101")

    # Perform predictions using the loaded models
    mnist_prediction = predict_mnist(mnist_image, model=model_mnist, class_names=[
        "Under", "Over"
    ])

    paintane = predict_class(img, model=model_paintane, class_names=["mbalatane", 'mpayintane'], reso=224,
                             model_name="paintane")

    results = {
        "color_tone": tone,
        "mnist_prediction": mnist_prediction,
        "paintane": paintane
    }

    # Additional predictions based on the paintane result
    if paintane == "mbalatane":
        results["astin"] = predict_class(crop_image_astin, model=model_astin,
                                         class_names=["longsleeve", "shortsleeve", "sleeveless"], reso=300,
                                         model_name="astin")
        results["pattern"] = predict_class(img, model=model_patern,
                                           class_names=["amudi", "dorosht", "ofoghi", "riz", "sade"], reso=300,
                                           model_name="pattern")
        results["yaghe"] = predict_class(crop_image_yaghe, model=model_yaghe,
                                         class_names=["classic", "hoodie", "round", "turtleneck", "v_neck"], reso=300,
                                         model_name="yaghe")

    elif paintane == "mpayintane":
        results["rise"] = predict_class(img, model=model_rise, class_names=["highrise", "lowrise"], reso=300,
                                        model_name="rise")
        results["shalvar"] = predict_class(img, model=model_shalvar,
                                           class_names=["mbaggy", "mcargo", "mcargoshorts", "mmom", "mshorts",
                                                        "mslimfit", "mstraight"], reso=300, model_name="noe shalvar")
        results["tarh_shalvar"] = predict_class(img, model=model_tarh_shalvar,
                                                class_names=["mpamudi", "mpdorosht", "mpofoghi", "mpriz", "mpsade"],
                                                reso=300, model_name="tarhshalvar")
        results["skirt_and_pants"] = predict_class(img, model=model_skirt_pants, class_names=["pants", "skirt"],
                                                   reso=300, model_name="skirt and pants")

    return results


def test_model_astin(image_path="../../image/sample_astin.jpg"):
    model_astin = load_modelll(model_astin_path, class_num=3, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_astin,
                           class_names=["longsleeve", "shortsleeve", "sleeveless"], reso=300,
                           model_name="astin")
    print(f"Astin Prediction: {result}")


def test_model_patern(image_path="../../image/sample_patern.jpg"):
    model_patern = load_modelll(model_patern_path, class_num=5, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_patern,
                           class_names=["amudi", "dorosht", "ofoghi", "riz", "sade"], reso=300,
                           model_name="pattern")
    print(f"Patern Prediction: {result}")


def test_model_paintane(image_path="../../image/sample_paintane.jpg"):
    model_paintane = load_modelll(model_paintane_path, class_num=2, base_model="mobilenet")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_paintane,
                           class_names=["mbalatane", 'mpayintane'], reso=224,
                           model_name="paintane")
    print(f"Paintane Prediction: {result}")


def test_model_rise(image_path="../../image/sample_rise.jpg"):
    model_rise = load_modelll(model_rise_path, class_num=2, base_model="resnet152_600")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_rise,
                           class_names=["highrise", "lowrise"], reso=300,
                           model_name="rise")
    print(f"Rise Prediction: {result}")


def test_model_shalvar(image_path="../../image/sample_shalvar.jpg"):
    model_shalvar = load_modelll(model_shalvar_path, class_num=7, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_shalvar,
                           class_names=["mbaggy", "mcargo", "mcargoshorts", "mmom", "mshorts",
                                        "mslimfit", "mstraight"], reso=300, model_name="noe shalvar")
    print(f"Shalvar Prediction: {result}")


def test_model_mnist(image_path="../../image/sample_mnist.jpg"):
    model_mnist = load_modelll(model_mnist_path, class_num=2, base_model="mobilenet-v2")
    sample_image = cv2.imread(image_path)
    prepared_image = mnist_prepar(sample_image)
    result = predict_mnist(
        prepared_image, model=model_mnist, class_names=[
            "Under", "Over"
        ]
    )
    print(f"MNIST Prediction: {result}")


def test_model_tarh_shalvar(image_path="../../image/sample_tarh_shalvar.jpg"):
    model_tarh_shalvar = load_modelll(model_tarh_shalvar_path, class_num=5, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_tarh_shalvar,
                           class_names=["mpamudi", "mpdorosht", "mpofoghi", "mpriz", "mpsade"],
                           reso=300, model_name="tarhshalvar")
    print(f"Tarh Shalvar Prediction: {result}")


def test_model_skirt_pants(image_path="../../image/sample_skirt_pants.jpg"):
    model_skirt_pants = load_modelll(model_skirt_pants_path, class_num=2, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_skirt_pants,
                           class_names=["pants", "skirt"], reso=300, model_name="skirt and pants")
    print(f"Skirt and Pants Prediction: {result}")


def test_model_yaghe(image_path="../../image/sample_yaghe.jpg"):
    model_yaghe = load_modelll(model_yaghe_path, class_num=5, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_yaghe,
                           class_names=["classic", "hoodie", "round", "turtleneck", "v_neck"], reso=300,
                           model_name="yaghe")
    print(f"Yaghe Prediction: {result}")
