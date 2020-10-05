#!/bin/bash
chmod -R 777 /cocorico/var
export ITOU_ENV=local_dev
# ./clevercloud/post_build_hook.sh
/home/cocorico/.symfony/bin/symfony server:start --no-tls --port 80
