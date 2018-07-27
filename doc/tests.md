# BDD Tests

##  Execute tests

1. Install selenium

- Server

        
    #Linux
        cd /var/www/cocorico.local/Symfony/bin/
        wget -c https://goo.gl/4g538W -O selenium-server.jar
            
    #Windows
        - Go to https://www.seleniumhq.org/download/
        - Click on https://goo.gl/4g538W and save as selenium-server.jar in your Cocorico project bin folder
        
- Browser driver

    
    #Linux
        wget -c https://sites.google.com/a/chromium.org/chromedriver/downloads -O chromedriver.exe
    
    #Windows
            - Go to https://www.seleniumhq.org/download/
            - Click on https://sites.google.com/a/chromium.org/chromedriver/downloads and save as chromedriver.exe in your Cocorico project bin folder


2. Start selenium server


    #Linux 
    ./bin/selenium start
        
    #Windows
    .\bin\selenium.bat start
        

3. Create sqlite DB test

    - Linux
    
        ./bin/init.bat --env=test
        
    - Windows
    
        .\bin\init-db.bat --env=test
    
    
4. Execute tests
    - To speed up and to reinitialize mangopay users for KYC limit reached use SQLite DB Backup :
        cp features/_datas/backup/test_xxx      app/cache/cache/test
        cp features/_datas/backup/test_xxx.ser      app/cache/cache/test
    - Set parameters for test environment:
        cp app/config/parameters_test.yml.dist app/config/parameters_test.yml
        And set values
    - One test:
        bin/behat --name="User registration"
    - All tests:
        bin\behat
    - Rerun only failed tests:
        bin\behat.bat --rerun
    - One scenario:
       bin\behat.bat bin\behat features\frontend\UserLogin.feature:6