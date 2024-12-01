FROM nvidia/cuda:11.2.2-base-ubuntu20.04

WORKDIR /var/www
COPY PythonModule/. /var/www
ENV DEBIAN_FRONTEND=noninteractive


RUN  apt-get update && apt-get install -y  \
    build-essential \
    libgl1 \
    libjpeg-dev \
    libpng-dev \
    libtiff-dev \
    libavcodec-dev \
    libavformat-dev \
    libswscale-dev \
    libv4l-dev \
    libxvidcore-dev \
    libx264-dev \
    libgtk-3-dev \
    libcanberra-gtk3-module \
    libatlas-base-dev \
    gfortran \
    libgl1-mesa-glx \
    libglib2.0-0 \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

RUN pip3 install -r requirements.txt

CMD ["python3", "main.py"]

