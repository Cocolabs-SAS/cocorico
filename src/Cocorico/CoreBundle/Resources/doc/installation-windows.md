# Windows installation

## Requirements

### Apache 2 (or Nginx)

Activate following modules:

    - mod_headers
    - mod_rewrite
    - mod_ssl 
    
SSL with WAMP: 

    - Install Win32 OpenSSL here http://slproweb.com/products/Win32OpenSSL.html
    - Copy/paste libeay32.dll, ssleay32.dll into php bin folder (ex: C:\wamp64\bin\php\phpx.x)


Create your virtual host: [dev virtual host sample](src/Cocorico/CoreBundle/Resources/doc/virtual-hosts.rst)

### MongoDB

#### Install MongoDB:

See https://docs.mongodb.com/manual/tutorial/install-mongodb-on-windows/

#### Install PHP MongoDB Driver

See http://docs.mongodb.org/ecosystem/drivers/php/
    
**Note:** *For PHP 7 install mongodb extension and not mongo extension*

#### Start MongoDB

    bin\start-mongodb.bat "C:\Program Files\MongoDB\data"
            
    
### PHP
    
Install PHP >= 5.6 (tested on PHP 7.1, 5.6) 

Activate following extensions:

    - curl (>= 7.36)
    - intl
    - fileinfo
    - openssl
    - soap
    - exif
    - mongodb
    - gd
    - pdo_sqlite
    - pdo_mysql
    - opcache
    
Add the following lines to php.ini:

    curl.cainfo = "pathto/cacert.pem"
    memory_limit = 256M
    upload_max_filesize = 12M (as cocorico.user_img_max_upload_size)
    post_max_size = 240M

Set the same php timezone to php and php-cli php.ini file:

    date.timezone = UTC  
        
        
### MySQL 

Create your database and your database user

    CREATE DATABASE IF NOT EXISTS {DB} DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
    GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES, REFERENCES ON {DB}.* TO {DBUSER}@localhost IDENTIFIED BY '{DBUSERPWD}'

## Application installation

### Get project sources
             
Fork Cocorico Git Repository then get sources:
             
#### From PhpStorm:

     - Close all projects
     - Menu > VCS > Checkout from VC > Git
        - Git Repo: New forked repository address
        - Parent Dir: Choose parent of Symfony folder
        - Dir name: Symfony
     - Menu > File > Open: Symfony folder 
     - Change Project name
     - Activate Symfony2 framework
     - Configure automatically namespace root from Event log Dialog box for example
     
#### From command line:

Change to your parent "Document Root" directory and clone repository:

    cd /var/www/cocorico.dev/
    git clone https://github.com/[gituser]/cocorico.git Symfony
                     
                        
### Create services accounts

See  [Services account creation ](src/Cocorico/CoreBundle/Resources/doc/services-creation.rst)

    
### Install composer

If you don't have Composer yet, run the following command in the root folder of your Symfony project:

    cd Symfony
    php -r "readfile('https://getcomposer.org/installer');" | php
    
    
### Install Cocorico dependencies

    php composer.phar install
    
Or to speed up:
    
    php composer.phar install --prefer-dist -vvv
    
Or in case of error with tarball (slower):

    php composer.phar install --prefer-source -vvv
   
This command will ask you the values of some of your application parameters. 
You will find more informations on them in the following chapter.
   
### Set your application parameters 
  
  See `app/config/parameters.yml.dist`
     
### Configure project

Copy and paste web/.htaccess.dist and rename it to web/.htaccess. (It's configured by default for dev environment).
         
### Initialize the SQL and NoSQL database

#### SQL database initialisation:
 
    .\bin\init-db.bat --env=dev
        
#### MongoDB initialisation:

    .\bin\init-mongodb.bat --env=dev
    
    
## Checking your System Configuration

Before starting coding, make sure that your local system is properly configured for Cocorico.

Execute the `check.php` script to make sure that your local system is properly configured for Cocorico:

    php app/check.php

The script returns a status code of `0` if all mandatory requirements are met, `1` otherwise.

Access the `config.php` script from a browser:

    http://cocorico.dev/config.php

If you get any warnings or recommendations, fix them before moving on.

Check security dependencies:

    bin/security-checker security:check composer.lock
   
In case of error "An error occurred: SSL certificate problem: unable to get local issuer certificate.": 

    bin/security-checker security:check --end-point=http://security.sensiolabs.org/check_lock composer.lock

## Dump assets

    php app/console assets:install --symlink web --env=dev
    php app/console assetic:dump --env=dev

## Add crons

See [Technical documentation](src/Cocorico/CoreBundle/Resources/doc/index.rst)
    
## Browsing the Demo Application

Congratulations! You're now ready to use Cocorico.
http://cocorico.dev/

Admin access is here :

    http://cocorico.dev/admin/dashboard
    super-admin@cocorico.rocks
    super-admin
    
Don't forget to Change your super-admin password. 

Enjoy!