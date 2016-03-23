BDD Tests
=========

1) Execute tests
----------------

1. Start selenium server:

    - Linux
        ./bin/selenium start
    - Windows
        .\bin\selenium.bat start

2. Execute tests
    - To speed up and to reinitialize mangopay users for KYC limit reached use SQLite DB Backup :
        cp features/_datas/backup/test_xxx      app/cache/test
        cp features/_datas/backup/test_xxx.ser      app/cache/test
    - Set parameters for test environment:
        cp app/config/parameters_test.yml.dist app/config/parameters_test.yml
        And set values
    - One test:
        bin/behat --name="User registration"
    - All tests:
        bin\behat
    - Rerun only failed tests:
        bin\behat.bat --rerun
