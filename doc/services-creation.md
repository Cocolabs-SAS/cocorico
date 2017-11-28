# Create services accounts

## Create your Google API account

* Go to https://console.developers.google.com/project
* Sign-in with you google account
* Create a new project
* Activate the following APIs
    - Google Places API Web Service
    - Google Maps JavaScript API
    - Google Maps Geocoding API
* Create a Browser API Key and add your domain to the white list
* Create a Server API Key and add your server IP to the white list

In the next chapter "Install Cocorico dependencies" you will add respectively the "Browser API Key" 
and the "Server API Key" to the `cocorico_geo.google_place_api_key` and `cocorico_geo.google_place_server_api_key` 
parameters in `app/config/parameters.yml`.


*Note: Starting January 31 2018 the Places Web Service API will no longer accept API Keys with HTTP Referer usage restrictions.*
*See https://developers.google.com/maps/faq#switch-key-type.*
    
    
## Create your microsoft Translator account

    See https://www.microsoft.com/translator/getstarted.aspx. 
    
*Note: Free for 2 millions of characters by month, after it is 10$ per million characters.*
*See https://azure.microsoft.com/en-us/pricing/details/cognitive-services/translator-text-api/*
    
## Create your Facebook Login App

See [https://developers.facebook.com/docs/apps/register](https://developers.facebook.com/docs/apps/register)
    
* Go to https://developers.facebook.com/quickstarts/?platform=web
* Click on "Skip quick start"
* Click on "Settings" and fill in "App Domains" your domain name. (ex:  xxx.com)
* Click on "Add Platform" > "web site"
* Fill in "Site URL" with your site url. (ex: https://www.xxx.com/)
* Click on "save changes"
* Click on "Advanced".
* Fill in "Valid OAuth redirect URIs" with the urls for the concerned domain and the locales activated.
    Ex for xxx.com with "en" and "fr" as activated locales :
    
        - https://www.xxx.com/en/oauth/fb-login
        - https://www.xxx.com/fr/oauth/fb-login

* Click on "Save changes"
* You will then have to add your "Facebook App id" and "secret" in ``cocorico.facebook.app_id`` and ``cocorico.facebook.secret`` parameters while composer install described in "Install Cocorico dependencies and set your application parameters" chapter.
    