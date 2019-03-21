@ECHO OFF

REM Mongo DB initialisation
REM Usage: .\bin\init-mongodb.bat --env=dev

php bin/console doctrine:mongodb:schema:drop %*
php bin/console doctrine:mongodb:schema:create %*

