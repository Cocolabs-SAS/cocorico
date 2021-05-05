#!/bin/bash
# Database importation :
# docker exec -i some-mariadb mysql -ucocorico -pcocorico cocorico < coco_dump.sql
docker network create --driver bridge cocorico || true

docker stop some-mariadb || true
docker rm some-mariadb || true

docker run --name some-mariadb --network cocorico \
    -e MYSQL_ROOT_PASSWORD=cocorico \
    -e MYSQL_PASSWORD=cocorico \
    -e MYSQL_USER=cocorico \
    -e MYSQL_DATABASE=cocorico \
    -d mariadb:10
