 from debian:buster-slim

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/home/cocorico \
    HOME=/home/cocorico \
    HOST_UID=1000

ARG SQL_HOST=localhost
ARG SQL_PORT=3306
ARG SQL_NAME=cocorico
ARG SQL_USER=cocorico
ARG SQL_PASS=cocorico
ARG MONGO_URL='mongodb://localhost:27017'
ARG MONGO_DB=cocorico
ARG HOST_PORT=9090


ENV SQL_HOST=$SQL_HOST\
    SQL_PORT=$SQL_PORT \
    SQL_NAME=$SQL_NAME \
    SQL_USER=$SQL_USER \
    SQL_PASS=$SQL_PASS \
    MONGO_URL=$MONGO_URL \
    MONGO_DB=$MONGO_DB \
    HOST_PORT=$HOST_PORT \
    APP_ENV=dev

RUN apt-get update
RUN apt-get install -y \
        php7.3 \
        php7.3-fpm \
        php7.3-curl \
        php7.3-zip \
        php7.3-mbstring \
        php7.3-xml \
        php7.3-intl \
        php-dom \
        php-mongodb \
        php-mysql


RUN apt-get install -y \
        curl \
        git \
        default-mysql-client \ 
        unzip \
        wget

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer global require hirak/prestissimo 
RUN  groupadd cocorico && useradd -g cocorico --create-home cocorico

# COPY docker/nginx.default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx.default.conf /etc/nginx/sites-enabled/default

COPY . /cocorico
WORKDIR cocorico

COPY ./docker/parameters.yml /cocorico/app/config/parameters.yml
RUN sed -i -e 's|%SQL_HOST%|'"$SQL_HOST"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%SQL_PORT%|'"$SQL_PORT"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%SQL_NAME%|'"$SQL_NAME"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%SQL_USER%|'"$SQL_USER"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%SQL_PASS%|'"$SQL_PASS"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%MONGO_URL%|'"$MONGO_URL"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%MONGO_DB%|'"$MONGO_DB"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%HOST_PORT%|'"$HOST_PORT"'|' /cocorico/app/config/parameters.yml \
    && cat /cocorico/app/config/parameters.yml

RUN composer install


RUN clevercloud/post_build_hook.sh
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN useradd -ms /bin/bash _www && chown -R _www:_www ./var
# RUN service nginx restart && service php7.3-fpm start
COPY ./docker/run.sh ./run
CMD ["bash"]
# CMD ["/home/cocorico/.symfony/bin/symfony server:start --no-tls --port 80"]
