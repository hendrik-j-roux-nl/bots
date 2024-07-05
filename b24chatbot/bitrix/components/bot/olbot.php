<?php
// Include settings.php
require_once(dirname(__FILE__) . '/settings.php');
require_once(CREST_PATH . '/crest.php');

class OLBot
{
    private static $botsConfig = [];

    public static function init()
    {
        self::loadBotsConfig();
    }

    private static function loadBotsConfig()
    {
        if (file_exists(BOTS_CONFIG_FILE)) {
            self::$botsConfig = json_decode(file_get_contents(BOTS_CONFIG_FILE), true);
        }
    }

    private static function saveBotsConfig()
    {
        file_put_contents(BOTS_CONFIG_FILE, json_encode(self::$botsConfig, JSON_PRETTY_PRINT));
    }

    public static function handleEvent($event, $data)
    {
        switch ($event) {
            case 'ONIMBOTMESSAGEADD':
                return self::onMessageAdd($data);
            case 'ONIMBOTJOINCHAT':
                return self::onJoinChat($data);
            case 'ONIMBOTDELETE':
                return self::onBotDelete($data);
            default:
                return false;
        }
    }

    public static function registerBot($properties)
    {
        $handlerBackUrl = self::getHandlerBackUrl();

        $result = CRest::call('imbot.register', [
            'CODE' => $properties['CODE'],
            'TYPE' => 'O',
            'EVENT_MESSAGE_ADD' => $handlerBackUrl,
            'EVENT_WELCOME_MESSAGE' => $handlerBackUrl,
            'EVENT_BOT_DELETE' => $handlerBackUrl,
            'OPENLINE' => 'Y',
            'PROPERTIES' => $properties
        ]);

        if (isset($result['result'])) {
            $botId = $result['result'];
            self::$botsConfig[$properties['CODE']] = [
                'BOT_ID' => $botId,
                'PROPERTIES' => $properties
            ];
            self::saveBotsConfig();
            return $botId;
        }

        return false;
    }

    public static function getBotsList()
    {
        return self::$botsConfig;
    }

    public static function updateBot($code, $properties)
    {
        if (isset(self::$botsConfig[$code])) {
            $result = CRest::call('imbot.update', [
                'BOT_ID' => self::$botsConfig[$code]['BOT_ID'],
                'FIELDS' => $properties
            ]);

            if (isset($result['result'])) {
                self::$botsConfig[$code]['PROPERTIES'] = $properties;
                self::saveBotsConfig();
                return true;
            }
        }
        return false;
    }

    private static function onMessageAdd($data)
    {
        $botCode = $data['PARAMS']['TO_USER_ID'];
        if (isset(self::$botsConfig[$botCode])) {
            $bot = self::$botsConfig[$botCode];
            
            // Send user message to Rasa NLU
            $rasaResponse = self::sendToRasa($data['PARAMS']['MESSAGE'], $data['PARAMS']['DIALOG_ID']);
            
            $message = $rasaResponse['message'] ?? "I'm sorry, I couldn't process that request.";
            
            CRest::call('imbot.message.add', [
                "BOT_ID" => $bot['BOT_ID'],
                "DIALOG_ID" => $data['PARAMS']['DIALOG_ID'],
                "MESSAGE" => $message,
            ]);
        }
        return true;
    }

    private static function onJoinChat($data)
    {
        $botCode = $data['PARAMS']['BOT_ID'];
        if (isset(self::$botsConfig[$botCode])) {
            $bot = self::$botsConfig[$botCode];
            
            // Initialize conversation with Rasa NLU
            $rasaResponse = self::sendToRasa('/start', $data['PARAMS']['DIALOG_ID']);
            
            $message = $rasaResponse['message'] ?? "Hello! I'm " . $bot['PROPERTIES']['NAME'] . ". How can I assist you?";
            
            CRest::call('imbot.message.add', [
                "BOT_ID" => $bot['BOT_ID'],
                "DIALOG_ID" => $data['PARAMS']['DIALOG_ID'],
                "MESSAGE" => $message,
            ]);
        }
        return true;
    }

    private static function onBotDelete($data)
    {
        $botCode = array_search($data['BOT_ID'], array_column(self::$botsConfig, 'BOT_ID'));
        if ($botCode !== false) {
            unset(self::$botsConfig[$botCode]);
            self::saveBotsConfig();
        }
        return true;
    }

    private static function sendToRasa($message, $senderId)
    {
        $data = [
            'sender' => $senderId,
            'message' => $message
        ];

        $ch = curl_init(RASA_ENDPOINT);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        // Rasa typically returns an array of messages, so we'll join them
        if (is_array($decodedResponse) && !empty($decodedResponse)) {
            $message = implode("\n", array_column($decodedResponse, 'text'));
            return ['message' => $message];
        }

        return ['message' => 'No response from Rasa'];
    }

    private static function getHandlerBackUrl()
    {
        return ($_SERVER['SERVER_PORT']==443 || $_SERVER["HTTPS"]=="on" ? 'https': 'http')."://".$_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'], [80, 443]) ? '' : ':'.$_SERVER['SERVER_PORT']).'/handler.php';
    }
}