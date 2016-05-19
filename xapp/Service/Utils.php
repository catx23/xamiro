<?php

/**
 * Class XApp_Service_Utils provides a common utils to setup a Rpc service but also tools for bootstrapping xapp
 *
 */
class XApp_Service_Utils
{

	/***
	 *  XApp-Service-Types
	 */
	const SMD_GET = "XAPP_SERVICE_TYPE_SMD_GET";
	const SMD_CALL = "XAPP_SERVICE_TYPE_SMD_CALL";
	const UPLOAD = "XAPP_SERVICE_TYPE_UPLOAD";
	const DOWNLOAD = "XAPP_SERVICE_TYPE_DOWNLOAD";
	const UNKNOWN = "XAPP_SERVICE_TYPE_UNKNOWN";
	const LOGIN = "XAPP_SERVICE_TYPE_LOGIN";

	/**
	 * Returns the script file
	 * @return mixed
	 */
	public static function getIndex()
	{
		$scriptParts = pathinfo($_SERVER['SCRIPT_FILENAME']);

		if (strpos($_SERVER['REQUEST_URI'], $scriptParts['basename']) == false) {
			$newRequestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$newRequestUri .= $scriptParts['basename'];
			$newRequestUri .= '?' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
			$_SERVER['REQUEST_URI'] = $newRequestUri;
		}
		return $scriptParts['basename'];
	}

	/***
	 * load XApp-PHP-Core files
	 */
	public static function loadCore()
	{

		if (!defined('XAPP')) {
			include_once(XAPP_BASEDIR . '/Core/core.php');
			require_once(XAPP_BASEDIR . '/Xapp/Autoloader.php');
			require_once(XAPP_BASEDIR . '/Xapp/Console.php');
			require_once(XAPP_BASEDIR . '/Xapp/Debug.php');
			require_once(XAPP_BASEDIR . '/Xapp/Error.php');
			require_once(XAPP_BASEDIR . '/Xapp/Event.php');
			require_once(XAPP_BASEDIR . '/Xapp/Option.php');
			require_once(XAPP_BASEDIR . '/Xapp/Xapp.php');

			/*
			require_once(XAPP_BASEDIR . '/Xapp/Debug.php');
			require_once(XAPP_BASEDIR . '/Xapp/Error.php');
			require_once(XAPP_BASEDIR . '/Xapp/Event.php');
			require_once(XAPP_BASEDIR . '/Xapp/Option.php');
			require_once(XAPP_BASEDIR . '/Xapp/Reflection.php');
			*/
		}
	}

	/***
	 * load XApp-PHP Rpc files
	 */
	public static function loadRPC()
	{
		include_once(XAPP_BASEDIR . '/Rpc/Exception.php');
		include_once(XAPP_BASEDIR . '/Rpc/Gateway.php');
		include_once(XAPP_BASEDIR . '/Rpc/Interface/Callable.php');
		include_once(XAPP_BASEDIR . '/Rpc/Smd/Exception/Exception.php');
		include_once(XAPP_BASEDIR . '/Rpc/Smd/Json.php');
		include_once(XAPP_BASEDIR . '/Rpc/Smd/Jsonp.php');
		include_once(XAPP_BASEDIR . '/Rpc/Smd/Jsonp.php');
		include_once(XAPP_BASEDIR . '/Rpc/Server/Json.php');
	}

	/***
	 * Returns whether we're in RPC introspection mode
	 * @return null
	 */
	public static function isSMDGet()
	{

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'GET') {
			$pageURL = self::getUrl();
			if (strpos($pageURL, 'view=rpc') !== false && strpos($pageURL, 'callback=') == false) {
				return true;
			}
		}
		return false;
	}

	/***
	 * Returns the type of request
	 * @return string
	 */
	public static function getServiceType()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$isJSONP = false;
		$hasJSONP = true;

		if ($hasJSONP) {
			$isJSONP = self::isJSONP();
		}

		if ($method === 'POST') {
			$hasJSONP = false;
		}

		if ($hasJSONP && $isJSONP && self::isDownload())
		{
			return self::DOWNLOAD;
		} elseif (self::isUpload()) {
			return self::UPLOAD;
		} elseif (self::isLogin()) {
			return self::LOGIN;
		} elseif (self::isSMDGet()) {
			return self::SMD_GET;
		} elseif (self::isSMDCall()) {
			return self::SMD_CALL;
		} elseif ($hasJSONP && $isJSONP && self::isSMDCall()) {
			return self::SMD_CALL;
		}
		return self::UNKNOWN;
	}

	/***
	 * Return whether the request is a RPC call
	 * @return bool
	 */
	public static function isSMDCall()
	{

		$method = $_SERVER['REQUEST_METHOD'];
		$pageURL = self::getUrl();
		if ($method === 'POST') {
			if (strpos($pageURL, 'view=smdCall') !== false) {
				return true;
			}
		} elseif ($method === 'GET') {
			if (strpos($pageURL, 'callback=') !== false) {
				return true;
			}
		}
		return false;
	}

	/***
	 * Returns PHP - POST as object
	 * @return mixed|null
	 */
	public static function getRawPostDecoded()
	{
		$_postData = file_get_contents("php://input");
		if ($_postData && strlen($_postData) > 0) {
			$_postData = json_decode($_postData);
			if ($_postData != null) {
				return $_postData;
			}
		}
		return null;
	}

	/***
	 * Returns the JSON-RPC-SMD Method of the current PHP POST or GET Uri
	 * @return null
	 */
	public static function getSMDMethod()
	{
		$_postData = self::getRawPostDecoded();
		if ($_postData != null) {
			if ($_postData->method != null) {
				return $_postData->method;
			}
		} else {
			$inUrl = '' . self::getUrl();
			if (isset($inUrl) && strlen($inUrl)) {
				$parts = parse_url($inUrl);

				$query = array();
				$queryString = $parts['query'];
				if ($queryString !== null) {
					parse_str($queryString, $query);
					if (array_key_exists('service', $query)) {
						$method = $query['service'];
						if ($method != null && strpos($method, '.') != -1) {
							$methodSplitted = explode('.', $method);
							if ($methodSplitted && count($methodSplitted) == 2) {
								return $method;
							}
						}
					}
				}
			}
		}
		return null;
	}

	/***
	 * Return whether the request is a RPC call
	 * @return bool
	 */
	public static function isLogin()
	{

		if (self::isSMDCall() && self::getSMDMethod() === 'XApp_XIDE_Controller_UserService.login') {
			return true;
		}
		return false;
	}

	/**
	 * Return true if its an upload to a RPC-Method
	 * @return bool
	 */
	public static function isUpload()
	{

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'POST') {
			$pageURL = self::getUrl();
			if (strpos($pageURL, 'view=upload') !== false) {
				return true;
			}
			if (strpos($pageURL, '.put') !== false) {
				return true;
			}
		}
		return false;
	}

	/***
	 * Little helper to determine a RPC2.0 method from RAW-POST
	 * @return null
	 */
	public static function isJSONP()
	{

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'GET') {
			$pageURL = self::getUrl();
			if (strpos($pageURL, 'callback') !== false) {
				return true;
			}
		}
		return false;
	}

	/***
	 * Return current request url
	 * @return string
	 */
	public static function getUrl()
	{
		$pageURL = 'http';
		if (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}

		$pageURL .= "://";

		$SERVER_NAME = $_SERVER["SERVER_NAME"];
		$SERVER_ADDR = $_SERVER["SERVER_ADDR"];
		$REMOTE_ADDRESS = $_SERVER["REMOTE_ADDR"];
		$isApache = $SERVER_NAME ==='::1' || $REMOTE_ADDRESS==='::1' || $SERVER_ADDR ==='::1';
		if(!$isApache && isset($_SERVER["HTTP_HOST"])) {
			$HTTP_HOST = $_SERVER["HTTP_HOST"];
			$PARTS = parse_url($HTTP_HOST);
			if(isset($PARTS['host'])) {
				$HOST = $PARTS['host'];
				$SERVER_NAME = $HOST;
			}
		}
		if ($_SERVER["SERVER_PORT"] != "80") {

			if(isset($_SERVER["HTTP_X_FORWARDED_HOST"])){
				$pageURL .=$_SERVER["HTTP_X_FORWARDED_HOST"];
				$pageURL .=$_SERVER["REQUEST_URI"];
			}else{
				$pageURL .= $SERVER_NAME . ":" . $_SERVER["SERVER_PORT"];
				$pageURL .=$_SERVER["REQUEST_URI"];
			}
		} else {


			if(isset($_SERVER["HTTP_X_FORWARDED_HOST"])){
				$pageURL .=$_SERVER["HTTP_X_FORWARDED_HOST"];
				$pageURL .=$_SERVER["REQUEST_URI"];
			}else{
				$pageURL .= $SERVER_NAME;
				$pageURL .=$_SERVER["REQUEST_URI"];
			}
		}
		return $pageURL;
	}

	/**
	 * Return true when its a download
	 * @return bool
	 */
	public static function isDownload()
	{

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'GET') {
			$pageURL = self::getUrl();
			if (strpos($pageURL, 'service=XCOM_Directory_Service.download') !== false) {
				return true;
			}

			if (strpos($pageURL, 'service=XCOM_Directory_Service.get') !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns the actual xfile operation from a RPC-Method
	 * @return string
	 */
	static function getXFileOperation()
	{

		$smdMethod = str_replace('Xapp_FileService.', '', self::getSMDMethod());
		switch ($smdMethod) {
			case 'copy':
				return XC_OPERATION_COPY_STR;
			case 'move':
				return XC_OPERATION_MOVE_STR;
			case 'deleteItems':
				return XC_OPERATION_DELETE_STR;
			case 'getContent':
				return XC_OPERATION_READ_STR;
			case 'setContent':
				return XC_OPERATION_EDIT_STR;
			case 'mkdir':
				return XC_OPERATION_NEW_DIRECTORY_STR;
			case 'mkfile':
				return XC_OPERATION_NEW_FILE_STR;
			case 'compress':
				return XC_OPERATION_COMPRESS_STR;
			case 'rename':
				return XC_OPERATION_READ_STR;
			case 'fileUpdate':
				return XC_OPERATION_FILE_UPDATE_STR;
			case 'download':
				return XC_OPERATION_DOWNLOAD_STR;
		}
		return XC_OPERATION_UNKOWN_STR;
	}

	/***
	 * Create a JSON-RPC-2.0 Error
	 * @param int $code
	 * @param $messages
	 * @return string
	 */
	static function toRPCErrorStd($code = 1, $messages)
	{
		$result = array();
		$result['error']['code'] = $code;
		$result['error']['message'] = $messages;
		return json_encode($result);
	}

	/**
	 * * Create a JSON-RPC-2.0 Error
	 * @param int $code
	 * @param $messages
	 * @return string
	 */
	static function toRPCError($code = 1, $messages)
	{
		$result = array();
		$result['code'] = $code;
		$result['result'] = array();
		$result['result'][] = $messages;
		return json_encode($result);
	}

	public static function getConsoleType()
	{
		$agent = '';
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}
		if (strlen(strstr($agent, "Firefox")) > 0) {

			return 'firephp';
		} elseif ((strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false)) {
			return 'chromephp';
		}
		return false;
	}
	/**
	 * Sanitizes a string key.
	 *
	 * Keys are used as internal identifiers. Lowercase & uppercase alphanumeric characters, dashes, comma and underscores are allowed.
	 *
	 * @param string $key String key
	 * @return string Sanitized key
	 */
	public static function _sanitize_key( $key ) {
		return preg_replace( '/[^A-Za-z0-9_.\/\-]/', '', $key );
	}

	/**
	 * Return a _GET key but sanitized
	 * @param $key
	 * @param $default
	 * @return string
	 */
	public static function _getKey($key,$default){
		if(isset($_GET[$key])){
			return self::_sanitize_key($_GET[$key]);
		}
		return $default;
	}

}