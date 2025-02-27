
import os
import cv2
from ultralytics import YOLO


# for docker
# model_path = '/var/www/deploy/models/yolo/best.pt'

# for local
base_path = os.path.dirname(__file__)
model_path = os.path.join(base_path, '../../models/yolo/best.pt')

# Load YOLOv8 model
model = YOLO(model_path)
model.to("cpu")

def yolo(image_path, image=None):

    image_file = os.path.basename(image_path)  # Get image file name

    # Run YOLOv8 on the image
    results = model.predict(source=image_path, save=False)

    # Check if there are any detections
    if not results or not results[0].boxes:
        print(f"No detections found in {image_file}")
        return None, None

    # Load image for cropping
    if image is None:
        image = cv2.imread(image_path)
        if image is None:
            print(f"Failed to load image: {image_file}")
            return None, None

    # Initialize the cropped images for 'astin' and 'yaghe'
    crop_image_astin = None
    crop_image_yaghe = None

    # Loop through each detection and crop the image for 'astin' and 'yaghe'
    for result in results[0].boxes:
        x1, y1, x2, y2 = map(int, result.xyxy[0].tolist())
        label_index = int(result.cls[0])  # Get the class index
        label_name = model.names[label_index]  # Get class name from index

        # Crop the image for the detected box
        cropped_image = image[y1:y2, x1:x2]
        if cropped_image.size == 0:
            continue  # Skip empty crops

        # Check if the class is 'astin' or 'yaghe' and store the cropped image
        if label_name == 'sleeve' and crop_image_astin is None:
            crop_image_astin = cropped_image  # Save first detected 'astin'
        elif label_name == 'collar' and crop_image_yaghe is None:
            crop_image_yaghe = cropped_image  # Save first detected 'yaghe'
            
    # Return the cropped images (could be None if not found)
    return crop_image_astin, crop_image_yaghe


if __name__ == "__main__":
    import cv2

    image_path = os.path.join(base_path, "Screenshot_20240830-131947.jpg")

    # Run the YOLO function
    crop_image_astin, crop_image_yaghe = yolo(image_path)

    # Load the main image
    main_image = cv2.imread(image_path)

    # Display the main image
    cv2.imshow("Main Image", main_image)

    # Display the cropped 'astin' image if available
    if crop_image_astin is not None:
        cv2.imshow("Astin", crop_image_astin)
    else:
        print("Astin not detected.")

    # Display the cropped 'yaghe' image if available
    if crop_image_yaghe is not None:
        cv2.imshow("Yaghe", crop_image_yaghe)
    else:
        print("Yaghe not detected.")

    # Wait for a key press and close the windows
    cv2.waitKey(0)
    cv2.destroyAllWindows()
