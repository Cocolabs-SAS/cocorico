@ECHO OFF

REM Mongo DB initialisation
REM Usage: .\bin\init-mongodb.bat --env=dev

php app/console doctrine:mongodb:schema:drop %*
php app/console doctrine:mongodb:schema:create %*

