# Deployment

Deployment use https://github.com/hpatoio/DeployBundle

1. Configuration

    * Parameters:
        Set in your parameters.yml the following parameters:
    
            deploy_host: cocorico.prod
            deploy_dir: /var/www/cocorico.prod/Symfony
            deploy_user: cocorico
    
    * php.ini:
        Add this to your php.ini:
    
            extension=mongodb.so

2. Deploy

    * Dry-Run:
    
            php app/console project:deploy prod
    
    * Real:
    
            php app/console project:deploy --go prod
    
     For more informations see https://github.com/hpatoio/DeployBundle

3. Post deployment tasks

    * Install/Update Vendors:
    
            export SYMFONY_ENV=prod && php composer.phar install --no-dev --prefer-dist --optimize-autoloader
            php composer.phar dump-autoload --optimize
    
    * Clear Symfony Cache:
    
            php app/console cache:clear --env=prod --no-debug
    
    * Dump Assetic Assets:
    
            php app/console assetic:dump --env=prod --no-debug

4. Add crons

    See [crons documentation](crons.md)

