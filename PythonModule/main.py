import redis
import os
import json
# import re
# import time
# import requests
# import random
# from deploy.man.body_type_M import get_man_body_type
# from deploy.man.load_and_predict_man import process_clothing_image
# from deploy.woman.body_typeF import get_body_type_female
# from deploy.woman.load_and_predict_woman import process_woman_clothing_image
# from deploy.woman.load_and_predict_woman_6model import process_six_model_predictions


# Redis Configuration via environment variables or hardcoded defaults
redis_host = 'redis_main'
redis_port = 6379
redis_password ='amir9895'
redis_queue = 'ai_predict_process'
redis_subscribe_channel = 'subscribe_ai_predict_process_channel'

# Create a Redis connection
redis_conn = redis.StrictRedis(
    host=redis_host,
    port=redis_port,
    password=redis_password,
    charset="utf-8",
    decode_responses=True
)



def process_image(gender,action,image_link):

    # Create directory if it doesn't exist
    temp_images_dir = '/var/www/temp_images/'
    if not os.path.exists(temp_images_dir):
        os.makedirs(temp_images_dir, exist_ok=True)

#     filename = image_link.split('/')[-1]
#     img_data = requests.get(image_link).content
#     img_name = temp_images_dir + str(int(time.time())) + str(random.randrange(100, 999)) + '-temp-' + filename
#
#     with open(img_name, 'wb') as handler:
#         handler.write(img_data)
#
#     if gender == 1 or gender == '1':
#         if action == 'body_type':
#             process_data = get_man_body_type(img_name)
#         else:
#             process_data = process_clothing_image(img_name)
#     else:
#         if action == 'body_type':
#             process_data = get_body_type_female(img_name)
#         else:
#             process_data = process_woman_clothing_image(img_name)
#             if process_data.get('paintane') is None:
#                 process_data = process_six_model_predictions(img_name)


    return {
#         "process_data":process_data,
        "item_name":"image_processed",
        "item_content":"1d5w1dw4d6w4d6w46d"
    }


def process_message(job_data):

    messageData = json.loads(job_data)
    if messageData is not None:

        action = messageData.get("action")
        user_id = messageData.get("user_id")
        gender = messageData.get("gender")
        clothes_id = messageData.get("clothes_id")
        image_link = messageData.get("image_link")
        time = messageData.get("time")
        process_image_data = process_image(gender,action,image_link)

        completion_data =  {
            "process_image": process_image_data,
            "action": action,
            "user_id": user_id,
            "gender": gender,
            "clothes_id": clothes_id,
            "image_link": image_link,
            "time": time,
        }

        publish_data_to_redis(completion_data)

def publish_data_to_redis(data):
    message = json.dumps(data)
    redis_conn.publish(redis_subscribe_channel, message)


def listen_redis_queue():
    while True:
        job = redis_conn.brpop(redis_queue)  # Blocking pop
        if job:
            _, job_data = job
            process_message(job_data)



if __name__ == "__main__":
    listen_redis_queue()
