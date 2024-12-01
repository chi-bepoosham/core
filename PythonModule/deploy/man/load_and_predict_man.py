import cv2
from .core import load_modelll, predict_class, get_color_tone, mnist_prepar, predict_mnist
from .yolo import yolo

def process_clothing_image(img_path):
    img = cv2.imread(img_path)
    mnist_image = mnist_prepar(image=img)

    # Get color tone
    tone = get_color_tone(img)

    # YOLO detection for crop images
    crop_image_astin = yolo(model="astin", image_path=img_path)
    crop_image_yaghe = yolo(model="astin", image_path=img_path)

    # Load models
    model_astin = load_modelll('/var/www/deploy/models/astin/models/astinman.h5', class_num=3, base_model="resnet101")
    model_patern = load_modelll('/var/www/deploy/models/patern/models/petternman.h5', class_num=5, base_model="resnet101")
    model_paintane = load_modelll('/var/www/deploy/models/paintane/models/mard.h5', class_num=2, base_model="mobilenet")
    model_rise = load_modelll('/var/www/deploy/models/rise/models/riseeeeef.h5', class_num=2, base_model="resnet152")
    model_shalvar = load_modelll('/var/www/deploy/models/shalvar/models/menpants.h5', class_num=7, base_model="resnet101")
    model_mnist = load_modelll('/var/www/deploy/models/fasionmnist/mnist.h5', class_num=10, base_model="mnist")
    model_tarh_shalvar = load_modelll('/var/www/deploy/models/tarh_shalvar/models/mmpantsprint.h5', class_num=5, base_model="resnet101")
    model_skirt_pants = load_modelll('/var/www/deploy/models/skirt_pants/models/skirt_pants.h5', class_num=2, base_model="resnet101")
    model_yaghe = load_modelll("/var/www/deploy/models/yaghe/models/man_yaghe.h5", class_num=5, base_model="resnet101")

    # Perform predictions
    mnist_prediction = predict_mnist(mnist_image, model=model_mnist, class_names=[
        'T-shirt/top', 'Trouser', 'Pullover', 'Dress', 'Coat', 'Sandal', 'Shirt', 'Sneaker', 'Bag', 'Ankle boot'
    ])

    paintane = predict_class(img, model=model_paintane, class_names=["mbalatane", 'mpayintane'], reso=224, model_name="paintane")

    results = {
        "color_tone": tone,
        "mnist_prediction": mnist_prediction,
        "paintane": paintane
    }

    if paintane == "mbalatane":
        results["astin"] = predict_class(crop_image_astin, model=model_astin, class_names=["longsleeve", "shortsleeve", "sleeveless"], reso=300, model_name="astin")
        results["pattern"] = predict_class(img, model=model_patern, class_names=["amudi", "dorosht", "ofoghi", "riz", "sade"], reso=300, model_name="pattern")
        results["yaghe"] = predict_class(crop_image_yaghe, model=model_yaghe, class_names=["classic", "hoodie", "round", "turtleneck", "v_neck"], reso=300, model_name="yaghe")

    elif paintane == "mpayintane":
        results["rise"] = predict_class(img, model=model_rise, class_names=["highrise", "lowrise"], reso=300, model_name="rise")
        results["shalvar"] = predict_class(img, model=model_shalvar, class_names=["mbaggy", "mcargo", "mcargoshorts", "mmom", "mshorts", "mslimfit", "mstraight"], reso=300, model_name="noe shalvar")
        results["tarh_shalvar"] = predict_class(img, model=model_tarh_shalvar, class_names=["mpamudi", "mpdorosht", "mpofoghi", "mpriz", "mpsade"], reso=300, model_name="tarhshalvar")
        results["skirt_and_pants"] = predict_class(img, model=model_skirt_pants, class_names=["pants", "skirt"], reso=300, model_name="skirt and pants")

    return results

# Test
# result = process_clothing_image("/root/shalvarli.jpg")
# print(result)
