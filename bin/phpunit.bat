@ECHO OFF
SET BIN_TARGET=%~dp0/../vendor/phpunit/phpunit/phpunit
php "%BIN_TARGET%" %*
