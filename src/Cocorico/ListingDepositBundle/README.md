CocoricoListingDepositBundle
============================

This bundle allow to add deposit to listing. If the offerer set a deposit amount on his listing then the asker will pay 
this amount while requesting a booking. Once the booking is validated and X days after the end of the booking admin user 
will be able to allocate the deposit amounts between asker and offerer through admin dashboard deposit refund menu.
Once allocated admin user will refund and or payed deposit amounts through Mangopay Dashboard if CocoricoMangopayBundle 
is enabled.

X = : cocorico_listing_deposit.booking.deposit_refund_delay parameter value

# Installation

If CocoricoMangopayBundle is enabled then its version must be >= 0.3.1 

## Edit your composer.json:
         
    ...
    "require": {
        ...
    },
    "repositories": [
        {
          "type": "composer",
          "url": "https://packages.cocorico.io",
          "options": {
            "ssl": {
              "verify_peer": true,
              "allow_self_signed": true
            }
          }
        }
    ],

## Copy / paste auth.json.dist to auth.json and add Cocorico account in auth.json in "http-basic" part.
        
    php composer.phar require cocorico/listing-deposit-bundle "^0."
    
    
## Edit `app/config/AppKernel.php` file:

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Cocorico\ListingDepositBundle\CocoricoListingDepositBundle(),
            // ...
        );

        return $bundles;
    }


## Edit `Cocorico/CoreBundle/Resources/config/parameters.yml` file:
    
    parameters:
        cocorico_listing_deposit.booking_deposit_refund.entity_class: Cocorico\ListingDepositBundle\Entity\BookingDepositRefund
        
        
## Update `Cocorico/CoreBundle/Entity/Listing.php` file:

    ...
    
    class Listing extends BaseListing
    {
        ...
        use \Cocorico\ListingDepositBundle\Model\ListingDepositableTrait;
        ...
        
## Update `Cocorico/CoreBundle/Entity/Booking.php` file:

    ...
    
    class Booking extends BaseBooking
    {
        ...
        use \Cocorico\ListingDepositBundle\Model\BookingDepositableTrait;
        ...

## If CocoricoMangoPayBundle is enabled Update `Cocorico/ListingDepositBundle/Entity/BookingDepositRefund.php` file:

    ...
    
    class BookingDepositRefund extends BaseBookingDepositRefund
    {
        ...
        use \Cocorico\MangoPayBundle\Model\BookingDepositPayinRefundMangoPayableTrait;
        use \Cocorico\MangoPayBundle\Model\BookingDepositBankWireMangoPayableTrait;
        ...
                
                
## Update schema:

Dry run:
    
   `php app/console doctrine:schema:update --dump-sql`
        
Update: 
    
   `php app/console doctrine:schema:update --force --env=dev`
   
## Add cron 

    13. Generate Booking Deposit refund (Optional. ListingDepositBundle must be enabled)
        
        `*/15 *  * * *  php <path-to-your-app>app/console cocorico_listing_deposit:bookings:generateDepositRefund --env=dev`
    
    14. Check Booking Deposit refund payments (Optional. ListingDepositBundle must be enabled)
            
        `*/15 *  * * *  php <path-to-your-app> app/console cocorico_listing_deposit:bookings:checkDepositsRefund --env=dev`

# Overriding

To override template or services you can as described in https://symfony.com/doc/2.8/bundles/inheritance.html create a 
new overriding bundle like this for example: 
    
    src\App\CocoricoListingDepositBundle/
    |- DependencyInjection/
    |   |- Compiler/
    |   |   |- OverrideServiceCompilerPass.php
    |   |- AppListingDepositExtension.php
    |   |- Configuration.php
    |- Event/
    |   |- BookingFormSubscriber.php
    |   |- BookingSubscriber.php
    |   |- ListingFormSubscriber.php
    |   |- ...
    |- Resources/
    |   |- views/
    |   |   |- Dashboard/
    |   |   |   |- Listing/
    |   |   |       |- form_deposit.html.twig
    |   |   |- Frontend/
    |   |   |   |- Listing/
    |   |   |   |   |- _show_amount_deposit.html.twig
    |- AppListingDepositBundle.php


## OverrideServiceCompilerPass.php
    
        <?php
        namespace App\ListingDepositBundle\DependencyInjection\Compiler;
        ...
        class OverrideServiceCompilerPass implements CompilerPassInterface
        {
            public function process(ContainerBuilder $container)
            {
                //Change the class of some services
                $definition = $container->getDefinition('cocorico_listing_deposit.listing.form.subscriber');
                $definition->setClass('App\ListingDepositBundle\Event\ListingFormSubscriber');
                ...
            }
        }
        
## AppListingDepositBundle.php

        <?php
        namespace App\ListingDepositBundle;
        ...
        class AppListingDepositBundle extends Bundle
        {
            public function build(ContainerBuilder $container)
            {
                parent::build($container);
        
                $container->addCompilerPass(new OverrideServiceCompilerPass());
            }
        
            public function getParent()
            {
                return 'CocoricoListingDepositBundle';
            }
        }