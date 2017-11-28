UPGRADE to 0.4.1
================


# Table of Contents

-  [Upgrade PHP to 7.1](#upgrade-php-to-71)
-  [Replace PHP mongo extension by mongodb](#replace-php-mongo-extension-by-mongodb)

## Upgrade PHP to 7.1

    

## Replace PHP mongo extension by mongodb
        
1. Disable deprecated PHP mongo extension (http://php.net/manual/en/book.mongo.php)

        sudo php5dismod -s cli mongo
        sudo php5dismod mongo
    
    If you need to keep old mongo extension, don't disable it and upgrade it to at least 1.6.7 version
    
        sudo pecl upgrade mongo

2. Install and enable the PHP mongoDB extension (http://php.net/manual/en/set.mongodb.php)

        #Install for PHP7 as default version
        sudo apt-get install php-mongodb
        
        #Install for PHP5 as default version
        sudo pecl install mongodb
   
        sudo php5enmod -s cli mongodb
        sudo php5enmod mongodb

*Note: Tested with mongodb 1.3.2 version*