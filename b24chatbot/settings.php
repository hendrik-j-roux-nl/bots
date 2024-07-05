<?php

/* ------[Define Relative Paths]------------------------------------------------------ */
// Define base paths
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__FILE__));
}

if (!defined('CREST_PATH')) {
    define('CREST_PATH', BASE_PATH . '/bitrix/modules/api');
}

if (!defined('BOTS_PATH')) {
    define('BOTS_PATH', BASE_PATH . '/bitrix/components/bot');
}

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', BASE_PATH . '/bitrix/components/bot/config');
}

if (!defined('LOG_PATH')) {
    define('LOG_PATH', BASE_PATH . '/logs');
}

if (!defined('STYLES_PATH')) {
    define('STYLES_PATH', BASE_PATH . '/styles');
}

/* ----------[Application Identity]--------------------------------------------------------- */
// Define the application ID
define('C_REST_CLIENT_ID', 'YOUR_CLIENT_ID_HERE');

// Define the application key
define('C_REST_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');



// Uncomment the line below and define the URL of the web hook if using web hooks
// define('C_REST_WEB_HOOK_URL', 'https://rest-api.bitrix24.com/rest/1/doutwqkjxgc3mgc1/');

// Define the current encoding of the application
define('C_REST_CURRENT_ENCODING', 'UTF-8');

// Set to false to validate SSL by curl
define('C_REST_IGNORE_SSL', false);

// Define whether logs should be saved as var_export for viewing convenience
define('C_REST_LOG_TYPE_DUMP', true);

// Define the directory path to save logs
define('C_REST_LOGS_DIR', __DIR__ . '/logs/');

// Define the file to store the encrypted token
define('C_REST_TOKEN_FILE', __DIR__ . '/configsecure_token.php');

// Logs where set to false
define('C_REST_BLOCK_LOG',false);

// Rasa NLU settings
define('RASA_ENDPOINT', 'http://localhost:5005/webhooks/rest/webhook');

// Bot configuration file
define('BOTS_CONFIG_FILE', __DIR__ . '/config/botconfig.json');