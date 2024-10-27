FROM nvidia/cuda:11.2.2-base-ubuntu20.04

WORKDIR /var/www
COPY PythonModule/. /var/www

RUN apt-get update && apt-get install -y \
    python3-pip

RUN pip3 install -r requirements.txt

CMD ["python3", "main.py"]

