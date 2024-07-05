<?php

/*--------------------------------------------------------------------------------------------
Typically, this file would contain broader application configurations, including 
database connections, environment settings, and integration configurations specific 
to the Bitrix framework.
----------------------------------------------------------------------------------------------*/
// Include the custom settings file
require_once(__DIR__ . '/settings.php');

/*
// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'bitrix_db');
define('DB_USER', 'bitrix_user');
define('DB_PASSWORD', 'secure_password');
*/
// Default language and timezone
define('DEFAULT_LANGUAGE', 'en');
define('DEFAULT_TIMEZONE', 'UTC');

// Application settings
define('APP_ENV', 'development'); // or 'production'
define('DEBUG_MODE', true);

// Bitrix framework settings
define('BITRIX_ROOT', __DIR__ . '/bitrix');
define('BITRIX_TEMPLATE', 'default');

?>