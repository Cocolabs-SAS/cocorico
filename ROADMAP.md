# Roadmap

## [Unreleased]

### Change createdAt, and updatedAt field names to created_at and updated_at according to doctrine.orm.naming_strategy
    
### Update PHP to 5.6

    * Last LTS version : See http://php.net/supported-versions.php
    * Will be useful for Symfony 3 upgrade and depreciation resolution

### Fix remaining SF 2.x deprecations

    * Accessing form type by its fully-qualified type class name instead string name
    * Passing type instances to FormBuilder::add() by its fully-qualified type class name instead string name
    * Use "constraints" with a Valid constraint instead of "cascade_validation" in form types.
    * Use form option "entry_type" instead of "type"
    * Replace "empty_value" option in types "choice", "date", ...by "placeholder". 
        Todo when this issue https://github.com/schmittjoh/JMSTranslationBundle/issues/228 will be resolved 

### Fix BDD tests

### Use "php-http/httplug" instead abandonned "egeloen/http-adapter" package in CocoricoGeoBundle
    * Wait merge of this PR https://github.com/geocoder-php/Geocoder/pull/487

### Architecture evolution
    
    * Decoupling
    * Modularity
    * ...
    
### Fix bug:

    * Double click on save availabilities pop-in
    * Wait merge of WebProfilerBundle https://github.com/symfony/symfony/pull/18413
    
    
## [0.2.0] - 2016-04-06

### Update Symfony from 2.5 to 2.8 (see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md)

    * Update Symfony package to its last LTS version (2.8).
    * Resolve depreciated warnings
    
### Update dependencies

