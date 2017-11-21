UPGRADE to 0.4.1
================

# Table of Contents

-  [Upgrade PHP 5 to 7.1](#Upgrade PHP 5 to 7.1)

## Upgrade PHP 5 to 7.1

 * Disable deprecated PHP mongo extension (http://php.net/manual/en/book.mongo.php)
 
        ;extension=mongo.so
    
 * Install and enable the PHP mongoDB extension (http://php.net/manual/en/set.mongodb.php)
        
        #Install
        sudo apt-get install php-mongodb
        #In case of error
        sudo pecl install mongodb
       
   
        #Enable extension
        extension=mongodb.so