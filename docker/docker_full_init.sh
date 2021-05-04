#!/bin/bash
## INIT MYSQL
if [[ ! -d /run/mysqld ]]; then
    mkdir -p /run/mysqld
fi

echo "Installing Mysql"
if [[ ! -d /var/lib/mysql/cocorico ]]; then
    mysql_install_db --user=mysql --datadir=/var/lib/mysql
    mysqld --user=mysql --bootstrap < ./docker/database.sql
fi

chmod 777 -R /var/lib/mysql
chown mysql:mysql -R /var/run/mysqld
echo "Running Mysql"
mysqld_safe --no-watch
while !(mysqladmin -ucocorico -pcocorico pin &> /dev/null); do
    sleep 1
    echo "Waiting for MySQL to come up"
done

if [[ ! -f /var/lib/mysql/cocorico/db.opt ]]; then
    echo "Filling Mysql"
    php bin/console doctrine:schema:update --force
    php bin/console doctrine:fixtures:load -n
fi
