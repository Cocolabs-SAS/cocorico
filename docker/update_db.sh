#!/bin/bash
php bin/console doctrine:schema:update --dump-sql
echo
read -p "===> Proceed with Doctrine schema update ? [yN] "

echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    php bin/console doctrine:schema:update --force
fi
