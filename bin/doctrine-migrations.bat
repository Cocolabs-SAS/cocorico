@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/doctrine/migrations/bin/doctrine-migrations
php "%BIN_TARGET%" %*
