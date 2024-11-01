import pika
import json
import time

class RabbitMQClient:
    def __init__(self, user, password, host, vhost, exchange_name, routing_key, queue_name):
        self.credentials = pika.PlainCredentials(user, password)
        self.parameters = pika.ConnectionParameters(
            host=host,
            virtual_host=vhost,
            credentials=self.credentials
        )
        self.exchange_name = exchange_name
        self.routing_key = routing_key
        self.queue_name = queue_name
        self.connection = None
        self.channel = None

    def connect(self):
        for i in range(5):
            try:
                self.connection = pika.BlockingConnection(self.parameters)
                self.channel = self.connection.channel()

                # Declare the topic exchange
                self.channel.exchange_declare(exchange=self.exchange_name, exchange_type='topic', durable=True)

                # Declare the queue and bind it to the topic exchange
                self.channel.queue_declare(queue=self.queue_name, durable=True, passive=True)
                self.channel.queue_bind(exchange=self.exchange_name, queue=self.queue_name, routing_key=self.routing_key)

                print("Connected to RabbitMQ topic exchange.")
                break
            except pika.exceptions.AMQPConnectionError:
                print(f"Connection attempt {i+1} failed, retrying in 5 seconds...")
                time.sleep(5)
        else:
            raise Exception("Failed to connect to RabbitMQ after 5 attempts.")

    def close_connection(self):
        if self.connection:
            self.connection.close()
            print("RabbitMQ connection closed.")

    def start_consuming(self, callback):
        def wrapper_callback(ch, method, properties, body):
            try:
                message_data = json.loads(body)
                callback(message_data)
                # Acknowledge the message after processing
                ch.basic_ack(delivery_tag=method.delivery_tag)
            except (json.JSONDecodeError, KeyError) as e:
                print(f"Failed to process message: {e}")
                ch.basic_nack(delivery_tag=method.delivery_tag)  # Negative acknowledgment for failed processing


        self.channel.basic_consume(queue=self.queue_name, on_message_callback=wrapper_callback, auto_ack=True)
        print("Started consuming messages.")
        self.channel.start_consuming()

    def send_message(self, data):
        message = json.dumps({
            "id": "02f7d3f2-9234-4d6c-97f4-01c301774560",
            "uuid": "d3bb48ex4-cd0-42bc-90e3-8b80c381a342",
            "data": data,
        })
        self.channel.basic_publish(
            exchange=self.exchange_name,
            routing_key=self.routing_key,
            body=message,
            properties=pika.BasicProperties(delivery_mode=2)
        )
        print(f"Sent message: {message}")
