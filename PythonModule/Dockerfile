FROM nvidia/cuda:11.3.1-cudnn8-devel-ubuntu20.04

WORKDIR /var/www
COPY PythonModule/main.py /var/www
COPY PythonModule/requirements.txt /var/www
#COPY PythonModule/. /var/www
ENV DEBIAN_FRONTEND=noninteractive

RUN rm /etc/apt/sources.list.d/cuda.list

RUN apt-get update && apt-get install -y  \
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
    protobuf-compiler \
    && rm -rf /var/lib/apt/lists/*

RUN pip3 install -r requirements.txt


# Set environment variables for CUDA
ENV LD_LIBRARY_PATH=/usr/local/cuda/lib64:$LD_LIBRARY_PATH
ENV PATH=/usr/local/cuda/bin:$PATH

# Set Python buffer to avoid IO buffering
ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    LANG=C.UTF-8 \
    PROTOCOL_BUFFERS_PYTHON_IMPLEMENTATION=python


CMD ["python3", "main.py"]
