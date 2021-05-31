#!/bin/bash
echo "=====> Executing prepare.sh clevercloud init script"
cp ./clevercloud/init/parameters.yml ./app/config/parameters.yml

# Custom substitute symfony env vars, because env preprocessor fails to work
var_list="GGL_KEY1 GGL_KEY2 \
SMTP_HOST SMTP_USER SMTP_PASSWORD SMTP_PORT \
TARTEAUCITRON MATOMO_HOST MATOMO_SITEID SENTRY_DSN HOTJAR_ID ITOU_ENV ITOU_HOST \
BASE_SCHEME BASE_HOST \
MYSQL_ADDON_HOST MYSQL_ADDON_PORT MYSQL_ADDON_DB MYSQL_ADDON_USER MYSQL_ADDON_PASSWORD \
MONGODB_ADDON_DB MONGODB_ADDON_URI"

for key in $var_list; do
    echo "Setting $key in parameters.yml"
    sed -i -e 's|%'"$key"'%|'"${!key}"'|' ./app/config/parameters.yml
done

sleep 2
echo "##### FINAL APP CONFIG #####"
cat ./app/config/parameters.yml
echo "############################"
sleep 2

# Install persistent upload folder
echo "Synchronising upload folder"
rsync -r ./web/uploads/ ./web/uploads2/
# Force link to new folder
mv ./web/uploads{,.old}
ln -fs `pwd`/web/uploads2 `pwd`/web/uploads
