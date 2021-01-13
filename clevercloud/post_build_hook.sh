#!/bin/bash

# if [[ ! -f web/json/currencies.json ]]; then
#     php bin/console cocorico:currency:update
# fi

if [[ ! -d web/bundles ]]; then
    php bin/console assets:install --symlink
fi

echo "=====> Enabling $APP_ENV .htaccess file"
cp web/.htaccess.$APP_ENV.dist web/.htaccess

echo "=====> Building front"
yarn install && yarn encore production
