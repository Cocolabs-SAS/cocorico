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
        './web/js/vendors.js',
        './web/js/vendor/json2.js',
        './web/js/vendor/hammer.js',
        './web/js/vendor/bootstrap-multiselect.js',
        './web/js/vendor/bootstrap-datetimepicker.js',
        './web/js/vendor/parsley.min.js',
        './web/js/vendor/jquery.unslider.js',
        './web/js/vendor/jquery.caroufredsel.min.js',
        './web/js/vendor/jquery.clearsearch.js',

        './web/js/jquery.main.js',
        './web/js/jquery.main-override.js',
        './web/js/common.js',
        './web/js/date-time.js',
        './web/js/vendor/cookie-consent.js',

        //'./node_modules/tarteaucitronjs/tarteaucitron.js',

        './web/css/vendor/bootstrap-datetimepicker.css',
        './web/css/vendor/bootstrap-multiselect.css',
        './web/css/vendor/unslider.css',
        './web/css/vendor/cookie-consent.css',
        //'./web/css/all.css',
        //'./web/css/all-override.css',
        //'./web/css/itou.css',
        './web/css/final_import.scss',
    ])

    .addEntry('bs4_common', [
        './web/js/vendors.js',
        './web/js/vendor/json2.js',
        './web/js/vendor/hammer.js',
        './web/js/vendor/parsley.min.js',
        './web/js/vendor/jquery.clearsearch.js',

        './web/js/jquery.main.js',
        './web/js/jquery.main-override.js',
        './web/js/common.js',
        './web/js/date-time.js',
        './web/js/vendor/cookie-consent.js',

        //'./node_modules/tarteaucitronjs/tarteaucitron.js',

        './web/css/vendor/unslider.css',
        './web/css/vendor/cookie-consent.css',
        //'./web/css/all.css',
        //'./web/css/all-override.css',
        //'./web/css/itou.css',
        './web/css/bs4_import.scss',
    ])

    .addEntry('itou_common', [
        './web/js/vendors.js',
        './web/js/vendor/json2.js',
        './web/js/vendor/hammer.js',
        './web/js/vendor/parsley.min.js',
        './web/js/vendor/jquery.clearsearch.js',

        './web/js/jquery.main.js',
        './web/js/jquery.main-override.js',
        './web/js/common.js',
        './web/js/date-time.js',
        './web/js/vendor/cookie-consent.js',

        //'./node_modules/tarteaucitronjs/tarteaucitron.js',

        './web/css/vendor/unslider.css',
        './web/css/vendor/cookie-consent.css',

        '/web/itou/javascripts/app.js',
        '/web/itou/stylesheets/app.css',

        './web/css/itou_marche.scss',
    ])

    .addEntry('upload', [
	   './web/js/upload.main.js'
    ])

    .addEntry('calendar', [
        './web/js/vendor/fullcalendar/fullcalendar.min.js',
        './web/js/vendor/fullcalendar/lang-all.js'
    ])

    .copyFiles([
        {from: './node_modules/tarteaucitronjs/', to: 'tarteaucitron/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: true},
        {from: './node_modules/ckeditor/', to: 'ckeditor/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: false},
        {from: './node_modules/ckeditor/adapters', to: 'ckeditor/adapters/[path][name].[ext]'},
        {from: './node_modules/ckeditor/lang', to: 'ckeditor/lang/[path][name].[ext]'},
        {from: './node_modules/ckeditor/plugins', to: 'ckeditor/plugins/[path][name].[ext]'},
        {from: './node_modules/ckeditor/skins', to: 'ckeditor/skins/[path][name].[ext]'}
    ])


    // Preload legacy functionnality
    .autoProvideVariables({
	$: 'jquery',
	jQuery: 'jquery',
	'window.jQuery': 'jquery',
	'root.jQuery': 'jquery',
    	// 'tarteaucitron' : 'tarteaucitron',
	// $: 'webpack-jquery-ui',
	// jQuery: 'webpack-jquery-ui',
	// 'window.jQuery': 'webpack-jquery-ui',
	// 'Cookies': 'js-cookie',
	// jcf: 'jcf',
    })

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    //.enableSingleRuntimeChunk()

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
    //.enableEslintLoader()
    .enableSassLoader()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

config = Encore.getWebpackConfig();

config.output.library = 'test';
config.output.libraryTarget = 'window';
config.output.libraryExport = 'default';
// console.log(config);

module.exports = {
    stats: 'errors-only',
    ...config
}
