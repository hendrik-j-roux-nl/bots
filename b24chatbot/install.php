<?php
// Include relative files
require_once(__DIR__ . '/settings.php');
require_once(BASE_PATH . '/error_handler.php');
require_once(CREST_PATH . '/crest.php');

function logSensitiveInfo($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    error_log($logMessage);
}

function handleInstallationError($errorMessage, $errorDetails = null) {
    logSensitiveInfo("Installation Error: $errorMessage");
    handleError(500, $errorMessage, $errorDetails);
}

function validateAndSanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

try {
    logSensitiveInfo("Received parameters: " . print_r($_REQUEST, true));

    $requiredParams = ['DOMAIN', 'AUTH_ID', 'REFRESH_ID', 'APP_SID', 'AUTH_EXPIRES', 'member_id'];
    $settings = [];

    foreach ($requiredParams as $param) {
        if (!isset($_REQUEST[$param])) {
            throw new Exception("Missing required parameter: $param");
        }
        $settings[$param] = validateAndSanitizeInput($_REQUEST[$param]);
    }

    $settings['client_endpoint'] = 'https://' . $settings['DOMAIN'] . '/rest/';

    // Instead of calling setAppSettings directly, use the installApp method
    $installResult = CRest::installApp([
        'access_token' => $settings['AUTH_ID'],
        'expires_in' => $settings['AUTH_EXPIRES'],
        'application_token' => $settings['APP_SID'],
        'refresh_token' => $settings['REFRESH_ID'],
        'domain' => $settings['DOMAIN'],
        'client_endpoint' => $settings['client_endpoint'],
        'member_id' => $settings['member_id'],
    ]);

    if (!$installResult['install']) {
        throw new Exception("Installation failed: " . json_encode($installResult));
    }

    logSensitiveInfo("Successfully installed application");

    $installResult = CRest::installApp();
    if (!$installResult['install']) {
        throw new Exception("Installation failed: " . json_encode($installResult));
    }

    $defaultBotSettings = json_decode(file_get_contents(CONFIG_PATH.'/config/defaultbot.json'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse defaultbot.json");
    }

    $botRegisterResult = CRest::call('imbot.register', [
        'CODE' => $defaultBotSettings['CODE'],
        'TYPE' => $defaultBotSettings['TYPE'],
        'EVENT_MESSAGE_ADD' => $defaultBotSettings['EVENT_MESSAGE_ADD'],
        'EVENT_WELCOME_MESSAGE' => $defaultBotSettings['EVENT_WELCOME_MESSAGE'],
        'EVENT_BOT_DELETE' => $defaultBotSettings['EVENT_BOT_DELETE'],
        'PROPERTIES' => $defaultBotSettings['PROPERTIES']
    ]);

    if (isset($botRegisterResult['error'])) {
        throw new Exception("Bot registration failed: {$botRegisterResult['error']}: {$botRegisterResult['error_description']}");
    }

    $openChannelResult = CRest::call('imopenlines.network.join', [
        'CODE' => $defaultBotSettings['OPEN_CHANNEL_CODE']
    ]);

    if (isset($openChannelResult['error'])) {
        throw new Exception("Open Channel joining failed: {$openChannelResult['error']}: {$openChannelResult['error_description']}");
    }

    $botId = $botRegisterResult['result'];
    logSensitiveInfo("Installation successful. Bot ID: $botId");

    // Prepare data for the view
    $viewData = [
        'botId' => $botId,
        'installSuccess' => true
    ];

} catch (Exception $e) {
    logSensitiveInfo("Installation Error: " . $e->getMessage());
    $viewData = [
        'installSuccess' => false,
        'errorMessage' => $e->getMessage()
    ];
}

// Render the view
require_once(BASE_PATH . '/webui/installation_result.php');