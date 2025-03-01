import os
import cv2
from .core import load_modelll, predict_class

# Define model paths
# For Docker
# model_balted_path_docker = '/var/www/deploy/models/6model/belted.h5'
# model_cowl_path_docker = '/var/www/deploy/models/6model/cowl.h5'
# model_empire_path_docker = '/var/www/deploy/models/6model/empire.h5'
# model_loose_path_docker = '/var/www/deploy/models/6model/loose.h5'
# model_peplum_path_docker = '/var/www/deploy/models/6model/peplum.h5'
# model_wrap_path_docker = '/var/www/deploy/models/6model/wrap.h5'

# For Local
base_path = os.path.dirname(__file__)
model_balted_path = os.path.join(base_path, '../../models/6model/belted.h5')
model_cowl_path = os.path.join(base_path, '../../models/6model/cowl.h5')
model_empire_path = os.path.join(base_path, '../../models/6model/empire.h5')
model_loose_path = os.path.join(base_path, '../../models/6model/loose.h5')
model_peplum_path = os.path.join(base_path, '../../models/6model/peplum.h5')
model_wrap_path = os.path.join(base_path, '../../models/6model/wrap.h5')

# Load models globally
model_balted = load_modelll(model_balted_path, class_num=2, base_model="resnet101")
model_cowl = load_modelll(model_cowl_path, class_num=2, base_model="resnet101")
model_empire = load_modelll(model_empire_path, class_num=2, base_model="resnet101")
model_loose = load_modelll(model_loose_path, class_num=2, base_model="resnet101")
model_peplum = load_modelll(model_peplum_path, class_num=2, base_model="resnet101")
model_wrap = load_modelll(model_wrap_path, class_num=2, base_model="resnet101")

def process_six_model_predictions(image_path):
    """
    Processes a woman's clothing image to predict various style attributes.
    
    Args:
        image_path (str): The path to the clothing image file.
        
    Returns:
        dict: A dictionary containing the prediction results for various clothing attributes including:
            - balted: Whether the clothing is belted or not
            - cowl: Whether the clothing has a cowl neck or not
            - empire: Whether the clothing has an empire waist or not
            - loose: Whether the clothing is loose or snatched
            - wrap: Whether the clothing is wrap style or not
            - peplum: Whether the clothing has a peplum or not
    """
    print(f"Processing image for 6 model predictions: {image_path}")
    image = cv2.imread(image_path)
    
    # Perform predictions
    results = {}
    
    # Predict balted
    results["balted"] = predict_class(image, model=model_balted, 
                                     class_names=["balted", "notbalted"], 
                                     reso=300, model_name="balted")
    print(f"Balted prediction: {results.get('balted')}")
    
    # Predict cowl
    results["cowl"] = predict_class(image, model=model_cowl, 
                                   class_names=["cowl", "notcowl"], 
                                   reso=300, model_name="cowl")
    print(f"Cowl prediction: {results.get('cowl')}")
    
    # Predict empire
    results["empire"] = predict_class(image, model=model_empire, 
                                     class_names=["empire", "notempire"], 
                                     reso=300, model_name="empire")
    print(f"Empire prediction: {results.get('empire')}")
    
    # Predict loose
    results["loose"] = predict_class(image, model=model_loose, 
                                    class_names=["losse", "snatched"], 
                                    reso=300, model_name="loose")
    print(f"Loose prediction: {results.get('loose')}")
    
    # Predict wrap
    results["wrap"] = predict_class(image, model=model_wrap, 
                                   class_names=["notwrap", "wrap"], 
                                   reso=300, model_name="wrap")
    print(f"Wrap prediction: {results.get('wrap')}")
    
    # Predict peplum
    results["peplum"] = predict_class(image, model=model_peplum, 
                                     class_names=["notpeplum", "peplum"], 
                                     reso=300, model_name="peplum")
    print(f"Peplum prediction: {results.get('peplum')}")
    
    print("Returning 6 model results:")
    print(results)
    return results


def test_model_balted(image_path="../../image/sample_balted.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_balted,
                          class_names=["balted", "notbalted"], 
                          reso=300, model_name="balted")
    print(f"Balted Prediction: {result}")
    return result


def test_model_cowl(image_path="../../image/sample_cowl.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_cowl,
                          class_names=["cowl", "notcowl"], 
                          reso=300, model_name="cowl")
    print(f"Cowl Prediction: {result}")
    return result


def test_model_empire(image_path="../../image/sample_empire.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_empire,
                          class_names=["empire", "notempire"], 
                          reso=300, model_name="empire")
    print(f"Empire Prediction: {result}")
    return result


def test_model_loose(image_path="../../image/sample_loose.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_loose,
                          class_names=["losse", "snatched"], 
                          reso=300, model_name="loose")
    print(f"Loose Prediction: {result}")
    return result


def test_model_wrap(image_path="../../image/sample_wrap.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_wrap,
                          class_names=["notwrap", "wrap"], 
                          reso=300, model_name="wrap")
    print(f"Wrap Prediction: {result}")
    return result


def test_model_peplum(image_path="../../image/sample_peplum.jpg"):
    sample_image = cv2.imread(image_path)
    result = predict_class(sample_image, model=model_peplum,
                          class_names=["notpeplum", "peplum"], 
                          reso=300, model_name="peplum")
    print(f"Peplum Prediction: {result}")
    return result


def test_all_models_with_single_image(image_path):
    """
    Test all models one by one with a single image and verify their accuracy.
    
    Args:
        image_path: Path to the image to test
    """
    print(f"\n--- Testing all models with image: {image_path} ---\n")
    
    # Test each model and log results
    balted_result = test_model_balted(image_path)
    print(f"Balted test completed: {'✓' if balted_result else '✗'}")
    
    cowl_result = test_model_cowl(image_path)
    print(f"Cowl test completed: {'✓' if cowl_result else '✗'}")
    
    empire_result = test_model_empire(image_path)
    print(f"Empire test completed: {'✓' if empire_result else '✗'}")
    
    loose_result = test_model_loose(image_path)
    print(f"Loose test completed: {'✓' if loose_result else '✗'}")
    
    wrap_result = test_model_wrap(image_path)
    print(f"Wrap test completed: {'✓' if wrap_result else '✗'}")
    
    peplum_result = test_model_peplum(image_path)
    print(f"Peplum test completed: {'✓' if peplum_result else '✗'}")
    
    print("\n--- All tests completed ---\n")
    
    # Return all results in a dictionary
    return {
        "balted": balted_result,
        "cowl": cowl_result,
        "empire": empire_result,
        "loose": loose_result,
        "wrap": wrap_result,
        "peplum": peplum_result
    }

if __name__ == "__main__":
    # Default test image path
    test_image_path = "../../image/331ec239dda7104b199e2d39fcdb9e2c.jpg"
    
    # Run tests on all models with the default image
    results = test_all_models_with_single_image(test_image_path)
    
    # Print summary of results
    print("\n--- Results Summary ---")
    for model_name, result in results.items():
        print(f"{model_name.capitalize()}: {result}")

