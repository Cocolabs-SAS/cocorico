# Application installation

## Get project sources
             
Fork Cocorico Git Repository then get sources:
             
### From PhpStorm:

 - Close all projects
 - Menu > VCS > Checkout from VC > Git
    - Git Repo: New forked repository address
    - Parent Dir: Choose parent of Symfony folder
    - Dir name: Symfony
 - Menu > File > Open: Symfony folder 
 - Change Project name
 - Activate Symfony2 framework
 - Configure automatically namespace root from Event log Dialog box for example
     
### From command line:

Go to to your parent "Document Root" directory and clone repository:

    cd /var/www/cocorico.local/
    git clone https://github.com/[gituser]/cocorico.git Symfony
                     
                     
## Create services accounts

See [Services account creation ](services-creation.md)


## Install composer

If you don't have Composer yet, run the following command in the root folder of your Symfony project:

    cd Symfony
    php -r "readfile('https://getcomposer.org/installer');" | php
     
    
## Install Cocorico dependencies

    php composer.phar install
    
Or to speed up:
    
    php composer.phar install --prefer-dist -vvv
    
Or in case of error with tarball (slower):

    php composer.phar install --prefer-source -vvv
   
This command will ask you the values of some of your application parameters. 
You will find more informations on them in the following chapter.
   
## Set your application parameters 
  
  See `app/config/parameters.yml.dist`
     
## Configure project

Copy and paste web/.htaccess.dev.dist and rename it to web/.htaccess. (It's configured by default for dev environment).
         
## Initialize the SQL and NoSQL database

### SQL database initialisation:
    
    #Linux
    chmod 744 bin/init-db
    ./bin/init-db php --env=dev
    
    #Windows
    .\bin\init-db.bat --env=dev
        
### MongoDB initialisation:

    #Linux
    chmod 744 bin/init-mongodb
    ./bin/init-mongodb php --env=dev
    
    #Windows
    .\bin\init-mongodb.bat --env=dev
    
## Check your System Configuration

Before starting coding, make sure that your local system is properly configured for Cocorico.

Execute this script to make sure that your local system is properly configured for Cocorico:

    php bin/symfony_requirements

The script returns a status code of `0` if all mandatory requirements are met, `1` otherwise.

Access the `config.php` script from a browser:

    http://cocorico.local/config.php

If you get any warnings or recommendations, fix them before moving on.

Check security dependencies:

    bin/security-checker security:check composer.lock
   
In case of error "An error occurred: SSL certificate problem: unable to get local issuer certificate.": 

    bin/security-checker security:check --end-point=http://security.sensiolabs.org/check_lock composer.lock

## Dump assets

    php app/console assets:install --symlink web --env=dev
    php app/console assetic:dump --env=dev

## Add crons

See [Crons documentation](crons.md)
    
## Browsing the Demo Application

Congratulations! You're now ready to use Cocorico [http://cocorico.local/](https://cocorico.local/)

Admin access is here :

    http://cocorico.local/admin/dashboard
    super-admin@cocorico.rocks
    super-admin
    
Don't forget to Change your super-admin password. 

Enjoy!

## Troubleshooting

Errors and exceptions are logged and rotated at the application level:

    $ tail -f var/logs/dev-yyyy-mm-dd.log
    $ tail -f var/logs/prod-yyyy-mm-dd.log