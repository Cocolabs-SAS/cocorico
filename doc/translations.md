# Translations

There are two types of translations:

- **Cocorico texts**:
    - stored in `app/Resources/translations/`
    - translated through [JMSTranslationBundle](https://github.com/schmittjoh/JMSTranslationBundle).

- **Users content**: 
    - stored in Database
    - manually or automatically translated through MS Translator API.

- In case of error or to ignore some translations add `/** @Ignore */` above the text to not translate.

- To make some entity contents translatable, prefix them with the text `entity`.

        Ex: $status = array(1 => 'entity.custom.name', ...)
    
- To customize how entity contents are translatable see `Cocorico\CoreBundle\Translator\EntityExtractor`

- To make some admin contents translatable prefix them with `admin`.

        Ex: 'label' => 'admin.listing.title'
    

## Extract translations

To extract translations keys from whole application:
    In english languages:
        `php app/console translation:extract en --config=cocorico`
        `php app/console cache:clear --env=dev`

To extract translations keys from external bundle:
    In english languages :
        `php app/console translation:extract en --bundle=CocoricoListingAlertBundle`
               
To translate keys you have choice to:

* Edit `app/Resources/translations/` files
* Go to http://cocorico.dev/[admin]/__translations/

## Do not generate entities

Do not generate entities from `generate:entities` because of this doctrine remark and the use of mapped super class

> This command is not suited for constant usage. It is a little helper and does not support all the mapping edge cases 
> very well. You still have to put work in your entities after using this command.

