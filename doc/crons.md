# Crons

Add this commands to your cron tab and don't forget to set the same PHP timezone "UTC" 
in  the php.ini file of php and php-cli.

## Required

1. Currencies update:

    `17 0 * * * php <path-to-your-app>app/console cocorico:currency:update --env=dev`

2. Bookings expiration:

    `0 */1 * * * php <path-to-your-app>app/console cocorico:bookings:expire --env=dev`

3. Bookings validation:

    `0 */1 * * * php <path-to-your-app>app/console cocorico:bookings:validate --env=dev`

4. Bookings bank wires checking:

    `0 */1 * * * php <path-to-your-app>app/console cocorico:bookings:checkBankWires --env=dev`

5. Bookings expiring alert:

    `*/15 * * * * php <path-to-your-app>app/console cocorico:bookings:alertExpiring --env=dev`

6. Bookings imminent alert:

    `*/15 * * * * php <path-to-your-app>app/console cocorico:bookings:alertImminent --env=dev`

7. Listings calendar update alert:

    `0 0 27 * * php <path-to-your-app>app/console cocorico:listings:alertUpdateCalendars --env=dev`


## Optionals

1. Listings platform notation computing (Optional. ListingSearchBundle must be enabled):
        
    `30 2 * * * php <path-to-your-app>app/console cocorico_listing_search:computeNotation --env=dev`
    
2. Accept or refuse bookings from SMS (Optional. SMSBundle must be enabled)
    
    `* * * * *  php <path-to-your-app>app/console cocorico_sms:bookings:acceptOrRefuseFromSMS --env=dev`

3. Check phone user from SMS (Optional. SMSBundle must be enabled)
    
    `* * * * *  php <path-to-your-app>app/console cocorico_sms:users:checkPhoneFromSMS --env=dev`

4. Alert user if new listings are found (Optional. ListingAlertBundle must be enabled)
    
    `0 3 * * *  php <path-to-your-app>app/console cocorico_listing_alert:alertNewListingsFound --env=dev`

5. Generate Sitemap (Optional. ListingSeoBundle must be enabled)
    
    `0 4  * * *  php <path-to-your-app>app/console cocorico_seo:sitemap:generate --env=dev`

6. Generate Bookings deposit refund (Optional. ListingDepositBundle must be enabled)
        
    `*/15 *  * * *  php <path-to-your-app>app/console cocorico_listing_deposit:bookings:generateDepositRefund --env=dev`
        
7. Check Booking Deposit refund payments (Optional. ListingDepositBundle must be enabled)
            
    `*/15 *  * * *  php <path-to-your-app> app/console cocorico_listing_deposit:bookings:checkDepositsRefund --env=dev`

