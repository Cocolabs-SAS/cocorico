#!/bin/bash

## INIT MONGO
mongod --bind_ip 0.0.0.0 --fork --logpath /var/log/mongod.log


## INIT MYSQL
if [[ ! -d /run/mysqld ]]; then
    mkdir -p /run/mysqld
fi

if [[ ! -d /var/lib/mysql/cocorico ]]; then
    mysql_install_db --user=root --datadir=/var/lib/mysql
    mysqld --user=root --bootstrap < ./docker/database.sql
fi

chmod 777 -R /var/lib/mysql
mysqld_safe --user=root
while !(mysqladmin -ucocorico -pcocorico pin-v `pwd`:/cocorico -v `pwd`/tmp/mysql:/var/lib/mysql -v `pwd`/tmp/mongo:/data/dbg &> /dev/null); do
    sleep 1
    echo "Waiting for MySQL to come up"
done

if [[ ! -f /var/lib/mysql/cocorico/db.opt ]]; then
    php bin/console doctrine:schema:update --force
    php bin/console doctrine:fixtures:load -n
fi

if [[ ! -d /data/db ]]; then
    php bin/console doctrine:mongodb:schema:create
fi
