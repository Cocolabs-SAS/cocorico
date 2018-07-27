# Linux Installation 

## Apache 2 (or Nginx)

Activate following modules

    - mod_headers
    - mod_rewrite
    - mod_ssl

Create your virtual host: [dev virtual host sample](virtual-hosts.md)


## MongoDB 

### Install MongoDB 

See https://docs.mongodb.com/manual/administration/install-on-linux/

### Install PHP MongoDB Driver 

See http://docs.mongodb.org/ecosystem/drivers/php/
    
**Note:** *For PHP 7 install mongodb extension and not mongo extension*

### Start MongoDB 

See http://docs.mongodb.org/manual/tutorial/install-mongodb-on-debian/
    
    
## PHP
    
Install PHP 7.1

Activate following extensions:

    - curl (>= 7.36)
    - intl
    - fileinfo
    - openssl
    - soap
    - exif
    - mongodb
    - imagick
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