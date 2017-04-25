General Technical Informations
================================

Crons
-----

Add this commands to your cron tab and don't forget to set the same php timezone "UTC" to php and php-cli php.ini file.

1. Currencies update:

    `17 0 * * * php <path-to-your-app>app/console cocorico:currency:update --env=dev`

2. Bookings expiration:

    `0 */1 * * * php <path-to-your-app>app/console cocorico:bookings:expire --env=dev`

3. Bookings validation:

    `0 */1 * * * php <path-to-your-app>app/console cocorico:bookings:validate --env=dev`

4. Bookings bank wires checking:

    `0 */1 * * * php <path-to-your-app>app/console cocorico:bookings:checkBankWires --env=dev`

5. Bookings expiring alert:

    `*/15 * * * php <path-to-your-app>app/console cocorico:bookings:alertExpiring --env=dev`

6. Bookings imminent alert:

    `*/15 * * * php <path-to-your-app>app/console cocorico:bookings:alertImminent --env=dev`

7. Listings calendar update alert:

    `0 0 27 * * php <path-to-your-app>app/console cocorico:listings:alertUpdateCalendars --env=dev`

8. Listings platform notation computing (Optional. ListingSearchBundle must be enabled):
        
    `30 2 * * * php <path-to-your-app>app/console cocorico_listing_search:computeNotation --env=dev`
    
9. Accept or refuse bookings from SMS (Optional. SMSBundle must be enabled)
    
    `* *  * * *  php <path-to-your-app>app/console cocorico:bookings:acceptOrRefuseFromSMS --env=dev`

10. Check phone from SMS (NOT IMPLEMENTED. Optional. SMSBundle must be enabled)
    
    `* *  * * *  php <path-to-your-app>app/console cocorico_user:checkPhoneFromSMS --env=dev`

11. Alert user if new listings are found (Optional. ListingAlertBundle must be enabled)
    
    `* *  * * *  php <path-to-your-app>app/console cocorico_listing_alert:alertNewListingsFound --env=dev`

12. Generate Sitemap (Optional. ListingSeoBundle must be enabled)
    
    `0 4  * * *  php <path-to-your-app>app/console cocorico_seo:sitemap:generate --env=dev`


        
Translations
------------

Views, Forms, Constraints messages are translated from JSM Translation bundle.

- To ignore some translations add `/** @Ignore */` above the text to not translate.
- To make some entity contents translatable by JSM prefix text with entity.
    Ex : entity.custom.name
- To add some entity contents untranslated by JMS see Cocorico\CoreBundle\Translator\EntityExtractor
- To make some admin contents translatable by JSM prefix text with admin.
    Ex : admin.listing.title
- In case of problem connection restart Apache then MongoDB


Extract translations
--------------------

To extract translations keys from whole application  :
    In english languages :
        `php app/console translation:extract en --config=cocorico`

To extract translations keys from external bundle :
    In english languages :
        `php app/console translation:extract en --bundle=CocoricoListingAlertBundle`
        
To translate keys :
    Go to : http://cocorico.dev/[admin]/__translations/

Do not generate entities
------------------------

Do not generate entities from generate:entities because of this doctrine remark and the use of mapped super class

> This command is not suited for constant usage. It is a little helper and does not support all the mapping edge cases 
> very well. You still have to put work in your entities after using this command.


MongoDB
-------

Listing availabilities and prices are stored in MongoDB.
To create schema: php app/console doctrine:mongodb:schema:create
To execute command : use cocorico; show collections;db.run.Command(Your command)


Global Twig variables
---------------------

Global twig variables are defined in `Cocorico\CoreBundle\Twig\CoreExtension.php` and in 
`Cocorico/CoreBundle/Resources/config/config.yml`

To create a new global twig variable, go to `app/config/parameters.yml` or `Cocorico/CoreBundle/Resources/config/parameters.yml` and define your parameter.
Do it also for `parameters.yml.dist` and `parameters_test.yml.dist` if this parameter is added to `app/config/parameters.yml` (environment dependant).

Go to `app/Resources/config/Services/twig_extension.yml`.
Add your param into the service named `cocorico.twig.cocorico_extension`.
Open CoreExtension.php and go to `getGlobals` function.
Add your parameter in array with the same syntax than others and in the same order than in your 
`cocorico.twig.cocorico_extension service`
Declare this param as protected var, add the comments showing the param type and add it in the `__construct` function.
Don't forget to respect the params order or it will fail.
You can now use your param as global twig variable


Prices
------

All prices (listing, booking) are stored in cents and in the default app currency.
To display them in the views it's necessary to divide them by 100. Some methods (`$listing->getPriceDecimal`) exists 
to get the price in decimal.


Currency
--------

Prices edition fields are expressed in the application default currency. 
So in twig templates these fields have to displayed the default currency 
symbol like this :

`{{ currencySymbol(defaultCurrency) }}`

Prices displaying (like listing prices in search result page) are done in the current currency.
So in twig template it is done like this :

`{{ currencySymbol(currentCurrency) }}`



VAT
---

Listing price fixing can be set with or without VAT through the parameter `cocorico.include_vat` value.

If it's setted to true then:

- listing price fixing include VAT
- all other prices like booking, bank wire, ... include also VAT

If it's setted to false then:

- listing price fixing don't include VAT
- Most of asker relative prices are displayed including VAT
- Most of offerer relative prices are displayed excluding VAT


Fees
----

The platform can take fees on amount of each transactions.


Refund
------

Asker cancellation example:
    - Booking amount excl fees = 95€
    - Asker fees = 10€
    - Offerer fees = 5€
    - Amount payed by asker = 110€
    
    - Amount refunded is 100%: Offerer fees payed by asker are refunded to asker.
        - Amount refunded to asker = 95€ * 1 + 5€ = 100€
        - Amount transferred to offerer wallet = 95€ * (1 - 1)  = 0€
        - Fees taken by the platform = 10€
        
    - Amount refunded is 50%: No fees refunded
        - Amount refunded to asker = 95€ * 0.5  = 47.50€
        - Amount transferred to offerer wallet = 95€ * (1 - 0.5)  = 47.50€
        - Fees taken by the platform = 15€
    
    - Amount refunded is 0%: No fees refunded
        - Amount refunded to asker = 95€ * 0 = 0€
        - Amount transferred to offerer wallet = 95€ * (1 - 0) = 95€
        - Fees taken by the platform = 15€

Time unit
---------

Time unit depend on value of some parameters.
See Cocorico/CoreBundle/Resources/config/parameters.yml to view default values.

Day mode:

    - cocorico.time_unit: 1440
    - cocorico.time_unit_allday: true

Night mode:

    - cocorico.time_unit: 1440
    - cocorico.time_unit_allday: false

Hour mode:

    - cocorico.time_unit: 60
    - cocorico.time_unit_allday: true

Here are other time unit relative parameters:

Allow single day (start day = end day) booking request and listing search
If days_max is set to 1 then must be set to true

    - cocorico.booking.allow_single_day: true
    - cocorico.booking.end_day_included: true

Include end day in booking request and listing search and disable single day booking request and listing search
If days_max is set to 1 then must be set to true

    - cocorico.booking.allow_single_day: false
    - cocorico.booking.end_day_included: true

Days display mode. (range or duration)

    - cocorico.days_display_mode: duration

Times display mode. (range or duration). No effect if time unit is day

    - cocorico.times_display_mode: duration

Max search, booking time unit number. Min 1. Max value of times max depends on time unit: 24 if time unit is hour.
Not needed if time unit is day.
Ex for 8 hours with time_unit equal to 60 minutes:
    - cocorico.times_max: 8


Examples:

    Night mode:

        - cocorico.time_unit: 1440
        - cocorico.time_unit_allday: false
        - cocorico.booking.allow_single_day: false
        - cocorico.booking.end_day_included: false
        - cocorico.days_display_mode: duration

    Day mode:

        - cocorico.time_unit: 1440
        - cocorico.time_unit_allday: true
        - cocorico.booking.allow_single_day: false
        - cocorico.booking.end_day_included: false
        - cocorico.days_display_mode: duration

    Hour mode:

        - cocorico.time_unit: 60
        - cocorico.time_unit_flexibility: 8
        - cocorico.time_unit_allday: true
        - cocorico.days_display_mode: duration
        - cocorico.times_display_mode: duration
        - cocorico.days_max: 1
        - cocorico.times_max: 8
        - cocorico.booking.allow_single_day: true
        - cocorico.booking.end_day_included: true


Breadcrumbs
-----------

You need to add your breadcrumbs in `src/Cocorico/CoreBundle/Resources/content/breadcrumbs.yml`

Format will be :

    route_name:
        -
            text: 'home' # text is translatable it will be extracted to breadcrumbs.en.xliff
            route: 'route_name' #JMS I18n translatable
        -
            text: 'list'
            path: '#'   # it will be non route and non translatable path which will be used directly


Mails
-----

* General

Mail content are defined by two keys xxx_subject and xxx_message with xxx specific for each mail.
Each key is translated through JMS `https://cocorico.dev/_translations/`
Translation domain is `cocorico_mail`.
In dev mode :

By default emails send are stored in `app/spool/default` folder.
if the parameter "debug_redirects" is set to true the email send will also be displayed in the profiler.
This works only for email not send through ajax.

Example for mails send when a new booking is accepted:

Mail templates:

    * Asker : `Cocorico/CoreBundle/Resources/views/Mails/accepted_booking_asker.txt.twig`
    * Offerer : `Cocorico/CoreBundle/Resources/views/Mails/accepted_booking_offerer.txt.twig`
    
Mail send from: `Cocorico/CoreBundle/Form/Handler/Dashboard/BookingFormHandler.php`


* Core mails

The core mails has send through service `Cocorico/CoreBundle/Mailer/TwigSwiftMailer.php`.

New mails method must be declared in `Cocorico/CoreBundle/Mailer/MailerInterface.php`

Mails templates are defined in `Cocorico/CoreBundle/Resources/config/Services/mailer.yml`.


* User mails : (registration, password resetting, registration confirmation)

The user mails has send through service `Cocorico/UserBundle/Mailer/TwigSwiftMailer.php`

New mails method must be declared in `Cocorico/UserBundle/Mailer/MailerInterface.php`

Mails templates are defined in `Cocorico/UserBundle/Resources/config/services/mailer.xml`


Extra Bundle Routing
--------------------

To add extra bundle routing to the app add new bundle routing path to `Cocorico/CoreBundle/Routing/ExtraBundleLoader.php`


WkHtml2PDF Install
------------------

    cd mytmpfolder
    wget http://download.gna.org/wkhtmltopdf/0.12/0.12.3/wkhtmltox-0.12.3_linux-generic-amd64.tar.xz
    sudo tar xvf wkhtmltox-0.12.3_linux-generic-amd64.tar.xz
    sudo mkdir /usr/local/bin
    sudo mv wkhtmltox/bin/wkhtmlto* /usr/local/bin/