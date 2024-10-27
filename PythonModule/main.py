import pika
import os
import json
import re
import time

# Define custom credentials
rabbitmq_user = "develop"
rabbitmq_password = "Z!^P>C78)g5"
rabbitmq_host = "rabbitmq"
rabbitmq_vhost = "rabbitmq"


def process_message(ch, method, properties, body):

    try:
        # Decode the message body
        message = json.loads(body)
        php_serialized_data = message.get("data", {}).get("command")

        # Regular expression to extract the JSON part
        match = re.search(r's:7:"message";s:\d+:"({.*})";', php_serialized_data)
        if match:

            # Decode the nested command containing your custom message
            json_data = match.group(1)
            messageData = json.loads(json_data)


    except (json.JSONDecodeError, KeyError) as e:
        messageData = e

    # Write the message body to a new file
    file_name = f"message.txt"
    with open(file_name, "w") as file:
        file.write(str(messageData))  # Save the message in the file

def consume_queue():
    credentials = pika.PlainCredentials(rabbitmq_user, rabbitmq_password)
    parameters = pika.ConnectionParameters(
        host=rabbitmq_host,
        virtual_host=rabbitmq_vhost,
        credentials=credentials
    )
    os.system("sleep 2")

    for i in range(5):
        try:
            connection = pika.BlockingConnection(parameters)
            break
        except pika.exceptions.AMQPConnectionError:
            print(f"Connection attempt {i+1} failed, retrying in 5 seconds...")
            os.system("sleep 5")
    else:
        print("Failed to connect to RabbitMQ after 5 attempts.")
        return

    channel = connection.channel()
    channel.queue_declare(queue='ai_predict_process', durable=True)
    channel.basic_consume(queue='ai_predict_process', on_message_callback=process_message, auto_ack=True)

    channel.start_consuming()


def send_message_to_rabbitmq(data):
    credentials = pika.PlainCredentials(rabbitmq_user, rabbitmq_password)
    parameters = pika.ConnectionParameters(
        host=rabbitmq_host,
        virtual_host=rabbitmq_vhost,
        credentials=credentials
    )

    for i in range(5):
        try:
            connection = pika.BlockingConnection(parameters)
            break
        except pika.exceptions.AMQPConnectionError:
            print(f"Connection attempt {i+1} failed, retrying in 5 seconds...")
            os.system("sleep 5")
    else:
        print("Failed to connect to RabbitMQ after 5 attempts.")
        return

    channel = connection.channel()
    channel.queue_declare(queue='ai_predict_process', durable=True)

    # Publish message to the queue
    message = json.dumps(data)
    channel.basic_publish(
        exchange='',
        routing_key='ai_predict_process',
        body=message,
        properties=pika.BasicProperties(
            delivery_mode=2,  # Make message persistent
        )
    )
    print(f"Sent message: {message}")
    connection.close()


if __name__ == "__main__":
#     consume_queue()

    os.system("sleep 2")
    # Example message data
    message_data = {
        "action": "user_created",
        "user_id": 1212,
        "description": "New user created from Python app"
    }
    send_message_to_rabbitmq(message_data)
