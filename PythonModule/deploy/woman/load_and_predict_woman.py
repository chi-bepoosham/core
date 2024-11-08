import cv2
from .core import load_modelll, predict_class, get_color_tone, predict_mnist, mnist_prepar
from .yolo import yolo

def process_woman_clothing_image(image_path):
    image = cv2.imread(image_path)
    mnist_image = mnist_prepar(image=image)

    # Color tone
    tone = get_color_tone(image)

    # YOLO detections
    crop_image_astin = yolo(image_path=image_path, model="astin")
    crop_image_yaghe = yolo(image_path=image_path, model='yaghe')

    # Load models
    model_astin = load_modelll('/var/www/deploy/models/astin/models/astinwoman.h5', class_num=6, base_model="resnet101")
    model_patern = load_modelll('/var/www/deploy/models/patern/models/petternwoman.h5', class_num=5, base_model="resnet101")
    model_paintane = load_modelll('/var/www/deploy/models/paintane/models/zan.h5', class_num=3, base_model="mobilenet")
    model_rise = load_modelll('/var/www/deploy/models/rise/models/riseeeeef.h5', class_num=2, base_model="resnet152")
    model_shalvar = load_modelll('/var/www/deploy/models/shalvar/models/womenpants.h5', class_num=8, base_model="resnet101")
    model_tarh_shalvar = load_modelll('/var/www/deploy/models/tarh_shalvar/models/wwpantsprint.h5', class_num=5, base_model="resnet101")
    model_skirt_pants = load_modelll('/var/www/deploy/models/skirt_pants/models/skirt_pants.h5', class_num=2, base_model="resnet101")
    model_yaghe = load_modelll("/var/www/deploy/models/yaghe/models/yaghewoman101A.h5", class_num=11, base_model="resnet101")
    model_balted = load_modelll('/var/www/deploy/models/6model/belted.h5', class_num=2, base_model="resnet101")
    model_cowl = load_modelll('/var/www/deploy/models/6model/cowl.h5', class_num=2, base_model="resnet101")
    model_skirt_print = load_modelll('/var/www/deploy/models/skirt_print/models/skirt_print.h5', class_num=5, base_model="resnet101_30_unit")
    model_skirt_type = load_modelll('/var/www/deploy/models/skirt_type/models/skirttt_types.h5', class_num=7, base_model="resnet101_30_unit")
    model_mnist = load_modelll('/var/www/deploy/models/fasionmnist/mnist.h5', class_num=10, base_model="mnist")

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

# Test
# result = process_woman_clothing_image("/root/tshirt.jpg")
# print(result)
