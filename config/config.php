<?php
/**
 * Chum Chum's — site configuration
 * --------------------------------------------------------------
 * Update the database credentials below with the values from
 * cPanel → MySQL® Databases. On cPanel the database name and user
 * are usually prefixed with your cPanel username, e.g. "cpuser_chumchums".
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'tcplckfa_chumchum26');
define('DB_USER', 'tcplckfa_chumchum26admin');
define('DB_PASS', 'f}+WJg3Zh~8+BaBW');
define('DB_CHARSET', 'utf8mb4');

/* Leave empty to auto-detect. Set to e.g. 'https://chumchums.com.au' if needed. */
define('SITE_URL', 'https://www.chumchums.com.au');

/* Set to true only while developing — shows PHP errors on screen. */
define('APP_DEBUG', false);

date_default_timezone_set('Australia/Melbourne');
