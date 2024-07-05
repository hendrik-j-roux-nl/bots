<?php
// Include settings.php
require_once(dirname(dirname(dirname(__DIR__))) . '/settings.php');
require_once(CREST_PATH . '/crest.php');

class CRestCurrent extends CRest
{
    public static function call($method, $params = [])
    {
        // Retrieve and sanitize OAuth parameters from $_REQUEST
        $authId = isset($_REQUEST['AUTH_ID']) ? htmlspecialchars($_REQUEST['AUTH_ID']) : '';
        $domain = isset($_REQUEST['DOMAIN']) ? htmlspecialchars($_REQUEST['DOMAIN']) : '';
        $refreshId = isset($_REQUEST['REFRESH_ID']) ? htmlspecialchars($_REQUEST['REFRESH_ID']) : '';
        $appSid = isset($_REQUEST['APP_SID']) ? htmlspecialchars($_REQUEST['APP_SID']) : '';

        // If OAuth parameters are missing, read from settings.json
        $settingsFile = CONFIG_PATH.'/config/settings.json';
        if (empty($authId) || empty($domain) || empty($refreshId) || empty($appSid)) {
            if (file_exists($settingsFile)) {
                $settings = json_decode(file_get_contents($settingsFile), true);
                if ($settings) {
                    $authId = $settings['AUTH_ID'] ?? $authId;
                    $domain = $settings['DOMAIN'] ?? $domain;
                    $refreshId = $settings['REFRESH_ID'] ?? $refreshId;
                    $appSid = $settings['APP_SID'] ?? $appSid;
                }
            }
        }

        // Log OAuth parameters for debugging
        $logData = [
            'authId' => $authId,
            'domain' => $domain,
            'refreshId' => $refreshId,
            'appSid' => $appSid,
        ];
        file_put_contents('logs/crest_log.txt', print_r($logData, true), FILE_APPEND);

        // Ensure we have the required keys before proceeding
        if (empty($authId) || empty($domain)) {
            return ['error' => 'Missing required OAuth parameters'];
        }

        // Save OAuth parameters to settings.json
        $settings = [
            'AUTH_ID' => $authId,
            'DOMAIN' => $domain,
            'REFRESH_ID' => $refreshId,
            'APP_SID' => $appSid,
        ];
        file_put_contents($settingsFile, json_encode($settings));

        return parent::call($method, array_merge($params, [
            'auth' => $authId,
            'domain' => $domain,
            'refresh_token' => $refreshId,
            'app_sid' => $appSid
        ]));
    }
}
?>