<?php
// Include settings.
// Using dirname(__DIR__) to include settings.php ensures no constant dependency, defined within settings.php.
require_once(__DIR__ . '/../settings.php');

// Rest of the code remains the same
require_once(CREST_PATH . '/crest.php');

// Function to log sensitive information
function logSensitiveInfo($message) {
    $logFile = LOG_PATH . '/sensitive_info.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Get current user info using the Bitrix24 REST API
$result = CRest::call('user.current');
$user = $result['result'] ?? null;

if ($user) {
    // Display the user's full name
    echo "Current user: {$user['NAME']} {$user['LAST_NAME']}";
} else {
    // Display an error message if the user info could not be retrieved
    echo "Error retrieving user information";
}
