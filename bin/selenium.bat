@ECHO OFF
REM start /B
start "" cmd.exe /k java -jar "%~dp0selenium-server.jar" -Dwebdriver.chrome.driver="%~dp0chromedriver.exe"