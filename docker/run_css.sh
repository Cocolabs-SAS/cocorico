#!/bin/bash
chmod -R 777 /cocorico/var
/home/cocorico/.symfony/bin/symfony server:start --no-tls --port 80 -d
php bin/console assetic:watch
