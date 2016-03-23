@ECHO OFF
SET BIN_TARGET=%~dp0/../vendor/behat/behat/bin/behat
php "%BIN_TARGET%" %*
