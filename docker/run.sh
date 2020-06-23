#!/bin/bash
./clevercloud/post_build_hook.sh
/home/cocorico/.symfony/bin/symfony server:start --no-tls --port 80
