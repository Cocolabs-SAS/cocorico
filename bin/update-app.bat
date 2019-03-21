@ECHO OFF

REM Update application
REM Usage: .\bin\update-app.bat --env=dev

REM In case of error : php composer.phar install --prefer-source -vvv
php composer.phar install --prefer-dist -vvv

php bin/console doctrine:schema:update --force %*
php bin/console doctrine:mongodb:schema:update %*
php bin/console cache:clear %*
php bin/console assets:install --symlink web %*
php bin/console assetic:dump %*