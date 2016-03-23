# Cocorico

Cocorico is an open source platform to create collaborative consumption marketplaces.
You can find more information about this project on [http://www.cocolabs.io](http://www.cocolabs.io).

This document contains information on how to download, install, and start using Cocorico.

- [Installation](#installation)
- [Versioning](#versioning)
- [Changes](#changes)
- [Contribute](#contribute)
- [Roadmap](#roadmap)
- [Technical documentation](#technical-documentation)
- [License](#mit-license)


# Installation

## Requirements & Server configuration

### Configure Apache
    
Activate following modules:

    - mod_headers
    - mod_rewrite

### Configure PHP
    
Tested versions:

    - php 5.4
    - MongoDB 2.6.8, 3.0.3
        
Activate following extensions:

    - apc (For php 5.5 use php  native opcode cache)
    - curl (>= 7.36)
    - intl
    - fileinfo
    - openssl
    - soap
    - exif
    - mongo
    - imagick
    - pdo_sqlite
    
Add the following lines to php.ini:

    - curl.cainfo = "pathto/cacert.pem"
    - xdebug.max_nesting_level = 1000
    - memory_limit = 256M
    - upload_max_filesize = 12M (as cocorico.user_img_max_upload_size)
    - post_max_size = 13M
    - [APC]
      apc.enable_cli=Off
      apc.enabled = 1
      apc.shm_segments = 1
      apc.shm_size = 64M
      apc.max_file_size = 10M
      apc.stat = 1

Set the same php timezone to php and php-cli php.ini file:

    - date.timezone = UTC      
        
        
### Create your database and your database user

    - CREATE DATABASE IF NOT EXISTS {DB} DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
    - GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES ON {DB}.* TO {DBUSER}@localhost IDENTIFIED BY '{DBUSERPWD}'

        
### Install and start MongoDB 

#### Install MongoDB on Debian:

    See http://docs.mongodb.org/manual/tutorial/install-mongodb-on-debian/?_ga=1.159299576.319082154.1425377029

#### Install PHP MongoDB Driver:

    See http://docs.mongodb.org/ecosystem/drivers/php/

#### Start MongoDB:

On Windows:
    
    bin\start-mongodb.bat "C:\Program Files\MongoDB\data"
            
On Linux:
    
    See http://docs.mongodb.org/manual/tutorial/install-mongodb-on-debian/
    
### Create your virtual host

See [dev virtual host sample](src/Cocorico/CoreBundle/Resources/doc/virtual-hosts.rst)


## Application install & configuration

### Get Project sources
             
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
    git clone https://[gituser]@xxx.org/xxx/cocorico-xxx.git Symfony
                     
                     
### Create services accounts
    
#### Create your microsoft Translator account:

    See https://www.microsoft.com/translator/getstarted.aspx
    
#### Create your Facebook App:

    See https://developers.facebook.com/docs/apps/register
    Set "Valid OAuth redirect URIs" with [http://cocorico.dev]/[locale]/oauth/fb-login

    
### Install composer

If you don't have Composer yet, run the following command in the root folder of your Symfony project:

For Linux:

    curl -s http://getcomposer.org/installer | php
        
For Windows: 

    php -r "readfile('https://getcomposer.org/installer');" | php
    
    
### Install Cocorico dependencies and set your application parameters

    php composer.phar install
    
Or to speed up:
    
    php composer.phar install --prefer-dist -vvv
    
Or in case of error with tarball (slower):

    php composer.phar install --prefer-source -vvv
   
   
### Configure project

Copy and Paste web/.htaccess.dist and rename it to web/.htaccess. (It's configured by default for dev environment).
       
            
### Initialize the SQL and NoSQL database

#### SQL database initialisation:
 
For Linux:

    chmod 744 bin/init-db
    ./bin/init-db php --env=dev
    
For Windows:

    .\bin\init-db.bat --env=dev
        
        
#### MongoDB initialisation:

For Linux:
    
    chmod 744 bin/init-mongodb
    ./bin/init-mongodb php --env=dev
        
For Windows:
     
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


## Dump assets

    php app/console assets:install --symlink web --env=dev
    php app/console assetic:dump --env=dev


## Browsing the Demo Application

Congratulations! You're now ready to use Cocorico.
http://cocorico.dev/

Admin access is here :

    http://cocorico.dev/admin/dashboard
    super-admin@cocorico.rocks
    super-admin
    
Don't forget to Change your super-admin password. 

Enjoy!


# Versioning

Cocorico follows the Semantic Versioning 2 as far as possible:

> Given a version number MAJOR.MINOR.PATCH, increment the:
>
> MAJOR version when you make incompatible API changes,
>
> MINOR version when you add functionality in a backwards-compatible manner, and
>
> PATCH version when you make backwards-compatible bug fixes.


# Changes

[CHANGELOG.md](CHANGELOG.md) list the relevant changes done for each release.

# Contribute

[CONTRIBUTING.md](CONTRIBUTING.md) explain how you can participate.

# Roadmap

[Roadmap.md](ROADMAP.md) list the planned features.

# Technical documentation

[General](src/Cocorico/CoreBundle/Resources/doc/index.rst)

[Parameters](src/Cocorico/CoreBundle/Resources/doc/parameters.rst)

[Virtual Host](src/Cocorico/CoreBundle/Resources/doc/virtual-hosts.rst)

[Tests](src/Cocorico/CoreBundle/Resources/doc/tests.rst)

[Deployment](src/Cocorico/CoreBundle/Resources/doc/deployment.rst)

# License

Cocorico is released under the [MIT license](LICENSE).

