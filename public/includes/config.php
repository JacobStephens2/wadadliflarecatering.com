<?php
/**
 * Site-wide Configuration
 */

// Site information
define('SITE_NAME', 'Wadadli Flare Catering');
define('SITE_URL', 'https://wadadli.stephens.page');
define('SITE_EMAIL', 'wadadliflare.catering@gmail.com');
define('SITE_PHONE', '(267) 481-5872');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('PRIVATE_PATH', dirname(BASE_PATH) . '/private');
define('PUBLIC_PATH', BASE_PATH);

// URLs
define('BASE_URL', '/');
define('CSS_URL', BASE_URL . 'css/');
define('JS_URL', BASE_URL . 'js/');
define('IMAGES_URL', BASE_URL . 'images/');
define('GALLERY_URL', BASE_URL . 'gallery/');
define('MENU_URL', BASE_URL . 'menu/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Contact information
define('CONTACT_PHONE', '(267) 481-5872');
define('CONTACT_EMAIL', 'wadadliflare.catering@gmail.com');
// define('CONTACT_EMAIL', 'jacob@stephens.page');
define('FACEBOOK_URL', 'https://www.facebook.com/people/wadadli-flare-Catering/100093105235894/');
define('INSTAGRAM_URL', 'https://www.instagram.com/wadadli_flare_catering/');
define('GOOGLE_MAPS_URL', 'https://maps.app.goo.gl/rwZ2hSf6WmHhSUde6');

// Timezone
date_default_timezone_set('America/New_York');

