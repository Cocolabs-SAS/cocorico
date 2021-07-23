#!/bin/bash

if [[ ! -d web/bundles ]]; then
    php bin/console assets:install --symlink
fi

echo "=====> Enabling $APP_ENV .htaccess file"
cp web/.htaccess.$APP_ENV.dist web/.htaccess

echo "=====> Building front"
yarn install && yarn encore production
