@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/doctrine/dbal/bin/doctrine-dbal
php "%BIN_TARGET%" %*
