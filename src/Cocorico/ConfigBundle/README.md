Cocorico Config Bundle
========================

This bundle allow to override parameters.yml from database.
This document contains information on how to install, and start using CocoricoConfigBundle.

Step 1: Enable the Bundle
-------------------------

Then, enable the bundle by adding the following line in the `app/AppKernel.php` file of your project:

    <?php
    // app/AppKernel.php
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new Cocorico\ConfigBundle\CocoricoConfigBundle(),
            );
            
            // ...
        }
        // ...
    

Step 2: Update Database
-----------------------

    php app/console doctrine:schema:update --force
    


Step 3: Set parameters to override
-------------------------------------

The parameter type is the form type name of the field in admin

Example:
    
    parameters:
        cocorico_config.parameters_allowed:
            cocorico.fee_as_asker:
              type: 'percent'
            cocorico.fee_as_offerer:
              type: 'percent'
    
    
    
Step 4: Load fixtures
---------------------

Add parameters into database :

    php app/console doctrine:fixtures:load --fixtures=src\Cocorico\ConfigBundle\DataFixtures\ORM\ --append
    

Step 5: Clear cache
-------------------

Clear cache each time database parameters are changed if you want to load their new values :

    php app/console cache:clear
    
    
Thanks
------
This bundle is inspired from https://github.com/egzakt/UnifikDatabaseConfigBundle   
Thanks to hubert perron and other contributors of UnifikDatabaseConfigBundle
    