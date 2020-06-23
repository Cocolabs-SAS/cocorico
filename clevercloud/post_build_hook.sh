#!/bin/bash

if [[ ! -f web/json/currencies.json ]]; then
    php bin/console cocorico:currency:update
fi

if [[ ! -d web/bundles ]]; then
    php bin/console assets:install --symlink
fi

if [[ ! -d web/css/compiled || ! -d web/js/compiled ]]; then
    php bin/console assetic:dump
fi

echo "=====> Enabling $APP_ENV .htaccess file"
cp web/.htaccess.$APP_ENV.dist web/.htaccess
