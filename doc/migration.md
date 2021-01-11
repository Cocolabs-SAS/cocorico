# Notes on symfony migration - le marché itou
## Documentation:
- Webpack update example : https://github.com/wikimedia/eventmetrics/pull/101/files

## 11/01/21
- removal of mongodb dependency, removal of listing availabilities functionnality
- Fixed twig extension / abstract extension alert
- Remove twig spaceless alert
- Switched route bundle (from sensio to symfony)

# Package replacements / removal
- Removed mongo packages
- Removing ramsey/array_column | ramsey/array_column is abandoned, you should avoid using it. Use it-for-free/array_column
- Removed sensio/generator-bundle, added ymfony/maker-bundle

Other alerts:
```
Package helios-ag/fm-elfinder-php-connector is abandoned, you should avoid using it. No replacement was suggested.
Package patchwork/jsqueeze is abandoned, you should avoid using it. No replacement was suggested.
Package robloach/component-installer is abandoned, you should avoid using it. Use oomphinc/composer-installers-extender instead.
Package sonata-project/core-bundle is abandoned, you should avoid using it. No replacement was suggested.
Package symfony/assetic-bundle is abandoned, you should avoid using it. Use symfony/webpack-encore-pack instead.
Package twig/extensions is abandoned, you should avoid using it. No replacement was suggested.
Package whiteoctober/breadcrumbs-bundle is abandoned, you should avoid using it. Use mhujer/breadcrumbs-bundle instead.
Package zendframework/zend-code is abandoned, you should avoid using it. Use laminas/laminas-code instead.
Package zendframework/zend-eventmanager is abandoned, you should avoid using it. Use laminas/laminas-eventmanager instead.
Package phpunit/php-token-stream is abandoned, you should avoid using it. No replacement was suggested.
Package phpunit/phpunit-mock-objects is abandoned, you should avoid using it. No replacement was suggested.
Package sensio/distribution-bundle is abandoned, you should avoid using it. No replacement was suggested.
Package sensio/generator-bundle is abandoned, you should avoid using it. Use symfony/maker-bundle instead.
```

# To Do on Migration:
- replace whiteoctober/breadcrumbs-bundle with  mhujer/breadcrumbs-bundle
