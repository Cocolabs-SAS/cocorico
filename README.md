# Cocorico

Cocorico is an open source platform to create collaborative consumption marketplaces.
You can find more information about this project on [http://www.cocolabs.io](http://www.cocolabs.io).

This document contains information on how to download, install, and start using Cocorico:

- [Installation](#installation)
- [Versioning](#versioning)
- [Changes](#changes)
- [Contribute](#contribute)
- [Roadmap](#roadmap)
- [Technical documentation](#technical-documentation)
- [License](#license)

**Note:** 

* For Symfony 2.5.x, you need to use the 0.1.x release of the bundle
* For Symfony 2.8.x, you need to use the 0.2.x release of the bundle

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
    git clone https://[gituser]@xxx.org/xxx/cocorico-xxx.git Symfony
                     
                     
### Create services accounts

#### Create your Google API account:

* Go to https://console.developers.google.com/project
* Sign-in with you google account
* Create a new project
* Activate the following APIs:
     - Google Places API Web Service
     - Google Maps JavaScript API
     - Google Maps Geocoding API
* Create a Browser API Key and add your domain to the white list
* Create a Server API Key and add your server IP to the white list

In the next chapter "Install Cocorico dependencies" you will add respectively the "Browser API Key" 
and the "Server API Key" to the `cocorico_geo.google_place_api_key` and `cocorico_geo.google_place_server_api_key` 
parameters in `app/config/parameters.yml`.


Note:
See https://developers.google.com/maps/documentation/javascript/usage?hl=en for Google API usage limits. For example 
at 06 July 2016 the use of Google Map JavaScript API is free until exceeding 25,000 map loads per 24 hours.
    
#### Create your microsoft Translator account:

    See https://www.microsoft.com/translator/getstarted.aspx
    
#### Create your Facebook App:

See [https://developers.facebook.com/docs/apps/register](https://developers.facebook.com/docs/apps/register)
    
* Go to https://developers.facebook.com/quickstarts/?platform=web
* Click on "Skip quick start"
* Click on "Settings" and fill in "App Domains" your domain name. (ex:  xxx.com)
* Click on "Add Platform" > "web site"
* Fill in "Site URL" with your site url. (ex: https://www.xxx.com/)
* Click on "save changes"
* Click on "Advanced".
* Fill in "Valid OAuth redirect URIs" with the urls for the concerned domain and the locales activated.
    Ex for xxx.com with "en" and "fr" as activated locales :
    
        - https://www.xxx.com/en/oauth/fb-login
        - https://www.xxx.com/fr/oauth/fb-login

* Click on "Save changes"
* You will then have to add your "Facebook App id" and "secret" in 
`cocorico.facebook.app_id` and `cocorico.facebook.secret` parameters while composer install described in 
"Install Cocorico dependencies and set your application parameters" chapter below
    
### Install composer

If you don't have Composer yet, run the following command in the root folder of your Symfony project:

For Linux:

    cd Symfony
    curl -s http://getcomposer.org/installer | php
        
For Windows:

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

Anyone and everyone is welcome to contribute. Please take a moment to
review the [guidelines for contributing](CONTRIBUTING.md).

* [Bug reports](CONTRIBUTING.md#bugs)
* [Feature requests](CONTRIBUTING.md#features)
* [Pull requests](CONTRIBUTING.md#pull-requests)

# Roadmap

[ROADMAP](ROADMAP.md) list the planned features.

# Technical documentation

* [General](src/Cocorico/CoreBundle/Resources/doc/index.rst)
* [Parameters](src/Cocorico/CoreBundle/Resources/doc/parameters.rst)
* [Virtual Host](src/Cocorico/CoreBundle/Resources/doc/virtual-hosts.rst)
* [Tests](src/Cocorico/CoreBundle/Resources/doc/tests.rst)
* [Deployment](src/Cocorico/CoreBundle/Resources/doc/deployment.rst)

# License

Cocorico is released under the [MIT license](LICENSE).

