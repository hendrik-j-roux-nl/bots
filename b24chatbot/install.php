<?php
// Include relative files
require_once(__DIR__ . '/settings.php');
require_once(BASE_PATH . '/error_handler.php');
require_once(CREST_PATH . '/crest.php');


// Basic logging
function setLog($arData, $type = '')
{
    $return = false;
    if(!defined("C_REST_BLOCK_LOG") || C_REST_BLOCK_LOG !== true)
    {
        if(defined("C_REST_LOGS_DIR"))
        {
            $path = C_REST_LOGS_DIR;
        }
        else
        {
            $path = __DIR__ . '/logs/';
        }
        $path .= date("Y-m-d/H") . '/';

        if (!file_exists($path))
        {
            @mkdir($path, 0775, true);
        }

        $path .= time() . '_' . $type . '_' . rand(1, 9999999) . 'log';
        if(!defined("C_REST_LOG_TYPE_DUMP") || C_REST_LOG_TYPE_DUMP !== true)
        {
            $jsonLog = static::wrapData($arData);
            if ($jsonLog === false)
            {
                $return = file_put_contents($path . '_backup.txt', var_export($arData, true));
            }
            else
            {
                $return = file_put_contents($path . '.json', $jsonLog);
            }
        }
        else
        {
            $return = file_put_contents($path . '.txt', var_export($arData, true));
        }
    }
    return $return;
}

/*
function logSensitiveInfo($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    error_log($logMessage);
}
*/
function validateAndSanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
setLog("Received parameters: " . print_r($_REQUEST, true));
/*
try {
    logSensitiveInfo("Received parameters: " . print_r($_REQUEST, true));

    // Debug: Log the CRest class methods
    logSensitiveInfo("CRest methods: " . print_r(get_class_methods('CRest'), true));

    // Use the installApp method from CRest class
   // $installResult = CRest::installApp();

    logSensitiveInfo("Install result: " . print_r($installResult, true));

    if ($installResult['install']) {
        logSensitiveInfo("Application installed successfully");

        // Proceed with bot registration
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
*/
// Render the view
require_once(BASE_PATH . '/webui/installation_result.php');