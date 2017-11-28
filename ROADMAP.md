# Roadmap

## [Unreleased]

### Fix remaining SF 2.x deprecations

* Accessing form type by its fully-qualified type class name instead string name

* Passing type instances to FormBuilder::add() by its fully-qualified type class name instead string name

* Use "constraints" with a Valid constraint instead of "cascade_validation" in form types.

* Use form option "entry_type" instead of "type"

* Replace "empty_value" option in types "choice", "date", ...by "placeholder". 
    To do when this issue https://github.com/schmittjoh/JMSTranslationBundle/issues/228 will be resolved 


### Use "php-http/httplug" instead of abandonned "egeloen/http-adapter" package in CocoricoGeoBundle

* Wait merge of this PR https://github.com/geocoder-php/Geocoder/pull/487
    
   
### Upgrade Symfony 3.4

### Fix BDD tests

### Fix db fields case  

Fix case of createdAt, and updatedAt field names to created_at and updated_at according to doctrine.orm.naming_strategy

### Architecture evolution
    
* Decoupling
* Externalization
* Overridability
* Use SF Workflow component (SF >= 3.2) or fduch/workflow-bundle (SF >=2.3)
* APIfication
* Micro services
* Travis
    
### Upgrade to Symfony 4

### Add translator Providers (Google)
    
### Fix bug:

* Double click on save availabilities pop-in

* Wait merge of WebProfilerBundle https://github.com/symfony/symfony/pull/18413
    
    
    
## [0.4.0] - 2016-12-02

### Update PHP to 5.6

* Last LTS version : See http://php.net/supported-versions.php

* Will be useful for Symfony 3 upgrade and depreciation resolution
        
        
## [0.2.0] - 2016-04-06

### Update Symfony from 2.5 to 2.8 (see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md)

* Update Symfony package to its last LTS version (2.8).### Update PHP to 5.6                                                     
    * Last LTS version : See http://php.net/supported-versions.php
    * Will be useful for Symfony 3 upgrade and depreciation resolution
    
* Resolve depreciated warnings
    
### Update dependencies
