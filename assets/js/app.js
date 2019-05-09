/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.scss in this case)
require('../css/bootstrap.min.css');
require('../css/app.scss');
require('../css/home.scss');
require('../css/registration.scss');
require('../css/login.scss');
require('../css/one-photo.scss');
require('../css/all-photos.scss');
require('../css/add-photos.scss');
require('../css/one-album.scss');
require('../css/all-albums.scss');
require('../css/add-album.scss');
require('../css/one-album-all-photos.scss');
require('../css/admin.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// require jQuery normally
const $ = require('jquery');
// create global $ and jQuery variables
global.$ = global.jQuery = $;



require('bootstrap');
require('popper.js');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

