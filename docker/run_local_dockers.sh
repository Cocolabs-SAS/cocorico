#!/bin/bash
docker stop some-mongo || true
docker stop some-mariadb || true
docker rm some-mongo some-mariadb || true

docker run --name some-mongo --network cocorico -d \
    -e MONGO_INITDB_DATABASE=cocorico \
    mongo:4.0-xenial 

docker run --name some-mariadb --network cocorico \
    -e MYSQL_ROOT_PASSWORD=cocorico \
    -e MYSQL_PASSWORD=cocorico \
    -e MYSQL_USER=cocorico \
    -e MYSQL_DATABASE=cocorico \
    -d mariadb:10
