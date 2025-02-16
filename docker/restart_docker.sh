#!/bin/bash


# Restart Docker Compose services
docker compose down && docker compose --profile gpus up -d
