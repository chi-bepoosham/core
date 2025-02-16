import os
import cv2
from .core import load_modelll, predict_class, get_color_tone, predict_mnist, mnist_prepar
from .yolo import yolo

# Define model paths

# For Docker
# model_astin_path_docker = '/var/www/deploy/models/astin/models/astinwoman.h5'
# model_patern_path_docker = '/var/www/deploy/models/patern/models/petternwoman.h5'
# model_paintane_path_docker = '/var/www/deploy/models/paintane/models/zan.h5'
# model_rise_path_docker = '/var/www/deploy/models/rise/models/riseeeeef.h5'
# model_shalvar_path_docker = '/var/www/deploy/models/shalvar/models/womenpants.h5'
# model_tarh_shalvar_path_docker = '/var/www/deploy/models/tarh_shalvar/models/wwpantsprint.h5'
# model_skirt_pants_path_docker = '/var/www/deploy/models/skirt_pants/models/skirt_pants.h5'
# model_yaghe_path_docker = '/var/www/deploy/models/yaghe/models/yaghewoman101A.h5'
# model_skirt_print_path_docker = '/var/www/deploy/models/skirt_print/models/skirt_print.h5'
# model_skirt_type_path_docker = '/var/www/deploy/models/skirt_type/models/skirttt_types.h5'
# model_mnist_path_docker = '/var/www/deploy/models/fasionmnist/mnist.h5'

# For Local
base_path = os.path.dirname(__file__)
model_astin_path = os.path.join(base_path, '../../models/astin/astinwoman.h5')
model_patern_path = os.path.join(base_path, '../../models/pattern/petternwoman.h5')
model_paintane_path = os.path.join(base_path, '../../models/paintane/zan.h5')  # FIXME: not available
model_rise_path = os.path.join(base_path, '../../models/rise/riseeeeef.h5')  # FIXME: low accuracy
model_shalvar_path = os.path.join(base_path, '../../models/shalvar/womenpants.h5')
model_tarh_shalvar_path = os.path.join(base_path, '../../models/tarh_shalvar/wwpantsprint.h5')
model_skirt_pants_path = os.path.join(base_path, '../../models/skirt_pants/skirt_pants.h5')  
model_yaghe_path = os.path.join(base_path, '../../models/yaghe/yaghewoman101A.h5')
model_skirt_print_path = os.path.join(base_path, '../../models/skirt_print/skirt_print.h5')
model_skirt_type_path = os.path.join(base_path, '../../models/skirt_type/skirttt_types.h5') 
model_mnist_path = os.path.join(base_path, '../../models/fasionmnist/mnist.h5')  # FIXME: not available


def process_woman_clothing_image(image_path):
    image = cv2.imread(image_path)
    mnist_image = mnist_prepar(image=image)

    # Color tone
    tone = get_color_tone(image)

    crop_image_astin, crop_image_yaghe = yolo(image_path=image_path)

    # Load models
    model_astin = load_modelll(model_astin_path, class_num=6, base_model="resnet101")
    model_patern = load_modelll(model_patern_path, class_num=5, base_model="resnet101")
    model_paintane = load_modelll(model_paintane_path, class_num=3, base_model="mobilenet")
    model_rise = load_modelll(model_rise_path, class_num=2, base_model="resnet152")
    model_shalvar = load_modelll(model_shalvar_path, class_num=8, base_model="resnet101")
    model_tarh_shalvar = load_modelll(model_tarh_shalvar_path, class_num=5, base_model="resnet101")
    model_skirt_pants = load_modelll(model_skirt_pants_path, class_num=2, base_model="resnet101")
    model_yaghe = load_modelll(model_yaghe_path, class_num=11, base_model="resnet101")
    model_skirt_print = load_modelll(model_skirt_print_path, class_num=5, base_model="resnet101_30_unit")
    model_skirt_type = load_modelll(model_skirt_type_path, class_num=7, base_model="resnet101_30_unit")
    model_mnist = load_modelll(model_mnist_path, class_num=10, base_model="mnist")

    # Perform predictions
    mnist_prediction = predict_mnist(
        mnist_image, model=model_mnist, class_names=[
            'T-shirt/top', 'Trouser', 'Pullover', 'Dress', 'Coat', 'Sandal', 'Shirt', 'Sneaker', 'Bag', 'Ankle boot'
        ]
    )

    paintanezan = predict_class(image, model=model_paintane, class_names=["fbalatane", "fpaintane", "ftamamtane"], reso=224, model_name="paintane")

    results = {
        "color_tone": tone,
        "mnist_prediction": mnist_prediction,
        "paintane": paintanezan
    }

    if paintanezan in ["fbalatane", "ftamamtane"]:
        results["astin"] = predict_class(crop_image_astin, model=model_astin, class_names=['bottompuffy', "fhalfsleeve", "flongsleeve", "fshortsleeve", "fsleeveless", "toppuffy"], reso=300, model_name="astin")
        results["pattern"] = predict_class(image, model=model_patern, class_names=["dorosht", "rahrahamudi", "rahrahofoghi", "riz", "sade"], reso=300, model_name="pattern")
        results["yaghe"] = predict_class(crop_image_yaghe, model=model_yaghe, class_names=["boatneck", "classic", "halter", "hoodie", "of_the_shoulder", "one_shoulder", "round", "squer", "sweatheart", 'turtleneck', "v_neck"], reso=300, model_name="yaghe")

    if paintanezan in ["fpaintane", "ftamamtane"]:
        results["rise"] = predict_class(image, model=model_rise, class_names=["highrise", "lowrise"], reso=300, model_name="rise")
        results["shalvar"] = predict_class(image, model=model_shalvar, class_names=["wbaggy", "wbootcut", "wcargo", "wcargoshorts", "wmom", "wshorts", "wskinny", "wstraight"], reso=300, model_name="noeshalvar")
        results["tarh_shalvar"] = predict_class(image, model=model_tarh_shalvar, class_names=["wpamudi", "wpdorosht", "wpofoghi", "wpriz", "wpsade"], reso=300, model_name="tarhshalvar")
        results["skirt_and_pants"] = predict_class(image, model=model_skirt_pants, class_names=["pants", "skirt"], reso=300, model_name="skirt and pants")
        results["skirt_print"] = predict_class(image, model=model_skirt_print, class_names=["skirtamudi", "skirtdorosht", "skirtofoghi", "skirtriz", "skirtsade"], reso=300, model_name="skirt and pants")
        results["skirt_type"] = predict_class(image, model=model_skirt_type, class_names=["alineskirt", "balloonskirt", "mermaidskirt", "miniskirt", "pencilskirt", "shortaskirt", "wrapskirt"], reso=300, model_name="model_skirt_type")

    return results


def test_model_astin(image_path="../../image/sample_astin.jpg"):
    model_astin = load_modelll(model_astin_path, class_num=6, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_astin,
                           class_names=['bottompuffy', "fhalfsleeve", "flongsleeve", "fshortsleeve", "fsleeveless", "toppuffy"], reso=300,
                           model_name="astin")
    print(f"Astin Prediction: {result}")


def test_model_patern(image_path="../../image/sample_patern.jpg"):
    model_patern = load_modelll(model_patern_path, class_num=5, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_patern,
                           class_names=["dorosht", "rahrahamudi", "rahrahofoghi", "riz", "sade"], reso=300,
                           model_name="pattern")
    print(f"Patern Prediction: {result}")


def test_model_paintane(image_path="../../image/sample_paintane.jpg"):
    model_paintane = load_modelll(model_paintane_path, class_num=3, base_model="mobilenet")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_paintane,
                           class_names=["fbalatane", "fpaintane", "ftamamtane"], reso=224,
                           model_name="paintane")
    print(f"Paintane Prediction: {result}")


def test_model_rise(image_path="../../image/sample_rise.jpg"):
    model_rise = load_modelll(model_rise_path, class_num=2, base_model="resnet152")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_rise,
                           class_names=["highrise", "lowrise"], reso=300,
                           model_name="rise")
    print(f"Rise Prediction: {result}")


def test_model_shalvar(image_path="../../image/sample_shalvar.jpg"):
    model_shalvar = load_modelll(model_shalvar_path, class_num=8, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_shalvar,
                           class_names=["wbaggy", "wbootcut", "wcargo", "wcargoshorts", "wmom", "wshorts", "wskinny", "wstraight"], reso=300, model_name="noeshalvar")
    print(f"Shalvar Prediction: {result}")


def test_model_mnist(image_path="../../image/sample_mnist.jpg"):
    model_mnist = load_modelll(model_mnist_path, class_num=10, base_model="mnist")
    sample_image = cv2.imread(image_path)
    prepared_image = mnist_prepar(sample_image)
    result = predict_mnist(prepared_image, model=model_mnist, class_names=[
        'T-shirt/top', 'Trouser', 'Pullover', 'Dress', 'Coat', 'Sandal', 'Shirt', 'Sneaker', 'Bag', 'Ankle boot'
    ])
    print(f"MNIST Prediction: {result}")


def test_model_tarh_shalvar(image_path="../../image/sample_tarh_shalvar.jpg"):
    model_tarh_shalvar = load_modelll(model_tarh_shalvar_path, class_num=5, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_tarh_shalvar,
                           class_names=["wpamudi", "wpdorosht", "wpofoghi", "wpriz", "wpsade"],
                           reso=300, model_name="tarhshalvar")
    print(f"Tarh Shalvar Prediction: {result}")


def test_model_skirt_pants(image_path="../../image/sample_skirt_pants.jpg"):
    model_skirt_pants = load_modelll(model_skirt_pants_path, class_num=2, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_skirt_pants,
                           class_names=["pants", "skirt"], reso=300, model_name="skirt and pants")
    print(f"Skirt and Pants Prediction: {result}")


def test_model_yaghe(image_path="../../image/sample_yaghe.jpg"):
    model_yaghe = load_modelll(model_yaghe_path, class_num=11, base_model="resnet101")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_yaghe,
                           class_names=["boatneck", "classic", "halter", "hoodie", "of_the_shoulder", "one_shoulder", "round", "squer", "sweatheart", 'turtleneck', "v_neck"], reso=300,
                           model_name="yaghe")
    print(f"Yaghe Prediction: {result}")

def test_model_skirt_print(image_path="../../image/sample_skirt_print.jpg"):
    model_skirt_print = load_modelll(model_skirt_print_path, class_num=5, base_model="resnet101_30_unit")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_skirt_print, 
                           class_names=["skirtamudi", "skirtdorosht", "skirtofoghi", "skirtriz", "skirtsade"], 
                           reso=300, model_name="skirt and pants")
    print(f"Skirt Print Prediction: {result}")

def test_model_skirt_type(image_path="../../image/sample_skirt_print.jpg"):
    model_skirt_type = load_modelll(model_skirt_type_path, class_num=7, base_model="resnet101_30_unit")
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_skirt_type, 
                           class_names=["alineskirt", "balloonskirt", "mermaidskirt", "miniskirt", "pencilskirt", "shortaskirt", "wrapskirt"], 
                           reso=300, model_name="model_skirt_type")
    print(f"Skirt Print Prediction: {result}")
