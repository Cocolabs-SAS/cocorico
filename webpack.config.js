var Encore = require('@symfony/webpack-encore');

Encore

    // Directory where compiled assets will be stored.
    .setOutputPath('./web/assets/')

    // Public URL path used by the web server to access the output path.
    .setPublicPath('/assets/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('common', [
            './web/js/vendor/moment.js',
            // 'web/js/vendor/json2.js',
            // 'web/js/vendor/jquery.cookie.js',
            // 'web/js/vendor/jquery.touch-punch.js',
            // 'web/js/vendor/hammer.js',
            // 'web/js/vendor/bootstrap-multiselect.js',
            // 'web/js/vendor/bootstrap-datetimepicker.js',
            // 'web/js/vendor/parsley.min.js',
            // 'web/js/vendor/jquery.unslider.js',
            // 'web/js/vendor/jquery.caroufredsel.min.js',
            // 'web/js/vendor/jquery.clearsearch.js',
            // 'web/js/jquery.main.js',
            // 'web/js/jquery.main-override.js',
            // 'web/js/common.js',
            // 'web/js/date-time.js',
            // 'web/js/vendor/cookie-consent.js',
            // 'web/js/vendor/ie.js',
            // 'web/css/all.css',
            // 'web/css/vendor/bootstrap-datetimepicker.css',
            // 'web/css/vendor/bootstrap-multiselect.css',
            // 'web/css/vendor/unslider.css',
            // 'web/css/vendor/cookie-consent.css',
            // 'web/css/all-override.css',
            // 'web/css/itou.css',
    ])
    // .addEntry('app', [
    //     './app/Resources/assets/vendor/bootstrap-typeahead.min',
    //     './app/Resources/assets/vendor/jquery.i18n.min.js',
    //     './app/Resources/assets/js/application.js',
    //     './app/Resources/assets/js/dateLocales.js',
    //     './app/Resources/assets/js/default.js',
    //     './app/Resources/assets/js/eventdata.js',
    //     './app/Resources/assets/js/events.js',
    //     './app/Resources/assets/js/programs.js',
    //     './app/Resources/assets/css/_mixins.scss',
    //     './app/Resources/assets/css/application.scss',
    //     './app/Resources/assets/css/default.scss',
    //     './app/Resources/assets/css/events.scss',
    //     './app/Resources/assets/css/programs.scss',
    // ])

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()

    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // Other options.
    .enableSassLoader()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
