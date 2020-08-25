#!/bin/bash
docker stop some-mongo
docker stop some-mariadb
docker rm some-mango some-mariadb

docker run --name some-mongo --network cocorico -d \
    -e MONGO_INITDB_DATABASE=cocorico \
    mongo:4.0-xenial 

docker run --name some-mariadb --network cocorico \
    -e MYSQL_ROOT_PASSWORD=cocorico \
    -e MYSQL_PASSWORD=cocorico \
    -e MYSQL_USER=cocorico \
    -e MYSQL_DATABASE=cocorico \
    -d mariadb:10
