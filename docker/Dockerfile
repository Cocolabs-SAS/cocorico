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
ARG GGL_KEY1=none
ARG GGL_KEY2=none
ARG ITOU_HOST=none
ARG SMTP_HOST=none
ARG SMTP_PASSWORD=none
ARG SMTP_PORT=none
ARG SMTP_USER=none


ENV SQL_HOST=$SQL_HOST\
    SQL_PORT=$SQL_PORT \
    SQL_NAME=$SQL_NAME \
    SQL_USER=$SQL_USER \
    SQL_PASS=$SQL_PASS \
    MONGO_URL=$MONGO_URL \
    MONGO_DB=$MONGO_DB \
    HOST_PORT=$HOST_PORT \
    GGL_KEY1=$GGL_KEY1 \
    GGL_KEY2=$GGL_KEY2 \
    ITOU_HOST=$ITOU_HOST \
    APP_ENV=dev \
    SMTP_HOST=$SMTP_HOST \
    SMTP_PASSWORD=$SMTP_PASSWORD \
    SMTP_PORT=$SMTP_PORT \
    SMTP_USER=$SMTP_USER \
    SYMFONY_ENV=dev 

RUN apt-get update

RUN apt-get install -y \
        curl \
        git \
        default-mysql-client \ 
        imagemagick \ 
        unzip \
        wget \
        screen

RUN apt-get install -y \
        php7.3 \
        php7.3-fpm \
        php7.3-curl \
        php7.3-zip \
        php7.3-mbstring \
        php7.3-xml \
        php7.3-intl \
        php-dom \
        php-imagick \
        php-mongodb \
        php-mysql


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# RUN composer global require hirak/prestissimo 
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
    && sed -i -e 's|%GGL_KEY1%|'"$GGL_KEY1"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%GGL_KEY2%|'"$GGL_KEY2"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%SMTP_HOST%|'"$SMTP_HOST"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%SMTP_USER%|'"$SMTP_USER"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%SMTP_PASSWORD%|'"$SMTP_PASSWORD"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%SMTP_PORT%|'"$SMTP_PORT"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%ITOU_HOST%|'"$ITOU_HOST"'|' /cocorico/app/config/parameters.yml \
    && sed -i -e 's|%ITOU_ENV%|local_dev|' /cocorico/app/config/parameters.yml \
    && cat /cocorico/app/config/parameters.yml


# CMD ["bash"]
RUN composer install

RUN clevercloud/post_build_hook.sh
RUN wget https://get.symfony.com/cli/installer -O - | bash
# RUN useradd -ms /bin/bash _www && chown -R _www:_www ./var
# RUN service nginx restart && service php7.3-fpm start
COPY ./docker/run.sh ./run
COPY ./docker/run_css.sh ./run_css
RUN echo "Start Symfony server with:\n\
/home/cocorico/.symfony/bin/symfony server:start --no-tls --port 80\n\
"
CMD ["bash"]
# # CMD ["/home/cocorico/.symfony/bin/symfony", "server:start", "--no-tls", "--port 80"]
