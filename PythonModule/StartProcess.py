from RabbitMQClient import RabbitMQClient
import os


class StartProcess:

    def __init__(self):
        self.rabbitmq_client =  RabbitMQClient(
                                       user="develop",
                                       password="Z!^P>C78)g5",
                                       host="rabbitmq",
                                       vhost="rabbitmq",
                                       exchange_name="ai_topic_exchange",
                                       routing_key="ai_process",
                                       queue_name="ai_predict_process"
                                   )
        os.system("sleep 10")
        self.connect_rabbitmq()
        self.test()


    def test(self):

        self.rabbitmq_client.start_consuming(self.process_receive_message)



    def connect_rabbitmq(self):
        # Connect to RabbitMQ
        self.rabbitmq_client.connect()

    def process_receive_message(self,message):
        print(f"Received message: {message}")
        with open("message.txt", "w") as file:
            file.write(str(message))

        # Example of sending a message
        message_data = {
            "action": "user_created",
            "user_id": 1212,
            "description": "New user created from Python app"
        }
        self.rabbitmq_client.send_message(message_data)


