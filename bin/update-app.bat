@ECHO OFF

REM Update application
REM Usage: .\bin\update-app.bat --env=dev

REM In case of error : php composer.phar install --prefer-source -vvv
php composer.phar install --prefer-dist -vvv

php app/console doctrine:schema:update --force %*
php app/console doctrine:mongodb:schema:update %*
php app/console cache:clear %*
php app/console assets:install --symlink web %*
php app/console assetic:dump %*