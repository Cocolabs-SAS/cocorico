# Windows installation

## Apache 2

Cocorico works also with Nginx.

### Activate following modules

    - mod_headers
    - mod_rewrite
    - mod_ssl 
    
### Install SSL with WAMP

* Install Win32 OpenSSL here http://slproweb.com/products/Win32OpenSSL.html
* Copy/paste libeay32.dll, ssleay32.dll into php bin folder (ex: C:\wamp64\bin\php\phpx.x)

### Generate self signed certificate

    cd C:\wamp\bin\apache\Apache2.4.4\conf

    #Generate private key
    openssl genrsa -aes256 -out key\private.key 2048

    #Remove passphrase
    openssl rsa -in private.key -out key\private.key

    #Generate certificate
    openssl req -new -x509 -nodes -sha1 -key key\private.key -out cert\cocorico.crt -days 36500 -config C:\wamp\bin\apache\apache2.4.4\conf\openssl.cnf
            
### Create your virtual host: 

See [dev virtual host sample](virtual-hosts.md)

## MongoDB

### Install MongoDB

See https://docs.mongodb.com/manual/tutorial/install-mongodb-on-windows/

### Install PHP MongoDB Driver

See http://docs.mongodb.org/ecosystem/drivers/php/
    
**Note:** *For PHP 7 install mongodb extension and not mongo extension*

### Start MongoDB

    bin\start-mongodb.bat "C:\Program Files\MongoDB\data"
            
## PHP
    
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
    - apcu
    
Add the following lines to php.ini:

    curl.cainfo = "pathto/cacert.pem"
    memory_limit = 256M
    upload_max_filesize = 12M 
    post_max_size = 240M

Set the same php timezone to php and php-cli php.ini file:

    date.timezone = UTC  
        
        
## MySQL 

Create your database and your database user

    CREATE DATABASE IF NOT EXISTS {DB} DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
    GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES, REFERENCES ON {DB}.* TO {DBUSER}@localhost IDENTIFIED BY '{DBUSERPWD}'