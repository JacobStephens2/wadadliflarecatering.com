<?php
/**
 * Site-wide Configuration
 */

// Load environment variables early so they're available for all constants
require_once __DIR__ . '/../../private/includes/env.php';

// Site information
define('SITE_NAME', 'Wadadli Flare Catering');
define('SITE_URL', 'https://wadadliflarecatering.com');
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
define('FACEBOOK_URL', 'https://www.facebook.com/p/wadadli-flare-Catering-100093105235894/');
define('INSTAGRAM_URL', 'https://www.instagram.com/wadadli_flare_catering/');
define('GOOGLE_MAPS_URL', 'https://maps.app.goo.gl/pQKd8TsuFbZTvP1M8');

// Review links. GOOGLE_REVIEW_URL opens Google's write-a-review dialog directly;
// the place ID is derived from the Maps listing's ftid
// 0x89c667df78c8f629:0xb9699bae9bdb96e8. Facebook has no equivalent deep link, so
// we send customers to the page and tell them to use the Reviews tab.
define('GOOGLE_REVIEW_URL', 'https://search.google.com/local/writereview?placeid=ChIJKfbIeN9nxokR6Jbbm66babk');
define('FACEBOOK_REVIEW_URL', FACEBOOK_URL);

// Cloudflare Turnstile (anti-spam CAPTCHA)
define('TURNSTILE_SITE_KEY', $_ENV['TURNSTILE_SITE_KEY'] ?? getenv('TURNSTILE_SITE_KEY') ?: '');
define('TURNSTILE_SECRET', $_ENV['TURNSTILE_SECRET'] ?? getenv('TURNSTILE_SECRET') ?: '');

// Timezone
date_default_timezone_set('America/New_York');

