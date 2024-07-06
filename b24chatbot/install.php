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

function validateAndSanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

try {
    logSensitiveInfo("Received parameters: " . print_r($_REQUEST, true));

    // Debug: Log the CRest class methods
    logSensitiveInfo("CRest methods: " . print_r(get_class_methods('CRest'), true));

    // Use the installApp method from CRest class
    $installResult = CRest::installApp();

    logSensitiveInfo("Install result: " . print_r($installResult, true));

    /* --------------- [Default Bot Installation]---------------------------------------------------------------------------
    Check the application installation to be successful.  Install the default bot so that the Bitrix24 application
    can display the admin screen to the installer.

    On successfull installation the C_REST_CLIENT_ID and C_REST_CLIENT_SECRET are saved to the settings.php file.
    ---------------------------------------------------------------------------------------------------------------------- */
    if ($installResult['install']) {
        logSensitiveInfo("Application installed successfully");

        // Proceed with default bot registration
        $defaultBotSettings = json_decode(file_get_contents(BOTCON_PATH.'/defaultbot.json'), true);
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

        logSensitiveInfo("Bot register result: " . print_r($botRegisterResult, true));

        if (isset($botRegisterResult['error'])) {
            throw new Exception("Bot registration failed: {$botRegisterResult['error']}: {$botRegisterResult['error_description']}");
        }

        $openChannelResult = CRest::call('imopenlines.network.join', [
            'CODE' => $defaultBotSettings['OPEN_CHANNEL_CODE']
        ]);

        logSensitiveInfo("Open channel result: " . print_r($openChannelResult, true));

        if (isset($openChannelResult['error'])) {
            throw new Exception("Open Channel joining failed: {$openChannelResult['error']}: {$openChannelResult['error_description']}");
        }

        $botId = $botRegisterResult['result'];
        logSensitiveInfo("Installation successful. Bot ID: $botId");

        $viewData = [
            'installSuccess' => true,
            'botId' => $botId
        ];
    } else {
        throw new Exception("Installation failed: " . json_encode($installResult));
    }
} catch (Exception $e) {
    logSensitiveInfo("Installation Error: " . $e->getMessage());
    $viewData = [
        'installSuccess' => false,
        'errorMessage' => $e->getMessage()
    ];
}

// Render the view
require_once(BASE_PATH . '/webui/installation_result.php');
