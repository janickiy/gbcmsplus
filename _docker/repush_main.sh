#!/bin/bash

DOCKER_BASE="dockerhub.wapdev.org:443/mcms"
DOCKER_NAME="latest"

docker build --no-cache -t $DOCKER_BASE:$DOCKER_NAME main
docker tag $DOCKER_BASE:$DOCKER_NAME $DOCKER_BASE:$DOCKER_NAME && docker push $DOCKER_BASE:$DOCKER_NAME
