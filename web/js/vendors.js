// const jQuery = require('jquery');
require('jquery-ui');
// require('jquery-ui-touch-punch');

// Do not include full JCF lib, some parts are not relevant
const jcf = require('jcf/js/jcf');
require('jcf/js/jcf.select');
require('jcf/js/jcf.radio');
require('jcf/js/jcf.checkbox');
require('jcf/js/jcf.scrollable');
require('jcf/js/jcf.file');
require('jcf/js/jcf.range');
require('jcf/js/jcf.number');
require('jcf/js/jcf.textarea');

require('bootstrap');
require('bootstrap/js/dist/modal');
require('bootstrap/js/dist/tooltip');

const Cookies = require('js-cookie');

window.$ = window.jQuery = jQuery;
window.Cookies = Cookies;
window.jcf = jcf;
