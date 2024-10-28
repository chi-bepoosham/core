import pika
import os
import json
import re
import time
import base64

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
    os.system("sleep 10")

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
    os.system("sleep 10")

    # Attempt to establish a connection with retries
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
    try:
        # Only check if the queue exists without declaring it again
        channel.queue_declare(queue='ai_predict_process', durable=True, passive=True)
    except pika.exceptions.ChannelClosedByBroker:
        print("Queue 'ai_predict_process' does not exist or settings mismatch.")
        connection.close()
        return

    # Publish message to the queue
    message = json.dumps({
            "uuid": "d3bb48e4-cd10-42bc-90e3-8b80c381a342",
            "displayName": "App\\Jobs\\ProcessRabbitMQMessage",
            "job": "App\\Jobs\\ProcessRabbitMQMessage",
            "maxTries": None,
            "maxExceptions": None,
            "failOnTimeout": False,
            "backoff": None,
            "timeout": None,
            "retryUntil": None,
            "data": {
                "commandName": "App\\Jobs\\ProcessRabbitMQMessage",
                "command": base64.b64encode(data).decode('utf-8')
            },
            "id": "02f7d3f2-9234-4d6c-97f4-01c301774560"
    })
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
