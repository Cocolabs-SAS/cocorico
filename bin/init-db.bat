@ECHO OFF

REM DB initialisation
REM Usage: .\bin\init-db.bat --env=dev

REM php bin/console doctrine:database:drop --force %*
php bin/console doctrine:database:create --if-not-exists %*
php bin/console doctrine:schema:update --force %*
php bin/console doctrine:fixtures:load %*
php bin/console cocorico:currency:update %*


