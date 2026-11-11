<?php
/**
 * Chum Chum's — site configuration
 * --------------------------------------------------------------

 * EXAMPLE FILE — copy this to config/config.php on the server and fill in the real values.
 * config/config.php is NOT stored in git (it holds the database password).
 *
 * Update the database credentials below with the values from
 * cPanel → MySQL® Databases. On cPanel the database name and user
 * are usually prefixed with your cPanel username, e.g. "cpuser_chumchums".
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'cpuser_database');
define('DB_USER', 'cpuser_user');
define('DB_PASS', 'your-database-password');
define('DB_CHARSET', 'utf8mb4');

/* Leave empty to auto-detect. Set to e.g. 'https://chumchums.com.au' if needed. */
define('SITE_URL', 'https://www.chumchums.com.au');

/* Set to true only while developing — shows PHP errors on screen. */
define('APP_DEBUG', false);

date_default_timezone_set('Australia/Melbourne');
