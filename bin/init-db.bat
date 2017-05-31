@ECHO OFF

REM DB initialisation
REM Usage: .\bin\init-db.bat --env=dev

REM php app/console doctrine:database:drop --force %*
php app/console doctrine:database:create --if-not-exists %*
php app/console doctrine:schema:update --force %*
php app/console doctrine:fixtures:load %*
php app/console cocorico:currency:update %*


