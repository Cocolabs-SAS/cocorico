#!/bin/bash

# Update Database
while !(mysqladmin -u$SQL_USER -p$SQL_PASS -h$SQL_HOST -P$SQL_PORT pin &> /dev/null); do
    sleep 1
    echo "Waiting for MySQL to come up"
done
echo "\n==> Checking DB update\n"
php bin/console doctrine:schema:update --dump-sql
echo "/n<== End of DB update check\n"
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load -n


# Assets install
php bin/console assets:install --symlink

# Yarn build
yarn install && yarn encore production

# Folder fix
chmod -R 777 /cocorico/var
export ITOU_ENV=local_dev

# Run server & watch CSS updates
/home/cocorico/.symfony/bin/symfony server:start --no-tls --port $HOST_PORT &
yarn encore dev --watch
