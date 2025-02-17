import redis
import json
import os


# Redis Configuration via environment variables or hardcoded defaults
redis_host = 'redis_main'
redis_port = 6379
redis_password ='amir9895'
redis_publisher_queue = 'publisher_ai_predict_process'
redis_subscriber_queue = 'subscriber_ai_predict_process'

# Create a Redis connection
redis_conn = redis.StrictRedis(
    host=redis_host,
    port=redis_port,
    password=redis_password,
    charset="utf-8",
    decode_responses=True
)

def callback(message):
    """Process incoming Redis messages."""
    if message['type'] == 'pmessage':  # Pattern subscription message
        msg = message['data']
        print(f"Received from {message['channel']}: {msg}")

        # Log received message
        with open("messages.log", "a") as file:
                file.write(message['data'] + "\n")

        # Prepare the response message to publish
        sub_message = {"transaction_status": "ok"}
        try:
            redis_conn.publish(redis_subscriber_queue, json.dumps(sub_message))
            print(f"Published : {sub_message}")
        except Exception as e:
            write_to_log(f"Error publishing message: {e}")


def subscribe_queue():
    """Subscribe to Redis channel and process messages."""
    pubsub = redis_conn.pubsub()
    pubsub.psubscribe(redis_publisher_queue)

    try:
        for message in pubsub.listen():
            callback(message)
    except KeyboardInterrupt:
        print("Subscriber stopped gracefully.")
    except Exception as e:
        print(f"Error occurred: {e}")
        write_to_log(f"Error occurred: {e}")
    finally:
        pubsub.close()


if __name__ == "__main__":
    subscribe_queue()
