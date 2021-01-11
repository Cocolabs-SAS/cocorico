Documentation: https://symfony.com/doc/3.4/frontend.html

Assets are managed with [Webpack Encore](https://github.com/symfony/webpack-encore).
Local CSS and JavaScript live in [app/Resources/assets](https://github.com/wikimedia/grantmetrics/tree/master/app/Resources/assets).
Fonts and vendor assets must be defined in [webpack.config.js](https://github.com/wikimedia/grantmetrics/blob/master/webpack.config.js),
and if needed, sourced in the `<head>` of [base.html.twig](https://github.com/wikimedia/grantmetrics/blob/master/app/Resources/views/base.html.twig).

On compilation, all assets are copied to the `web/assets/` directory (publicly accessible).
This happens by running `./node_modules/.bin/encore production` (or `dev` if you don't want the files to be minified and versioned).
You can also continually watch for file changes with `./node_modules/.bin/encore production --watch`.
