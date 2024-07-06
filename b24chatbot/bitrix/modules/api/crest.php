<?php
// Include settings.php
require_once(__DIR__ . '/../../../settings.php');

/**
 *  @version 1.36
 *  define:
 *      C_REST_WEB_HOOK_URL = 'https://rest-api.bitrix24.com/rest/1/doutwqkjxgc3mgc1/'  //url on creat Webhook
 *      or
 *      C_REST_CLIENT_ID = 'local.5c8bb1b0891cf2.87252039' //Application ID
 *      C_REST_CLIENT_SECRET = 'SakeVG5mbRdcQet45UUrt6q72AMTo7fkwXSO7Y5LYFYNCRsA6f'//Application key
 *
 *		C_REST_CURRENT_ENCODING = 'windows-1251'//set current encoding site if encoding unequal UTF-8 to use iconv()
 *      C_REST_BLOCK_LOG = true //turn off default logs
 *      C_REST_LOGS_DIR = LOGS_PATH .'/logs/' //directory path to save the log
 *      C_REST_LOG_TYPE_DUMP = true //logs save var_export for viewing convenience
 *      C_REST_IGNORE_SSL = true //turn off validate ssl by curl
 */

class CRest
{
	const VERSION = '1.36';
	const BATCH_COUNT    = 50;//count batch 1 query
	const TYPE_TRANSPORT = 'json';// json or xml

	/**
	 * call where install application even url
	 * only for rest application, not webhook
	 */

	public static function installApp()
	{
		$result = [
			'rest_only' => true,
			'install' => false
		];
		if($_REQUEST[ 'event' ] == 'ONAPPINSTALL' && !empty($_REQUEST[ 'auth' ]))
		{
			$result['install'] = static::setAppSettings($_REQUEST[ 'auth' ], true);
		}
		elseif($_REQUEST['PLACEMENT'] == 'DEFAULT')
		{
			$result['rest_only'] = false;
			$result['install'] = static::setAppSettings(
				[
					'access_token' => htmlspecialchars($_REQUEST['AUTH_ID']),
					'expires_in' => htmlspecialchars($_REQUEST['AUTH_EXPIRES']),
					'application_token' => htmlspecialchars($_REQUEST['APP_SID']),
					'refresh_token' => htmlspecialchars($_REQUEST['REFRESH_ID']),
					'domain' => htmlspecialchars($_REQUEST['DOMAIN']),
					'client_endpoint' => 'https://' . htmlspecialchars($_REQUEST['DOMAIN']) . '/rest/',
				],
				true
			);
		}

		static::setLog(
			[
				'request' => $_REQUEST,
				'result' => $result
			],
			'installApp'
		);
		return $result;
	}

	/**
	 * @var $arParams array
	 * $arParams = [
	 *      'method'    => 'some rest method',
	 *      'params'    => []//array params of method
	 * ];
	 * @return mixed array|string|boolean curl-return or error
	 *
	 */
	protected static function callCurl($arParams)
	{
		if(!function_exists('curl_init'))
		{
			return [
				'error'             => 'error_php_lib_curl',
				'error_information' => 'need install curl lib'
			];
		}
		$arSettings = static::getAppSettings();
		if($arSettings !== false)
		{
			if(isset($arParams[ 'this_auth' ]) && $arParams[ 'this_auth' ] == 'Y')
			{
				$url = 'https://oauth.bitrix.info/oauth/token/';
			}
			else
			{
				$url = $arSettings[ "client_endpoint" ] . $arParams[ 'method' ] . '.' . static::TYPE_TRANSPORT;
				if(empty($arSettings[ 'is_web_hook' ]) || $arSettings[ 'is_web_hook' ] != 'Y')
				{
					$arParams[ 'params' ][ 'auth' ] = $arSettings[ 'access_token' ];
				}
			}

			$sPostFields = http_build_query($arParams[ 'params' ]);

			try
			{
				$obCurl = curl_init();
				curl_setopt($obCurl, CURLOPT_URL, $url);
				curl_setopt($obCurl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($obCurl, CURLOPT_POSTREDIR, 10);
				curl_setopt($obCurl, CURLOPT_USERAGENT, 'Bitrix24 CRest PHP ' . static::VERSION);
				if($sPostFields)
				{
					curl_setopt($obCurl, CURLOPT_POST, true);
					curl_setopt($obCurl, CURLOPT_POSTFIELDS, $sPostFields);
				}
				curl_setopt(
					$obCurl, CURLOPT_FOLLOWLOCATION, (isset($arParams[ 'followlocation' ]))
					? $arParams[ 'followlocation' ] : 1
				);
				if(defined("C_REST_IGNORE_SSL") && C_REST_IGNORE_SSL === true)
				{
					curl_setopt($obCurl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($obCurl, CURLOPT_SSL_VERIFYHOST, false);
				}
				$out = curl_exec($obCurl);
				$info = curl_getinfo($obCurl);
				if(curl_errno($obCurl))
				{
					$info[ 'curl_error' ] = curl_error($obCurl);
				}
				if(static::TYPE_TRANSPORT == 'xml' && (!isset($arParams[ 'this_auth' ]) || $arParams[ 'this_auth' ] != 'Y'))//auth only json support
				{
					$result = $out;
				}
				else
				{
					$result = static::expandData($out);
				}
				curl_close($obCurl);

				if(!empty($result[ 'error' ]))
				{
					if($result[ 'error' ] == 'expired_token' && empty($arParams[ 'this_auth' ]))
					{
						$result = static::GetNewAuth($arParams);
					}
					else
					{
						$arErrorInform = [
							'expired_token'          => 'expired token, cant get new auth? Check access oauth server.',
							'invalid_token'          => 'invalid token, need reinstall application',
							'invalid_grant'          => 'invalid grant, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
							'invalid_client'         => 'invalid client, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
							'QUERY_LIMIT_EXCEEDED'   => 'Too many requests, maximum 2 query by second',
							'ERROR_METHOD_NOT_FOUND' => 'Method not found! You can see the permissions of the application: CRest::call(\'scope\')',
							'NO_AUTH_FOUND'          => 'Some setup error b24, check in table "b_module_to_module" event "OnRestCheckAuth"',
							'INTERNAL_SERVER_ERROR'  => 'Server down, try later'
						];
						if(!empty($arErrorInform[ $result[ 'error' ] ]))
						{
							$result[ 'error_information' ] = $arErrorInform[ $result[ 'error' ] ];
						}
					}
				}
				if(!empty($info[ 'curl_error' ]))
				{
					$result[ 'error' ] = 'curl_error';
					$result[ 'error_information' ] = $info[ 'curl_error' ];
				}

				static::setLog(
					[
						'url'    => $url,
						'info'   => $info,
						'params' => $arParams,
						'result' => $result
					],
					'callCurl'
				);

				return $result;
			}
			catch(Exception $e)
			{
				static::setLog(
					[
						'message' => $e->getMessage(),
						'code' => $e->getCode(),
						'trace' => $e->getTrace(),
						'params' => $arParams
					],
					'exceptionCurl'
				);

				return [
					'error' => 'exception',
					'error_exception_code' => $e->getCode(),
					'error_information' => $e->getMessage(),
				];
			}
		}
		else
		{
			static::setLog(
				[
					'params' => $arParams
				],
				'emptySetting'
			);
		}

		return [
			'error'             => 'no_install_app',
			'error_information' => 'error install app, pls install local application '
		];
	}

	/**
	 * Generate a request for callCurl()
	 *
	 * @var $method string
	 * @var $params array method params
	 * @return mixed array|string|boolean curl-return or error
	 */

	public static function call($method, $params = [])
	{
		$arPost = [
			'method' => $method,
			'params' => $params
		];
		if(defined('C_REST_CURRENT_ENCODING'))
		{
			$arPost[ 'params' ] = static::changeEncoding($arPost[ 'params' ]);
		}

		$result = static::callCurl($arPost);
		return $result;
	}

	/**
	 * @example $arData:
	 * $arData = [
	 *      'find_contact' => [
	 *          'method' => 'crm.duplicate.findbycomm',
	 *          'params' => [ "entity_type" => "CONTACT",  "type" => "EMAIL", "values" => array("info@bitrix24.com") ]
	 *      ],
	 *      'get_contact' => [
	 *          'method' => 'crm.contact.get',
	 *          'params' => [ "id" => '$result[find_contact][CONTACT][0]' ]
	 *      ],
	 *      'get_company' => [
	 *          'method' => 'crm.company.get',
	 *          'params' => [ "id" => '$result[get_contact][COMPANY_ID]', "select" => ["*"],]
	 *      ]
	 * ];
	 *
	 * @var $arData array
	 * @var $halt   integer 0 or 1 stop batch on error
	 * @return array
	 *
	 */

	public static function callBatch($arData, $halt = 0)
	{
		$arResult = [];
		if(is_array($arData))
		{
			if(defined('C_REST_CURRENT_ENCODING'))
			{
				$arData = static::changeEncoding($arData);
			}
			$arDataRest = [];
			$i = 0;
			foreach($arData as $key => $data)
			{
				if(!empty($data[ 'method' ]))
				{
					$i++;
					if(static::BATCH_COUNT >= $i)
					{
						$arDataRest[ 'cmd' ][ $key ] = $data[ 'method' ];
						if(!empty($data[ 'params' ]))
						{
							$arDataRest[ 'cmd' ][ $key ] .= '?' . http_build_query($data[ 'params' ]);
						}
					}
				}
			}
			if(!empty($arDataRest))
			{
				$arDataRest[ 'halt' ] = $halt;
				$arPost = [
					'method' => 'batch',
					'params' => $arDataRest
				];
				$arResult = static::callCurl($arPost);
			}
		}
		return $arResult;
	}

	/**
	 * Getting a new authorization and sending a request for the 2nd time
	 *
	 * @var $arParams array request when authorization error returned
	 * @return array query result from $arParams
	 *
	 */

	private static function GetNewAuth($arParams)
	{
		$result = [];
		$arSettings = static::getAppSettings();
		if($arSettings !== false)
		{
			$arParamsAuth = [
				'this_auth' => 'Y',
				'params'    =>
					[
						'client_id'     => $arSettings[ 'C_REST_CLIENT_ID' ],
						'grant_type'    => 'refresh_token',
						'client_secret' => $arSettings[ 'C_REST_CLIENT_SECRET' ],
						'refresh_token' => $arSettings[ "refresh_token" ],
					]
			];
			$newData = static::callCurl($arParamsAuth);
			if(isset($newData[ 'C_REST_CLIENT_ID' ]))
			{
				unset($newData[ 'C_REST_CLIENT_ID' ]);
			}
			if(isset($newData[ 'C_REST_CLIENT_SECRET' ]))
			{
				unset($newData[ 'C_REST_CLIENT_SECRET' ]);
			}
			if(isset($newData[ 'error' ]))
			{
				unset($newData[ 'error' ]);
			}
			if(static::setAppSettings($newData))
			{
				$arParams[ 'this_auth' ] = 'N';
				$result = static::callCurl($arParams);
			}
		}
		return $result;
	}

	/**
	 * @var $arSettings array settings application
	 * @var $isInstall  boolean true if install app by installApp()
	 * @return boolean
	 */

	private static function setAppSettings($arSettings, $isInstall = false)
	{
		$return = false;
		if(is_array($arSettings))
		{
			$oldData = static::getAppSettings();
			if($isInstall != true && !empty($oldData) && is_array($oldData))
			{
				$arSettings = array_merge($oldData, $arSettings);
			}
			$return = static::setSettingData($arSettings);
		}
		return $return;
	}

	/**
	 * @return mixed setting application for query
	 */

	/**
	 * Retrieves the settings for the application.
	 *
	 * This function checks if the constant C_REST_WEB_HOOK_URL is defined and not empty.
	 * If it is, it creates an array with the client endpoint and sets the is_web_hook flag to 'Y'.
	 * If it is not, it retrieves the settings data using the getSettingData() function.
	 * If the retrieved data contains the necessary fields (access_token, domain, refresh_token,
	 * application_token, and client_endpoint), it sets the $isCurrData flag to true.
	 *
	 * @return array|false The application settings array if they exist, false otherwise.
	 */
	private static function getAppSettings()
	{
		// Check if the constant C_REST_WEB_HOOK_URL is defined and not empty
		if (defined("C_REST_WEB_HOOK_URL") && !empty(C_REST_WEB_HOOK_URL)) {
			// Create an array with the client endpoint and set the is_web_hook flag to 'Y'
			$arData = [
				'client_endpoint' => C_REST_WEB_HOOK_URL,
				'is_web_hook'     => 'Y'
			];
			$isCurrData = true;
		} else {
			// Retrieve the settings data using the getSettingData() function
			$arData = static::();
			$isCurrData = false;
			// Check if the retrieved data contains the necessary fields
			if (
				!empty($arData[ 'access_token' ]) &&
				!empty($arData[ 'domain' ]) &&
				!empty($arData[ 'refresh_token' ]) &&
				!empty($arData[ 'application_token' ]) &&
				!empty($arData[ 'client_endpoint' ])
			) {
				// Set the $isCurrData flag to true
				$isCurrData = true;
			}
		}

		// Return the application settings array if they exist, false otherwise
		return ($isCurrData) ? $arData : false;
	}

    /**
     * Retrieves the encryption key from a file.
     * If the file does not exist, it generates a random key and saves it to the file.
     *
     * @return string The encryption key in hexadecimal format.
     */
    private static function getEncryptionKey()
    {
        // Path to the file containing the encryption key
        $keyFile = CONFIG_PATH . '/encryption_key.php';

        // If the file does not exist, generate a random key and save it to the file
        if (!file_exists($keyFile)) {
            // Generate a random key of 32 bytes
            $key = bin2hex(random_bytes(32));

            // Create a new PHP file and write the key to it
            file_put_contents($keyFile, "<?php return '$key';");
        }

        // Include the file containing the encryption key and return it
        return include $keyFile;
    }
    /**
     * Encrypts the plaintext using the AES-256-CBC algorithm with the provided encryption key.
     * The encryption key is a hexadecimal string converted to binary.
     * The initialisation vector is generated randomly.
     * The ciphertext is base64 encoded and returned.
     *
     * @param string $plaintext The plaintext to be encrypted.
     * @param string $encryptionKey The encryption key in hexadecimal format.
     * @return string The encrypted plaintext in base64 format.
     */
    private static function encryptData($plaintext, $encryptionKey)
    {
        // Generate a random initialisation vector
        $initializationVector = random_bytes(16);

        // Encrypt the plaintext using AES-256-CBC algorithm
        $ciphertext = openssl_encrypt(
            $plaintext,
            'AES-256-CBC',
            hex2bin($encryptionKey),
            0,
            $initializationVector
        );

        // Base64 encode the initialisation vector and ciphertext and return
        return base64_encode($initializationVector . $ciphertext);
    }
    /**
     * Decrypts the ciphertext using the AES-256-CBC algorithm with the provided encryption key.
     * The encryption key is a hexadecimal string converted to binary.
     * The initialisation vector is extracted from the ciphertext.
     * The decrypted plaintext is returned.
     *
     * @param string $encryptedData The ciphertext to be decrypted.
     * @param string $encryptionKey The encryption key in hexadecimal format.
     * @return string The decrypted plaintext.
     */
    private static function decryptData($encryptedData, $encryptionKey)
    {
        // Extract the initialisation vector from the ciphertext
        $initializationVector = substr($encryptedData, 0, 16);
        $encryptedContent = substr($encryptedData, 16);

        // Decrypt the ciphertext using AES-256-CBC algorithm
        return openssl_decrypt(
            $encryptedContent,
            'AES-256-CBC',
            hex2bin($encryptionKey),
            0,
            $initializationVector
        );
    }

	/**
	 * Retrieves the setting data from the encrypted token file.
	 *
	 * @return array The decoded setting data.
	 */
	protected static function getSettingData()
	{
		// Check if the token file exists
		if (file_exists(C_REST_TOKEN_FILE)) {
			// Get the encryption key
			$encryptionKey = self::getEncryptionKey();

			// Read the encrypted data from the file
			$encryptedData = file_get_contents(C_REST_TOKEN_FILE);

			// Decrypt the data using the encryption key
			$decryptedData = self::decryptData($encryptedData, $encryptionKey);

			// Decode the decrypted data as JSON and return it
			return json_decode($decryptedData, true);
		}

		// Return an empty array if the token file does not exist
		return [];
	}
	/**
	 * @var $data mixed
	 * @var $encoding boolean true - encoding to utf8, false - decoding
	 *
	 * @return string json_encode with encoding
	 */
	protected static function changeEncoding($data, $encoding = true)
	{
		if(is_array($data))
		{
			$result = [];
			foreach ($data as $k => $item)
			{
				$k = static::changeEncoding($k, $encoding);
				$result[$k] = static::changeEncoding($item, $encoding);
			}
		}
		else
		{
			if($encoding)
			{
				$result = iconv(C_REST_CURRENT_ENCODING, "UTF-8//TRANSLIT", $data);
			}
			else
			{
				$result = iconv( "UTF-8",C_REST_CURRENT_ENCODING, $data);
			}
		}

		return $result;
	}

	/**
	 * @var $data mixed
	 * @var $debag boolean
	 *
	 * @return string json_encode with encoding
	 */
	protected static function wrapData($data, $debag = false)
	{
		if(defined('C_REST_CURRENT_ENCODING'))
		{
			$data = static::changeEncoding($data, true);
		}
		$return = json_encode($data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);

		if($debag)
		{
			$e = json_last_error();
			if ($e != JSON_ERROR_NONE)
			{
				if ($e == JSON_ERROR_UTF8)
				{
					return 'Failed encoding! Recommended \'UTF - 8\' or set define C_REST_CURRENT_ENCODING = current site encoding for function iconv()';
				}
			}
		}

		return $return;
	}

	/**
	 * @var $data mixed
	 * @var $debag boolean
	 *
	 * @return string json_decode with encoding
	 */
	protected static function expandData($data)
	{
		$return = json_decode($data, true);
		if(defined('C_REST_CURRENT_ENCODING'))
		{
			$return = static::changeEncoding($return, false);
		}
		return $return;
	}

	/**
	 * Can overridden this method to change the data storage location.
	 *
	 * @var $arSettings array settings application
	 * @return boolean is successes save data for setSettingData()
	 */

	protected static function setSettingData($arSettings)
   	 {
		$encryptionKey = self::getEncryptionKey();
		$encryptedData = self::encryptData(json_encode($arSettings), $encryptionKey);
       		return (boolean)file_put_contents(C_REST_TOKEN_FILE, $encryptedData);
   	 }

	/**
	 * Set log for debugging.
	 *
	 * @param array $arData The data to be logged.
	 * @param string $type The type of log.
	 * @return bool|int Returns false on failure or the number of bytes written on success.
	 */
	public static function setLog($arData, $type = '')
	{
		// Check if log is blocked
		if(!defined("C_REST_BLOCK_LOG") || C_REST_BLOCK_LOG !== true)
		{
			// Define log directory
			if(defined("C_REST_LOGS_DIR"))
			{
				$path = C_REST_LOGS_DIR;
			}
			else
			{
				$path = LOG_PATH . '/logs/';
			}

			// Create log directory for current date and hour
			$path .= date("Y-m-d/H") . '/';
			if (!file_exists($path))
			{
				@mkdir($path, 0775, true);
			}

			// Generate log file name
			$path .= time() . '_' . $type . '_' . rand(1, 9999999) . 'log';

			// Check if log type is dump
			if(!defined("C_REST_LOG_TYPE_DUMP") || C_REST_LOG_TYPE_DUMP !== true)
			{
				// Wrap data in JSON format
				$jsonLog = static::wrapData($arData);
				if ($jsonLog === false)
				{
					// Write backup log file
					$return = file_put_contents($path . '_backup.txt', var_export($arData, true));
				}
				else
				{
					// Write JSON log file
					$return = file_put_contents($path . '.json', $jsonLog);
				}
			}
			else
			{
				// Write text log file
				$return = file_put_contents($path . '.txt', var_export($arData, true));
			}

			return $return;
		}

		return false;
	}

	/**
	 * check minimal settings server to work CRest
	 * @var $print boolean
	 * @return array of errors
	 */
	public static function checkServer($print = true)
	{
		$return = [];

		//check curl lib install
		if(!function_exists('curl_init'))
		{
			$return['curl_error'] = 'Need install curl lib.';
		}

		//creat setting file
		file_put_contents(CONFIG_PATH . '/settings_check.json', static::wrapData(['test'=>'data']));
		if(!file_exists(CONFIG_PATH . '/settings_check.json'))
		{
			$return['setting_creat_error'] = 'Check permission! Recommended: folders: 775, files: 664';
		}
		unlink(CONFIG_PATH . '/settings_check.json');
		//creat logs folder and files
		$path = LOG_PATH . '/logs/'.date("Y-m-d/H") . '/';
		if(!mkdir($path, 0775, true) && !file_exists($path))
		{
			$return['logs_folder_creat_error'] = 'Check permission! Recommended: folders: 775, files: 664';
		}
		else
		{
			file_put_contents($path . 'test.txt', var_export(['test'=>'data'], true));
			if(!file_exists($path . 'test.txt'))
			{
				$return['logs_file_creat_error'] = 'check permission! recommended: folders: 775, files: 664';
			}
			unlink($path . 'test.txt');
		}

		if($print === true)
		{
			if(empty($return))
			{
				$return['success'] = 'Success!';
			}
			echo '<pre>';
			print_r($return);
			echo '</pre>';

		}

		return $return;
	}
}