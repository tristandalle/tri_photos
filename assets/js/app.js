/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.scss in this case)
require('../css/app.scss');
require('../css/all-photos.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// require jQuery normally
const $ = require('jquery');
// create global $ and jQuery variables
global.$ = global.jQuery = $;


require('popper.js');
require('bootstrap');


console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
