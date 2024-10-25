FROM nvidia/cuda:11.2.2-base-ubuntu20.04

RUN apt-get update && apt-get install -y \
    python3-pip

# COPY requirements.txt .
#RUN pip3 install -r requirements.txt

RUN pip3 install tensorflow==2.10
RUN pip3 install torch==1.11.0
RUN pip3 install ultralytics

COPY . /var/www
WORKDIR /var/www

CMD ["python3", "app.py"]

