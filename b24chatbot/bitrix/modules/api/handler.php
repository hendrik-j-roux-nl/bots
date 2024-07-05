<?php
// Include settings.php
require_once(__DIR__ . '/../../../settings.php');
//require_once(dirname(dirname(__DIR__)) . '/components/bot/olbot.php');
require_once(CREST_PATH . '/crest.php');

// Function to log sensitive information
function logSensitiveInfo($message) {
    $logFile = LOG_PATH . '/sensitive_info.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/*OLBot::init();

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    return;
}

// Get the raw input data from the request body
$inputData = file_get_contents('php://input');

// Decode the input data into an associative array
$event = json_decode($inputData, true);

// If the event is not present in the input data, return an error
if (empty($event['event'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    return;
}

// Initialize an empty result array
$result = [];

// Handle different events based on the event type received
switch ($event['event']) {
    // Handle the application installation event
    case 'ONAPPINSTALL':
        // Call the CRest::installApp() method to install the application
        $result = CRest::installApp();
        // Set the message based on the installation result
        $result['message'] = $result['install'] ? 'Application installed successfully' : 'Installation failed';
        break;

    // Handle the message add, join chat, and delete events
    case 'ONIMBOTMESSAGEADD':
    case 'ONIMBOTJOINCHAT':
    case 'ONIMBOTDELETE':
        // Call the OLBot::handleEvent() method to handle the event
        $result = OLBot::handleEvent($event['event'], $event['data']);
        break;

    // Handle unknown events
    default:
        // Set the success flag to false and an error message
        $result = ['success' => false, 'error' => 'Unknown event'];
        break;
}

// Encode the result array as JSON and output it
echo json_encode($result);
*/

echo 'Blah-blah from the handler.';