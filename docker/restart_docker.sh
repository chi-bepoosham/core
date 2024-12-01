#!/bin/bash

# Specify the full path to the Docker executable

# Change directory to where your docker-compose.yml file is located
cd /home/amir/Projects/ChiBepoosham

# Restart Docker Compose services
docker compose down && docker compose up -d
