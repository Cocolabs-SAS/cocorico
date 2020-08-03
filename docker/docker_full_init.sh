#!/bin/sh

## INIT MONGO
mongod --bind_ip 0.0.0.0 &&


if [[ ! -d /data/db ]]; then
    php bin/console doctrine:mongodb:schema:create
fi

## INIT MYSQL
if [[ ! -d /run/mysqld ]]; then
    mkdir -p /run/mysqld
fi

if [[ ! -d /var/lib/mysql/cocorico ]]; then
    mysql_install_db --user=root --datadir=/var/lib/mysql
    mysqld --user=root --bootstrap < /docker/database.sql
fi

mysqld_safe --user=root --console &&
while !(mysqladmin -ucocorico -pcocorico ping &> /dev/null); do
    sleep 1
done

if [[ ! -f /var/lib/mysql/cocorico/db.opt ]]; then
    php bin/console doctrine:schema:update --force
    php bin/console doctrine:fixtures:load -n
fi
