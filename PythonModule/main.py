import pika
import os
import json
import re
import time
import requests
import random
from deploy.man.body_type_M import get_man_body_type
from deploy.man.load_and_predict_man import process_clothing_image
from deploy.woman.body_typeF import get_body_type_female
from deploy.woman.load_and_predict_woman import process_woman_clothing_image
from deploy.woman.load_and_predict_woman_6model import process_six_model_predictions

# Define RabbitMQ credentials and connection settings
rabbitmq_user = "develop"
rabbitmq_password = "Z!^P>C78)g5"
rabbitmq_host = "rabbitmq"
rabbitmq_vhost = "rabbitmq"
response_queue = "ai_predict_process"


def process_image(gender,action,image_link):

    # Create directory if it doesn't exist
    temp_images_dir = '/var/www/temp_images/'
    if not os.path.exists(temp_images_dir):
        os.makedirs(temp_images_dir, exist_ok=True)

    filename = image_link.split('/')[-1]
    img_data = requests.get(image_link).content
    img_name = temp_images_dir + str(int(time.time())) + str(random.randrange(100, 999)) + '-temp-' + filename

    with open(img_name, 'wb') as handler:
        handler.write(img_data)

    if gender == 1 or gender == '1':
        if action == 'body_type':
            process_data = get_man_body_type(img_name)
        else:
            process_data = process_clothing_image(img_name)
    else:
        if action == 'body_type':
            process_data = get_body_type_female(img_name)
        else:
            process_data = process_woman_clothing_image(img_name)
            if process_data.get('paintane') is None:
                process_data = process_six_model_predictions(img_name)


    return {
        "process_data":process_data,
        "item_name":"image_processed",
        "item_content":"1d5w1dw4d6w4d6w46d"
    }



def establish_connection():
    credentials = pika.PlainCredentials(rabbitmq_user, rabbitmq_password)
    parameters = pika.ConnectionParameters(
        host=rabbitmq_host,
        virtual_host=rabbitmq_vhost,
        credentials=credentials
    )

    return pika.BlockingConnection(parameters)

def process_message(ch, method, properties, body):

    if properties.headers and properties.headers.get('index') == 1:
        ch.basic_nack(delivery_tag=method.delivery_tag, requeue=True)
    else:
        ch.basic_ack(delivery_tag=method.delivery_tag)
        message = json.loads(body)
        job_id = message.get("id")
        job_uuid = message.get("uuid")
        job_name = message.get("displayName")

        if job_name == 'App\\Jobs\\SendRabbitMQMessage' :
            try:
                try:
                    php_serialized_data = message.get("data", {}).get("command")
                    match = re.search(r's:7:"\x00\*\x00data";a:\d+:{(.*)}', php_serialized_data)
                    if match:
                        data_content = match.group(1)
                        # Regex pattern to extract individual key-value pairs
                        key_value_pairs = re.findall(r's:\d+:"(.*?)";(s|i):\d+:"?([^";]*)"?(?:;|$)', data_content)
                        # Convert key-value pairs to a dictionary
                        messageData = {key: int(value) if type_ == 'i' else value for key, type_, value in key_value_pairs}
                    else:
                        messageData = None

                except TypeError as e:
                    messageData = None

            except (json.JSONDecodeError, KeyError) as e:
                messageData = None



            if messageData is not None:

                action = messageData.get("action")
                user_id = messageData.get("user_id")
                gender = messageData.get("gender")
                clothes_id = messageData.get("clothes_id")
                image_link = messageData.get("image_link")
                time = messageData.get("time")
                process_image_data = process_image(gender,action,image_link)


                completion_data =  {
                    "id": job_id,
                    "uuid": job_uuid,
                    "job": job_name,
                    "data": {
                        "process_image": process_image_data,
                        "action": action,
                        "user_id": user_id,
                        "gender": gender,
                        "clothes_id": clothes_id,
                        "image_link": image_link,
                        "time": time,
                    },
                }

                send_message_to_rabbitmq(completion_data)

def send_message_to_rabbitmq(data):
    """Send a completion message to the RabbitMQ response queue."""
    connection = establish_connection()
    channel = connection.channel()

    message = json.dumps(data)
    channel.basic_publish(
        exchange='',
        routing_key='ai_predict_process',
        body=message,
        properties=pika.BasicProperties(delivery_mode=2,headers={'index': 1})
    )



    connection.close()

def consume_queue():
    time.sleep(10)
    connection = establish_connection()
    channel = connection.channel()

    channel.queue_declare(queue='ai_predict_process', durable=True, passive=True)
    channel.basic_consume(queue='ai_predict_process', on_message_callback=process_message)
    channel.start_consuming()



if __name__ == "__main__":
    consume_queue()
