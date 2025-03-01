import os
import cv2
from .core import load_modelll, predict_class, get_color_tone, predict_mnist, mnist_prepar
from .yolo import yolo

# Define model paths

# For Docker
# model_astin_path_docker = '/var/www/deploy/models/astin/astinwoman.h5'
# model_patern_path_docker = '/var/www/deploy/models/pattern/petternwoman.h5'
# model_paintane_path_docker = '/var/www/deploy/models/paintane/zan.h5'
# model_rise_path_docker = '/var/www/deploy/models/rise/riseeeeef.h5'
# model_shalvar_path_docker = '/var/www/deploy/models/shalvar/womenpants.h5'
# model_tarh_shalvar_path_docker = '/var/www/deploy/models/tarh_shalvar/wwpantsprint.h5'
# model_skirt_pants_path_docker = '/var/www/deploy/models/skirt_pants/skirt_pants.h5'
# model_yaghe_path_docker = '/var/www/deploy/models/yaghe/yaghewoman101A.h5'
# model_skirt_print_path_docker = '/var/www/deploy/models/skirt_print/skirt_print.h5'
# model_skirt_type_path_docker = '/var/www/deploy/models/skirt_type/skirttt_types.h5'
# model_mnist_path_docker = '/var/www/deploy/models/under_over/under_over_mobilenet_final.h5'

# For Local
base_path = os.path.dirname(__file__)
model_astin_path = os.path.join(base_path, '../../models/astin/astinwoman.h5')
model_patern_path = os.path.join(base_path, '../../models/pattern/petternwoman.h5')
model_paintane_path = os.path.join(base_path, '../../models/paintane/zan.h5')  
model_rise_path = os.path.join(base_path, '../../models/rise/riseeeeef.h5') 
model_shalvar_path = os.path.join(base_path, '../../models/shalvar/womenpants.h5')
model_tarh_shalvar_path = os.path.join(base_path, '../../models/tarh_shalvar/wwpantsprint.h5')
model_skirt_pants_path = os.path.join(base_path, '../../models/skirt_pants/skirt_pants.h5')  
model_yaghe_path = os.path.join(base_path, '../../models/yaghe/yaghewoman101A.h5')
model_skirt_print_path = os.path.join(base_path, '../../models/skirt_print/skirt_print.h5')
model_skirt_type_path = os.path.join(base_path, '../../models/skirt_type/skirttt_types.h5') 
model_mnist_path = os.path.join(base_path, '../../models/under_over/under_over_mobilenet_final.h5')

# Load models globally
model_astin = load_modelll(model_astin_path, class_num=6, base_model="resnet101")
model_patern = load_modelll(model_patern_path, class_num=5, base_model="resnet101")
model_paintane = load_modelll(model_paintane_path, class_num=3, base_model="mobilenet-v2-pt")
model_rise = load_modelll(model_rise_path, class_num=2, base_model="resnet152_600")
model_shalvar = load_modelll(model_shalvar_path, class_num=8, base_model="resnet101")
model_tarh_shalvar = load_modelll(model_tarh_shalvar_path, class_num=5, base_model="resnet101")
model_skirt_pants = load_modelll(model_skirt_pants_path, class_num=2, base_model="resnet101")
model_yaghe = load_modelll(model_yaghe_path, class_num=11, base_model="resnet101")
model_skirt_print = load_modelll(model_skirt_print_path, class_num=5, base_model="resnet101_30_unit")
model_skirt_type = load_modelll(model_skirt_type_path, class_num=7, base_model="resnet101_30_unit")
model_mnist = load_modelll(model_mnist_path, class_num=2, base_model="mobilenet-v2")


def process_woman_clothing_image(image_path):
    """
    Processes a woman's clothing image to predict various attributes such as color tone,
    clothing type (paintane), sleeve type (astin), pattern, neckline (yaghe),
    rise, shalvar type, shalvar pattern, and skirt/pants classification.

    Args:
        image_path (str): The path to the clothing image file.

    Returns:
        dict: A dictionary containing the prediction results for various clothing attributes including:
            - color_tone: The dominant color tone of the clothing
            - mnist_prediction: Classification as "Under" or "Over" clothing
            - paintane: Clothing type (fbalatane, fpaintane, ftamamtane)
            - astin: Sleeve type (if applicable)
            - pattern: Pattern type of the clothing
            - yaghe: Neckline type (if applicable)
            - rise: Rise type for pants (if applicable)
            - shalvar: Pants/shalvar type (if applicable)
            - tarh_shalvar: Pants pattern (if applicable)
            - skirt_pants: Classification as skirt or pants (if applicable)
            - skirt_print: Skirt pattern (if applicable)
            - skirt_type: Skirt type (if applicable)
    """
    print(f"Processing image: {image_path}")
    results = {}

    # 1. Image Loading and Preprocessing
    image = cv2.imread(image_path)

    # 2. General Predictions (Independent of clothing type)
    # Predict color tone
    results["color_tone"] = get_color_tone(image)
    print(f"Color tone prediction: {results.get('color_tone')}")

    print(f"MNIST prediction: {results.get('mnist_prediction')}")

    # Predict clothing type (paintane)
    results["paintane"] = predict_class(image, model=model_paintane, 
                                       class_names=["fbalatane", "fpaintane", "ftamamtane"], 
                                       reso=224, model_name="paintane")
    print(f"Paintane prediction: {results.get('paintane')}")

    # 3. Conditional Predictions based on 'paintane' (Clothing Type)
    # Upper body or full body clothing
    if results["paintane"] in ["fbalatane", "ftamamtane"]:
        print("Detected 'fbalatane' or 'ftamamtane'. Proceeding with upper body predictions.")

        # MNIST prediction for under/over clothing
        mnist_image = mnist_prepar(image=image)
        results["mnist_prediction"] = predict_mnist(
            mnist_image, model=model_mnist, class_names=[
                "Under", "Over"
            ]
        )
        
        # Crop astin and yaghe
        crop_image_astin, crop_image_yaghe = yolo(image_path=image_path)
        print(f"YOLO detection completed. astin crop: {crop_image_astin is not None}, yaghe crop: {crop_image_yaghe is not None}")
        
        # Predict sleeve type (astin)
        results["astin"] = predict_class(crop_image_astin, model=model_astin, 
                                        class_names=['bottompuffy', "fhalfsleeve", "flongsleeve", "fshortsleeve", "fsleeveless", "toppuffy"], 
                                        reso=300, model_name="astin")
        print(f"Astin prediction: {results.get('astin')}")
        
        # Predict pattern
        results["pattern"] = predict_class(image, model=model_patern, 
                                          class_names=["dorosht", "rahrahamudi", "rahrahofoghi", "riz", "sade"], 
                                          reso=300, model_name="pattern")
        print(f"Pattern prediction: {results.get('pattern')}")
        
        # Predict neckline (yaghe)
        results["yaghe"] = predict_class(crop_image_yaghe, model=model_yaghe, 
                                        class_names=["boatneck", "classic", "halter", "hoodie", "of_the_shoulder", "one_shoulder", "round", "squer", "sweatheart", 'turtleneck', "v_neck"], 
                                        reso=300, model_name="yaghe")
        print(f"Yaghe prediction: {results.get('yaghe')}")

    # Lower body or full body clothing
    if results["paintane"] in ["fpaintane", "ftamamtane"]:
        print("Detected 'fpaintane' or 'ftamamtane'. Proceeding with lower body predictions.")
        
        # Predict rise
        results["rise"] = predict_class(image, model=model_rise, 
                                       class_names=["highrise", "lowrise"], 
                                       reso=300, model_name="rise")
        print(f"Rise prediction: {results.get('rise')}")
        
        # Predict shalvar type
        results["shalvar"] = predict_class(image, model=model_shalvar, 
                                          class_names=["wbaggy", "wbootcut", "wcargo", "wcargoshorts", "wmom", "wshorts", "wskinny", "wstraight"], 
                                          reso=300, model_name="noeshalvar")
        print(f"Shalvar prediction: {results.get('shalvar')}")
        
        # Predict shalvar pattern
        results["tarh_shalvar"] = predict_class(image, model=model_tarh_shalvar, 
                                               class_names=["wpamudi", "wpdorosht", "wpofoghi", "wpriz", "wpsade"], 
                                               reso=300, model_name="tarhshalvar")
        print(f"Tarh shalvar prediction: {results.get('tarh_shalvar')}")
        
        # Predict skirt or pants
        results["skirt_and_pants"] = predict_class(image, model=model_skirt_pants, 
                                                  class_names=["pants", "skirt"], 
                                                  reso=300, model_name="skirt and pants")
        print(f"Skirt and pants prediction: {results.get('skirt_and_pants')}")
        
        # Predict skirt print
        results["skirt_print"] = predict_class(image, model=model_skirt_print, 
                                              class_names=["skirtamudi", "skirtdorosht", "skirtofoghi", "skirtriz", "skirtsade"], 
                                              reso=300, model_name="skirt and pants")
        print(f"Skirt print prediction: {results.get('skirt_print')}")
        
        # Predict skirt type
        results["skirt_type"] = predict_class(image, model=model_skirt_type, 
                                             class_names=["alineskirt", "balloonskirt", "mermaidskirt", "miniskirt", "pencilskirt", "shortaskirt", "wrapskirt"], 
                                             reso=300, model_name="model_skirt_type")
        print(f"Skirt type prediction: {results.get('skirt_type')}")
    
    print("Returning results:")
    print(results)
    return results


def test_model_astin(image_path="../../image/sample_astin.jpg"):
    # Crop astin and yaghe
    crop_image_astin, _ = yolo(image_path=image_path)
    result = predict_class(crop_image_astin, model=model_astin,
                           class_names=['bottompuffy', "fhalfsleeve", "flongsleeve", "fshortsleeve", "fsleeveless", "toppuffy"], reso=300,
                           model_name="astin")
    print(f"Astin Prediction: {result}")


def test_model_patern(image_path="../../image/sample_patern.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_patern,
                           class_names=["dorosht", "rahrahamudi", "rahrahofoghi", "riz", "sade"], reso=300,
                           model_name="pattern")
    print(f"Patern Prediction: {result}")


def test_model_paintane(image_path="../../image/sample_paintane.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_paintane,
                           class_names=["fbalatane", "fpaintane", "ftamamtane"], reso=224,
                           model_name="paintane")
    print(f"Paintane Prediction: {result}")


def test_model_rise(image_path="../../image/sample_rise.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_rise,
                           class_names=["highrise", "lowrise"], reso=300,
                           model_name="rise")
    print(f"Rise Prediction: {result}")


def test_model_shalvar(image_path="../../image/sample_shalvar.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_shalvar,
                           class_names=["wbaggy", "wbootcut", "wcargo", "wcargoshorts", "wmom", "wshorts", "wskinny", "wstraight"], reso=300, model_name="noeshalvar")
    print(f"Shalvar Prediction: {result}")


def test_model_mnist(image_path="../../image/sample_mnist.jpg"):
    sample_image = cv2.imread(image_path)
    prepared_image = mnist_prepar(sample_image)
    result = predict_mnist(
        prepared_image, model=model_mnist, class_names=[
            "Under", "Over"
        ]
    )
    print(f"MNIST Prediction: {result}")


def test_model_tarh_shalvar(image_path="../../image/sample_tarh_shalvar.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_tarh_shalvar,
                           class_names=["wpamudi", "wpdorosht", "wpofoghi", "wpriz", "wpsade"],
                           reso=300, model_name="tarhshalvar")
    print(f"Tarh Shalvar Prediction: {result}")


def test_model_skirt_pants(image_path="../../image/sample_skirt_pants.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_skirt_pants,
                           class_names=["pants", "skirt"], reso=300, model_name="skirt and pants")
    print(f"Skirt and Pants Prediction: {result}")


def test_model_yaghe(image_path="../../image/sample_yaghe.jpg"):
    # Crop astin and yaghe
    _, crop_image_yaghe = yolo(image_path=image_path)
    result = predict_class(crop_image_yaghe, model=model_yaghe,
                           class_names=["boatneck", "classic", "halter", "hoodie", "of_the_shoulder", "one_shoulder", 
                                        "round", "squer", "sweatheart", 'turtleneck', "v_neck"], reso=300,
                           model_name="yaghe")
    print(f"Yaghe Prediction: {result}")

def test_model_skirt_print(image_path="../../image/sample_skirt_print.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_skirt_print, 
                           class_names=["skirtamudi", "skirtdorosht", "skirtofoghi", "skirtriz", "skirtsade"], 
                           reso=300, model_name="skirt and pants")
    print(f"Skirt Print Prediction: {result}")

def test_model_skirt_type(image_path="../../image/sample_skirt_print.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_skirt_type, 
                           class_names=["alineskirt", "balloonskirt", "mermaidskirt", "miniskirt", "pencilskirt", "shortaskirt", "wrapskirt"], 
                           reso=300, model_name="model_skirt_type")
    print(f"Skirt Print Prediction: {result}")
