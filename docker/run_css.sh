#!/bin/bash
# chmod -R 777 /cocorico/var
# /home/cocorico/.symfony/bin/symfony server:start --no-tls --port 80 -d
composer install
clevercloud/post_build_hook.sh
php bin/console assetic:watch
