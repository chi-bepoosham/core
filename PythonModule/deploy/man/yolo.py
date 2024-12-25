
import cv2
def yolo(model,image_path):

    import os
    from pathlib import Path
    from ultralytics import YOLO
    import cv2
    from PIL import Image

    # مدل YOLOv8 رو لود کنید
    if model=="astin":
        model = YOLO('/var/www/deploy/models/astin/models/best.pt')
    if model=="yaghe":
        model = YOLO('/var/www/deploy/models/yaghe/models/best.pt')
    model.to("cpu")




    # پردازش تک‌تک تصاویر
    #for image_path in image_files:
    image_file = os.path.basename(image_path)  # نام فایل تصویر

    # اجرای YOLOv8 روی تصویر
    results = model.predict(source=image_path, save=False)
    print(f"Results for {image_file}: {results}")  # دیباگ

    # بررسی اینکه آیا هیچ نتیجه‌ای وجود دارد یا نه
    if len(results) == 0 or len(results[0].boxes) == 0:
        print(f"No detections found in {image_file}")
        image = cv2.imread(image_path)
        return image
    else:



        # لود کردن تصویر برای برش
        image = cv2.imread(image_path)
        if image is None:
            print(f"Failed to load image: {image_file}")

        # انتخاب باکسی که بیشترین مساحت را دارد
        max_area = 0
        best_box = None

        # برای هر باکس تشخیص داده شده
        for result in results[0].boxes:

            confidence = result.conf.item()
            if confidence < 90 :
                return "No Cloth detected"

            # گرفتن مختصات باکس (x1, y1, x2, y2)
            x1, y1, x2, y2 = map(int, result.xyxy[0].tolist())

            # محاسبه مساحت باکس
            area = (x2 - x1) * (y2 - y1)

            # بررسی اینکه آیا این باکس بزرگتر از باکس‌های قبلی است
            if area > max_area:
                max_area = area
                best_box = (x1, y1, x2, y2)

        # اگر باکسی پیدا شد، برش و ذخیره‌سازی آن
        if best_box is not None:
            x1, y1, x2, y2 = best_box

            # برش تصویر با توجه به مختصات باکس
            cropped_image = image[y1:y2, x1:x2]

            # بررسی اینکه آیا تصویر برش خورده درست است
            if cropped_image.size == 0:
                print(f"Failed to crop image: {image_file}")

            else:
                return cropped_image

