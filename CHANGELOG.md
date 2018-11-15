CHANGELOG
=========

This changelog references the relevant changes done in this project.

This project adheres to [Semantic Versioning](http://semver.org/) 
and to the [CHANGELOG recommendations](http://keepachangelog.com/).


## [Unreleased]

### Added
- Add bookings accessor by user type in User entity and CS

### Fixed
- Optimise user and listing fields in almost admin edition pages (bookingBankWire, PayinRefund, Booking, ...)
- Add person type in user fixtures
- Fix listing address geolocation while listing deposit and listing address edition when no click on validate address
- Add nationality field on registration form

### Changed
- Remove unused registration handling in new listing form handler
- Set listing user in listing form handler for listing deposit
- Remove unused registration handling in new booking form handler
## [0.7.0] - (2017-03-27)

### Added
- Add new login / registration step before new listing / new booking action
- Update listing characteristics values and types management in admin
- Add MessageBundle sms notification and update sms-bundle to  v0.3
- Add geolocation country filtering

### Fixed
- Remove error when booking duration is less than 1 hour in booking price form
- Remove errors fields message on new booking page when a secondary submission (Voucher, Delivery, ...) is done

### Changed
- Disable 301 redirect when offerer go to the new booking page of its listing
- Change MessageBundle mail notification method


## [0.6.0] - 2018-10-05

### Added
- Add Booking user delivery address
- Add delivery address in new booking form
- Add listing characteristics values and types management in admin
- Add form type for user entity
- Decouple user login/registration from new listing and booking
- Force authentication before listing deposit and booking request
- Add user legal type

### Fixed
- Fix listing favorites search request persisting
- Fix SMS calls when SMS Bundle is not enabled
- Fix SQL for booking expiration and expiring alert
- Fix Google "Browser API keys cannot have referer restrictions when used with this API"
- Fix phone_prefix user form field case

### Changed
- Split each dashboard profile actions in multiple controllers
- Factorize profile contact edition
- Change profile payment edition to bank account edition
- Refactor booking actions checking 
- Disable booking cancelation if booking has discount voucher amount
- Accept parameters value equal to 0 from ConfigBundle
- Add a no results message
- Set query hydration to HYDRATE_ARRAY on getHighestRanked 
- Disallow cocorico_user_login_check urls into robots.txt

## [0.5.0] - 2018-03-20

### Added
- Add minimal tests
- Add multi parameters on Booking validator messages
- Add static property access twig function
- Add Booking in Admin Review
- Add security voter to voucher page access
- Add Carrier bundle hatchback field to booking admin
- Add listing search engine on user profile page
- Add error message on listing calendar edition
- Set booking status to STATUS_PAYMENT_REFUSED when booking can not be validated
- Add booking acceptation delay 
- Add listing show query overriding
- Add parameter type in CocoricoConfigBundle
- Add LISTING_SEARCH_HIGH_RANK_QUERY and LISTING_SEARCH_BY_IDS_QUERY events

### Fixed
- Fix js escaping on result
- Fix #116 issue (update "unread" message in dashboard)
- Fix Booking expiration alert
- Replace depreciated 'intention' by 'csrf_token_id' in BookingNewType
- Fix search categories field displaying
- Fix #135 issue error when add to favourite
- Fix booking validation date by adding time to date verification
- Fix discount unicity issue while adding / removing discounts
- Fix similar listings
- Fix favorites listings
- Fix googlemaps MarkerWithLabel issue 393

### Changed
- Update doc
- Move some GlobalHelper methods to Utils\PHP class
- Handle translator provider errors
- Change `cocorico.booking.min_start_time_delay` parameter unit from hours to minutes
- Add minor corrections to ReviewBundle
- Disable listing deposit and new booking to admin user
- Add phone to registration form
- Add phone and email verification message on profile edition 
- Decouple SMSBundle
- Factorize and CS search

## [0.4.1] - 2017-11-22

### Added
- Add PHP 7.1 compatibility
- Add listing location in listing admin
- Add Carrier Bundle compatibility

### Fixed
- Fix user bad credential translation

### Changed
- Update doc for PHP7 and Docker usage
- Facilitate listing search form filters twig modifications in result page

## [0.4.0] - 2017-11-13

### Added
- Add rss feeds to home page
- Add "guzzlehttp/guzzle" to composer.json for DistanceMatrix usage
- Pre-filled reservation fields with the upcoming availability
- Add time_hours_available parameter and relative functionalities
- Add default users time zone parameter and relative functionalities
- Add missing listing link in reviews list
- Add rotating_file handler to monolog
- Add flags icons
- Add function to get culture code from locale
- Strip private info in all user texts (listing, user, reviews, messages)
- Add createdAt index in all timestampable entities

### Fixed
- Fix bug #71 - Listing status available while duplication
- Fix data fixtures for listing geo location
- Fix phone_prefix default value on ProfileContactFormType
- Fix missing breadcrumbs in Listing Categories and Location edition
- Fix hour removing bug in search form
- Fix search form css in day mode
- Fix time zone on booking minimum start time error displaying
- Fix end time of TimeRange validation by removing end time relation with hours_available parameter
- Fix some Symfony 2.8 depreciation
- Fix booking cancelation policy type checking while refunding by verifying also the booking start time
- Fix IPInfoDB dataType of ajax call by changing it to "json" instead of "jsonp"
- Fix Country name in booking new
- Fix missing translations in admin
- Fix listing delivery and options missing on booking new
- Fix booking pre-fill dates on BookingPrice for booking.min_start_time_delay different of 24
- Fix facebook login popup language
- Fix out of memory in admin forms containing a lot of listings
- Fix multi categories displaying in listings search result page and home page
- Fix JMS extraction on admins for subject equal to null
- Fix listing reviews order displaying in frontend and dashboard (listing, user)
- Fix GeocodingController createAction in listing show page
- Fix add_time_unit_text filter for duration < 1 hour
- Fix responsive of search form
- Fix cron docs
- Fix new booking form handler without BOOKING_NEW_FORM_PROCESS listeners

### Changed
- Upgrade Microsoft Translator API request method from Bing to Azur 
- Change homepage by extending to 100% visual image
- Change monolog action_level to critical
- Factorize DateRange and TimeRange creation in ListingSearchRequest, BookingPriceFormHandler and BookingFormHandler
- Remove dates and times synchronisation from booking price form to listing search form
- Change the maximum date time of the booking acceptation (and refusal )
- Remove duplicate datetimepicker css in all.css
- Enhance getNbUnReadMessages
- Extract js libraries from jquery.main.js
- Replace map markers spider by slider in InfoBox for listings with same locations
- Add markers and cluster overlay effect while listing mouseover
- Update ListingSearchBundle composer dependency to v0.2.2 (Listing search by distance and search extension when insufficient results)
- Change delivery twig templates path scheme for overriding purposes
- Remove bootstrap duplicated datetimepicker from bootstrap.min.js
- Time form field enhancement (timepicker min hour available, nb_minute form label hiding/displaying, time search form error )
- Use flags icons into images/flags folder
- Uniformize users name truncation (ex : Firstname L.)
- Replace method to know which bundles is enabled by using kernel.bundles instead of EntityManager methods
- Update deployment.rst


## [0.3.4] - 2017-05-31

### Added
- Add ListingCategoryFieldBundle support
- Add filter button in result page
- Add jsqueeze JS compiler to compile all js in prod
- Add css minifycsscompressor filter on fullcalendar.css
- Add csrf option to hwi_oauth
- Add characteristics tooltip in offerer dashboard
- Optimisation of mongodb prices and status edition and search
- Add DeliveryBundle support
- Add NumberRange Form type
- Add CAST DQL function
- Add support for search by range values for fields of type numeric and date in ListingCategoryFieldBundle

### Fixed
- Fix jquery warning
- Fix categories displaying
- Fix listing duplication error when listing doesn't have availabilities
- Update guzzlehttp/guzzle to 5.3.1 to Fix Security HTTP Proxy header vulnerability (CVE-2016-5385)
- Fix translations tabs if locales number is equal to 1
- Fix ConfigBundle LoadDataFixture when no parameters are allowed to be edited
- Fix mongodb times storing and search by time range
- Fix init-db command (https://github.com/doctrine/DoctrineBundle/issues/561)

### Changed
- Split listing categories and location dashboard edition and ajaxify categories edition
- New booking page dates displaying
- Change Readme about DB grant
- Create ListingSearchFormBuilder and use it for categories search instead of ListingFormSubscriber 
- Enhance date range options in DateRangeType and in Jquery Datepicker


## [0.3.3] - 2017-04-26

### Added

### Fixed
- Fix admin translation
- Fix duplicate booking options in admin
- Fix similar listings back link
- Fix design related change on manual translations fields 
- Fix user image order in listing result
- Fix Jquery CDN fallback
- Fix duplicate listing dashboard forms name (to display them into Web Profiler)

### Changed
- Change listing availabilities route translation
- Change doc
- Remove arrows in user language select list
- Allow all countries in listing deposit
- Remove SBO characteristics description requirement
- Do not display bill link in asker payments if asker fees are 0
- Add error icon in translation tabs in case of error
- Add Google API account creation explanation into README
- Set disabled property to true in UserAdmin for Mangopay related fields
- Set disabled property to true in ReviewAdmin for almost review fields

### Deprecated



## [0.3.2] - 2017-01-25

### Added
- Add booking policy block informations in listing show page
- Add new LanguageFiltered type to replace LocaleType dynamically poor for multi languages
- Guess lang to translation for auto translation

### Fixed
- Fix User country and nationality default values
- Fix arrows bug display in from and to translations fields
- Expire bookings with start date greater than today date
- Fix duplicate mails send while subscription
- Fix subscription validation page title 
- Fix unused email subject param in registration mail

### Changed
- Change duplicate h1 to h2 in listing show page
- Rename AddressFormType To UserAddressFormType
- Replace LocaleType and LanguageType usages by LanguageFiltered
- Optimize createNewListingThread call

### Deprecated



## [0.3.1] - 2016-12-24

### Added
- Add geo localized breadcrumbs
- Add "Access to site" link in admin
- Add SeoBundle functionality : display seo content on listing search result page
- Add SeoBundle functionality : Sitemap generation
- Add CMSBundle functionality : Footer links management
- Add SeoBundle functionality : JSON-LD Markups data
- Add form tag to message in booking show page

### Fixed
- Fix duplicate rows in `ListingSearchManager->getFindQueryBuilder`
- Fix user profile urls translation
- Fix missing label_catalogue on some Bundles
- Fix admin "go to site" link by disassociating it from translations activation
- Fix missing admin translations
- Fix GeoBundle findAll repositories methods conflict with default findAll method in SonataAdmin
- Fix search form categories list by adding missing fields in findQueryBuilder
- Fix selected countries validation while listing deposit when all countries are enabled
- Fix voucherIsEnabled method when ListingOption bundle is enabled
- Fix characteristics admin translations
- Fix user address fields requirement

### Changed
- Replace condition voucherIsEnabled by mangopayIsEnabled in BookingManager->findPayedByAsker
- Factorize user login in listing deposit form
- Hide ratings in marker popin when no ratings
- Set first name and last name required in user admin form
- CS
- Disable Curl SSL VERIFYHOST in non prod env
- Uniformize breadcrumbs management
- Move bundles services loading from bundles config.yml to bundles extension (UserBundle, PageBundle)
- Change and enhance placeholder method for translations form fields
- Enhance PageBundle translations
- Change Listing repository findPublishedListing method
- Remove all Microdata markups content
- Add sitemap.xml to rsync_exclude.txt
- Add and setting bookings number as asker/offerer in User entity
- Add command to reset bookings number as asker/offerer
- Add drop down icon to flags and currencies switchers
- Change packages repository method in composer.json
- Remove autoescape in show_voucher
- Change duplicate h1 to h2 in listingshow page 
- Move capitalize select box text css in all-override.css
- Change error fields name in edit_contact

## [0.2.6] - 2016-11-29

### Added
- Add multiple methods to geo localize user and add his location on listing location search field
- Set last listing address as default address while listing deposit
- Add user listings link in SBO users view list

### Fixed
- Fix missing user zip address while user geo localization
- Fix invalid country in listing location

### Changed
- Change voucherIsEnabled by mangopayIsEnabled in BookingManager->findPayedByAsker and minor things
- Disable Curl SSL VERIFYHOST in non prod env

### Deprecated


## [0.2.5] - 2016-10-19

### Added
- Add listing markers cluster on map
- Add markers spidering when overlaps

### Fixed
- Fix duplicate search query by removing iterator call in twig
- Fix place autocomplete missing in user profile page

### Changed
- Disable web profiler in staging env
- Display all markers of a listing search request on the map independently of pagination
- Change listings marker aspect on the map depending on whether they are on the current page

### Deprecated


## [0.2.4] - 2016-10-05

### Added

### Fixed
- Fix reviewer name in dashboard reviews
- Fix participant name in reservation thread messages when asker cancel booking
- Fix search by date without time in not day mode
- Fix timepicker compatibility in mobile device
- Fix google map infobox.js remote access disabled
- Fix admin listing images upload by disabling it
- Fix sensio/distribution-bundle / composer 1.1.0 type hint compatibility

### Changed
- Display pay button in admin bank wires if its status is todo
- Default user phone prefix to +33
- Enhance ReviewBundle
- Footer link
- gc_probability setted to 0 for prod env

### Deprecated


## [0.2.3] - 2016-08-23

### Added
- Add booking duration in booking price
- Add DQL MySQL timestamp diff function
- Add ReportBundle support

### Fixed
- Fix flash bags on review manager 
- Fix duration computing without time range 

### Changed
- CS 

### Deprecated


## [0.2.2] - 2016-07-06

### Added
- Add Min/Max listing duration informations on listing show page
- Add time picker in time type fields
- Add timepicker parameter

### Fixed
- Fix error occurring when a date is filled without the other one
- Fix error occurring when a time is filled without the other one
- Fix CS in common.js > getNbUnReadMessages
- Fix Translate manager with missing key or secret param
- Fix ie edge/ipad timepicker compatibility

### Changed
- Reduce number of twig core extension service args
- Factorize and simplify Javascript date and time management
- Fontello icons code
- Remove unused glyphicons halflings
- Remove "updated at" column in admin booking list
- Change default time_unit parameter to 60 min

### Deprecated
- Fix TimeRange form type sf 2.8 depreciation
- Remove TimeHidden form type
 

## [0.2.1] - 2016-06-28

### Fixed
- Fix sensio/distribution-bundle / composer 1.1.0 type hint compatibility
- Fix google map infobox.js remote access disabled
- Disable manual translations on listing and user edition when there is only one locale available on platform
- Set SMS default locale equal to app default locale
- Add booking status "new" criteria to SMS booking acceptation
 
### Changed
- Change listing search by categories by including listings without categories
- Update doc/index.rst


## [0.2.0] - 2016-04-08

### Added
- Add DoctrineMigrationBundle
      
### Changed
- Change version of sonata-project/doctrine-orm-admin-bundle to dev-master instead 2.3 to resolve AuditBlockService
- Change version of sonata-project/admin-bundle to 2.4@dev instead 2.3 to resolve AuditBlockService
- Change version of "knplabs/doctrine-behaviors" from dev-master to ^1.3 release
- Change version of "hwi/oauth-bundle" from "0.4.*@dev" to "^0.4"
- Update "egeloen/ckeditor-bundle" from "~3.0" to "^4.0"
- Update "helios-ag/fm-elfinder-bundle" from "~5.0" to "^6.0"
- Update "fzaninotto/faker" from "1.5.*@dev" to "^1.5"
- Update "jms/di-extra-bundle" from "1.4.*@dev" to "^1.7"
- Update "willdurand/geocoder-bundle" from "3.1.*@dev" to "^4.1"
- Change CocoricoGeoBundle to be compatible with "willdurand/geocoder-bundle" 4.1
- Change credit link
- Change doc index.rst
- Change listing_search_min_result value from 5 to 1
- Change page fixture description

### Deprecated

See https://gist.github.com/mickaelandrieu/5211d0047e7a6fbff925 and 
https://github.com/symfony/symfony/blob/2.8/UPGRADE-3.0.md

- Renamed AbstractType::setDefaultOptions to AbstractType::configureOptions
- Renamed AbstractType::getName to AbstractType::getBlockPrefix
- Renamed @translator service to @translator.default
- Replace @request service call by Request object injection in the action method
- Replace form.csrf_provider service call by security.csrf.token_manager service call
- Replace intention option by csrf_token_id option in security.yml 
- Replace intention form option resolver by csrf_token_id in forms
- Replace Twig initRuntime method by adding needs_environment = true in filters arg functions
- Replace setNormalizers by setNormalizer
- Change setAllowedValues to modify one option at a time
- Add `choices_as_values => true `to the ChoiceType and flip keys and values of choices option
- Split security.context service into security.authorization_checker and security.token_storage
- Rename `precision` option to `scale`
- Remove scope from service definitions
- Replace `sameas` by `same as` in Twig templates
- Replace `form` tag by twig `form_start` function 
- ... 

### Fixed
- Add `__toString` to Contact entity 
- Fix admin datagrid filter status for BookingPayinRefund
- Gmap Markers autoescape html
- Add custom DoctrineCurrencyAdapter to fix Lexik currency bundle convert sql request
- Listing discount editions error displaying
- Change listing category parent label in admin
- Add required attributes to page admin form fields
- Fix links translations in error pages
- Fix find bookings payed by asker when MangoPayBundle is not enabled

## [0.1.1] - 2016-04-04

### Added
- Add currency on booking amount error message 
- Add fees help in sonata admin for bank wire
- Add default currency on admin BankWire "Debited funds" field

### Fixed
- Fix duplicate error message on new booking 
- Fix bookings refusing while booking acceptation
- Fix currency format on all bills
- Fix admin currency vertical align on price fields 
- Fix admin listing "rules" field requirement 
      
### Changed
- Update documentation
- Change min listing price parameter to 1 (default currency)
- Change composer.json support section


## [0.1.0] - 2016-03-23

### Added

- First commit

