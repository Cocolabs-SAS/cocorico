# Twig


## Prices

All prices are stored in cents without decimal and need to be converted and formatted through 
the twig `format_price` filter inside templates.

    Ex: listing.priceDecimal | format_price(app.request.locale, 2, false)
    

## Currency

* To display default currency symbol (ex: €):

    `{{ currencySymbol(defaultCurrency) }}`
    
* To display current user currency symbol:

    `{{ currencySymbol(currentCurrency) }}`

    
## Images

There are two methods to add images in twig templates according to their type:

- **Cocorico images** through asset function:

    Ex: `<img src="`{{ asset('images/logo.png') }}`" />`
    
- **Users images** through imagine_filter:
    
    Ex: `<img src="`{{ listing.images[0].name | imagine_filter('listing_large')  }}`" />`
    
    Users images are cached in web/media/cache folder.


## Global variables

Global twig variables are defined in `Cocorico\CoreBundle\Twig\CoreExtension.php` and in 
`Cocorico/CoreBundle/Resources/config/config.yml`.

To create a new global twig variable relative to a parameter:

1. Add your parameter in `Cocorico/CoreBundle/Resources/config/parameters.yml`.
2. Inject it into `cocorico.twig.cocorico_extension` service.
3. Add it to the `\Cocorico\CoreBundle\Twig\CoreExtension::getGlobals` method.

Your parameter is now accessible in all twig templates
