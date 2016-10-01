<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/***
 * Function to get plugin descriptions
 * @param string $type
 * @package XApp-Connect\Main
 * @param string $runTimeConfiguration
 * @return array
 */

if (!function_exists('xapp_get_plugin_infos')) {
	function xapp_get_plugin_infos()
	{
		$res = array();
		$pluginManager = XApp_PluginManager::instance();
		if ($pluginManager) {
			$res['items'] = $pluginManager->getPluginInfos(XAPP_PLUGIN_DIR, XAPP_PLUGIN_TYPE);
			$res['class'] = 'jsontype.ComposerPackages';
		}
		return $res;
	}
}

if (!function_exists('xapp_fix_index')) {

	function xapp_fix_index()
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
}

if (!function_exists('xapp_get_script')) {

	function xapp_get_script()
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
}


/***
 *
 * @param null $lang
 */
function xapp_setup_language_standalone($lang = null)
{


	if (!function_exists('XAPP_TEXT')) {

		$default_locale = 'en-GB';

		$locale_file_prefix = 'com_xappcommander';
		$localPath = XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;

		$testPath = $localPath . $default_locale . '.' . $locale_file_prefix . '.ini';

		if (!file_exists($testPath)) {
			error_log('have no language file at ' . $testPath);

		} else {
			require_once(XAPP_BASEDIR . '/Config/Config.php');
			require_once(XAPP_BASEDIR . '/Config/Ini.php');

			$config = new Xapp_Config_Ini($testPath);
			if ($config) {
				XApp_Service_Entry_Utils::$languageClass = $config;
			} else {
				error_log('have no language data at ' . $testPath);
			}

		}

		function XAPP_TEXT($string, $lang = null, $component = null, $arguments = null)
		{

			if (!XApp_Service_Entry_Utils::$languageClass) {
				return $string;
			} else {
				$confValue = XApp_Service_Entry_Utils::$languageClass->get($string);
				if ($confValue) {
					return $confValue;
				}
			}
			return $string;
		}

		if (!function_exists('XAPP_TEXT_FORMATTED')) {

			function XAPP_TEXT_FORMATTED($string, $args = array())
			{

				$toFormat = XAPP_TEXT($string, null, null, null);
				return vsprintf($toFormat, $args);
			}
		}
	}
}

function xapp_setup_language($lang = null)
{

	if (!function_exists('XAPP_TEXT')) {

		if ((bool)xc_conf(XC_CONF_JOOMLA)) {
			$langToUse = $lang !== null ? $lang : JLanguage::getInstance(null)->getDefault(
			);//;XApp_Service_Entry_Utils::getBrowserDefaultLanguage();


			XApp_Service_Entry_Utils::$languageClass = JLanguage::getInstance($langToUse);
			XApp_Service_Entry_Utils::$language = $langToUse;


			function XAPP_TEXT($string, $lang = null, $component = null)
			{

				$langToUse = $lang !== null ? $lang : JLanguage::getInstance(null)->getDefault(
				);//;XApp_Service_Entry_Utils::getBrowserDefaultLanguage();

				$result = '' . $string;

				/***
				 * Catch language change
				 */
				if ($lang !== null && XApp_Service_Entry_Utils::$language != null) {
					if (XApp_Service_Entry_Utils::$language !== $lang) {
						$newInstance = JLanguage::getInstance($lang);
						if ($newInstance) {

							XApp_Service_Entry_Utils::$languageClass = JLanguage::getInstance($lang);
							JFactory::$language = XApp_Service_Entry_Utils::$languageClass;
						}
					}
				}

				//no lang specified
				if (XApp_Service_Entry_Utils::$languageClass == null) {
					$newInstance = JLanguage::getInstance($langToUse);
					if ($newInstance != null) {
						XApp_Service_Entry_Utils::$languageClass = $newInstance;
					} else {

					}
				}


				if (XApp_Service_Entry_Utils::$languageClass !== null) {

					if ($component) {
						XApp_Service_Entry_Utils::$languageClass->load($component);
					}
					$result = XApp_Service_Entry_Utils::$languageClass->_($string, true);
					if ($result !== null && $result !== $string) {
						//return $result;
					}
				}
				$result = JText::_($string, true);

				return $result;
			}

		}
	}
}

function xapp_setup_language_joomla($lang = null)
{

	if (!function_exists('XAPP_TEXT')) {


		$langToUse = $lang !== null ? $lang : JLanguage::getInstance(null)->getDefault(
		);//;XApp_Service_Entry_Utils::getBrowserDefaultLanguage();
		XApp_Service_Entry_Utils::$languageClass = JLanguage::getInstance($langToUse);
		XApp_Service_Entry_Utils::$language = $langToUse;
		function XAPP_TEXT($string, $lang = null, $component = null, $arguments = null)
		{

			$langToUse = $lang !== null ? $lang : JLanguage::getInstance(null)->getDefault(
			);//;XApp_Service_Entry_Utils::getBrowserDefaultLanguage();

			if ($lang == null) {
				$lang = '' . $langToUse;
			}

			$result = '' . $string;

			/***
			 * Catch language change
			 */
			if ($lang !== null && XApp_Service_Entry_Utils::$language != null) {
				if (XApp_Service_Entry_Utils::$language !== $lang) {
					$newInstance = JLanguage::getInstance($lang);
					if ($newInstance) {
						XApp_Service_Entry_Utils::$languageClass = JLanguage::getInstance($lang);
						JFactory::$language = XApp_Service_Entry_Utils::$languageClass;
					}
				}
			}

			//no lang specified
			if (XApp_Service_Entry_Utils::$languageClass == null) {
				$newInstance = JLanguage::getInstance($langToUse);
				if ($newInstance != null) {
					XApp_Service_Entry_Utils::$languageClass = $newInstance;
				}
			}


			if (XApp_Service_Entry_Utils::$languageClass !== null) {

				if ($component) {
					XApp_Service_Entry_Utils::$languageClass->load($component);
				}
				$result = XApp_Service_Entry_Utils::$languageClass->_($string, true);
				if ($result !== null && $result !== $string && strlen($result) > 0) {
					return $result;
				}
			}
			$result = JText::_($string, true);

			if (strlen($result) > 0) {
				return $result;
			}
			return $string;
		}

		if (!function_exists('XAPP_TEXT_FORMATTED')) {

			function XAPP_TEXT_FORMATTED($string, $args = array())
			{
				$toFormat = XAPP_TEXT($string, null, null, null);
				return vsprintf($toFormat, $args);
			}
		}

	}

}

function xapp_setup_language_wordpress($lang = null)
{

	if (!function_exists('XAPP_TEXT')) {

		$default_locale = 'en-GB';
		$wordpress_locale = get_locale();//en_US
		$wordpress_locale = str_replace('_', '-', $wordpress_locale);

		$locale_file_prefix = 'com_xappcommander';
		$localPath = XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
		$testPath = $localPath . $wordpress_locale . '.' . $locale_file_prefix . '.ini';
		if (file_exists($testPath)) {

		} else {
			$testPath = $localPath . $default_locale . '.' . $locale_file_prefix . '.ini';
		}
		if (!file_exists($testPath)) {

		} else {
			require_once(XAPP_BASEDIR . '/Config/Config.php');
			require_once(XAPP_BASEDIR . '/Config/Ini.php');


			$config = new Xapp_Config_Ini($testPath);
			if ($config) {
				XApp_Service_Entry_Utils::$languageClass = $config;
			}

		}

		function XAPP_TEXT($string, $lang = null, $component = null, $arguments = null)
		{

			$result = '' . $string;

			if (!XApp_Service_Entry_Utils::$languageClass) {
				return $string;
			} else {
				$confValue = XApp_Service_Entry_Utils::$languageClass->get($string);
				if ($confValue) {
					return $confValue;
				}
			}
			return $string;
		}

		if (!function_exists('XAPP_TEXT_FORMATTED')) {

			function XAPP_TEXT_FORMATTED($string, $args = array())
			{

				$toFormat = XAPP_TEXT($string, null, null, null);
				return vsprintf($toFormat, $args);
			}
		}
	}

}

/***
 * Collection of handy utils for the service entry point
 * Class XApp_Service_Entry_Utils
 */
class XApp_Service_Entry_Utils
{


	/***
	 *  XApp-Service-Types
	 */
	const SMD_GET = "XAPP_SERVICE_TYPE_SMD_GET";
	const SMD_CALL = "XAPP_SERVICE_TYPE_SMD_CALL";
	const UPLOAD = "XAPP_SERVICE_TYPE_UPLOAD";
	const DOWNLOAD = "XAPP_SERVICE_TYPE_DOWNLOAD";
	const CBTREE = "XAPP_SERVICE_TYPE_CBTREE";
	const UNKNOWN = "XAPP_SERVICE_TYPE_UNKNOWN";
	const LOGIN = "XAPP_SERVICE_TYPE_LOGIN";

	public static function isMobile()
	{
		$is_mobile = false;

		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			$is_mobile = false;
		} elseif (strpos($_SERVER['HTTP_USER_AGENT'],'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
		) {
			$is_mobile = true;
		} else {
			$is_mobile = false;
		}

		return $is_mobile;
	}


	/***
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

	static function toRPCError($code = 1, $messages)
	{
		$result = array();
		$result['code'] = $code;
		$result['result'] = array();
		$result['result'][] = $messages;
		return json_encode($result);
	}

	static function getXCommanderOperation()
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
			case 'extract':
				return XC_OPERATION_EXTRACT_STR;
		}
		return XC_OPERATION_UNKOWN_STR;
	}


	/*
	 * String op to integer
	 */
	static function opToInteger($what)
	{

		switch ($what) {
			case XC_OPERATION_MOVE_STR:
				return XC_OPERATION_MOVE;
			case XC_OPERATION_DELETE_STR:
				return XC_OPERATION_DELETE;
			case XC_OPERATION_COPY_STR:
				return XC_OPERATION_COPY;
			case XC_OPERATION_READ_STR:
				return XC_OPERATION_READ;
			case XC_OPERATION_WRITE_STR:
				return XC_OPERATION_WRITE;
			case XC_OPERATION_EDIT_STR:
				return XC_OPERATION_EDIT;
			case XC_OPERATION_COMPRESS_STR:
				return XC_OPERATION_COMPRESS;
			case XC_OPERATION_RENAME_STR:
				return XC_OPERATION_RENAME;
			case XC_OPERATION_DOWNLOAD_STR:
				return XC_OPERATION_DOWNLOAD;
			case XC_OPERATION_NEW_DIRECTORY_STR:
				return XC_OPERATION_NEW_DIRECTORY;
			case XC_OPERATION_NEW_FILE_STR:
				return XC_OPERATION_NEW_FILE;
			case XC_OPERATION_CUSTOM_STR:
				return XC_OPERATION_CUSTOM;
			case XC_OPERATION_FIND:
				return XC_OPERATION_FIND;
			case XC_OPERATION_UPLOAD_STR:
				return XC_OPERATION_UPLOAD;
			case XC_OPERATION_PLUGINS_STR:
				return XC_OPERATION_PLUGINS;
			case XC_OPERATION_EXTRACT_STR:
				return XC_OPERATION_EXTRACT;
		}

		return XC_OPERATION_UNKOWN;
	}

	/**
	 * Known RPC methods
	 * @return array
	 */
	static function getXCommanderFuncTable()
	{

		$verbs = array(
			XC_OPERATION_COPY_STR,
			XC_OPERATION_MOVE_STR,
			XC_OPERATION_DELETE_STR,
			XC_OPERATION_READ_STR,
			XC_OPERATION_WRITE_STR,
			XC_OPERATION_EDIT_STR,
			XC_OPERATION_NEW_DIRECTORY_STR,
			XC_OPERATION_COMPRESS_STR,
			XC_OPERATION_RENAME_STR,
			XC_OPERATION_DOWNLOAD_STR,
			XC_OPERATION_FILE_UPDATE_STR,
			XC_OPERATION_NEW_DIRECTORY_STR,
			XC_OPERATION_NEW_FILE_STR,
			XC_OPERATION_UPLOAD_STR,
			XC_OPERATION_DOWNLOAD_STR,
			XC_OPERATION_EXTRACT_STR
		);
		return $verbs;
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
		if ($hasJSONP && $isJSONP && XApp_Service_Entry_Utils::isDownload()) {
			return self::DOWNLOAD;
		} elseif (XApp_Service_Entry_Utils::isUpload()) {
			return self::UPLOAD;
		} elseif (XApp_Service_Entry_Utils::isLogin()) {
			return self::LOGIN;
		} elseif (XApp_Service_Entry_Utils::isSMDGet()) {
			return self::SMD_GET;
		} elseif (XApp_Service_Entry_Utils::isSMDCall()) {
			return self::SMD_CALL;
		} elseif (XApp_Service_Entry_Utils::isCBTree()) {
			return self::CBTREE;
		} elseif ($hasJSONP && $isJSONP && XApp_Service_Entry_Utils::isSMDCall()) {
			return self::SMD_CALL;
		}
		return self::UNKNOWN;
	}


	public static $serviceConf = null;
	public static $languageClass = null;
	public static $language = null;
	public static $locale_data = null;

	/**
	 * Get the user's browser default language
	 *
	 * @return string The language code
	 *
	 * @since 1.0.0
	 */
	public static function getBrowserDefaultLanguage()
	{
		$langs = array();

		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

			preg_match_all(
				'/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
				$_SERVER['HTTP_ACCEPT_LANGUAGE'],
				$lang_parse
			);

			if (count($lang_parse[1])) {

				$langs = array_combine($lang_parse[1], $lang_parse[4]);

				foreach ($langs as $lang => $val) {
					if ($val === '') {
						$langs[$lang] = 1;
					}
				}

				arsort($langs, SORT_NUMERIC);
			}
		}
		return array_shift(explode('-', array_shift(array_keys($langs))));
	}

	/***
	 * Fixes common lang version problems
	 */
	public static function init()
	{


	}

	/***
	 * Include and setup RainTpl
	 */
	public static function setupRainTpl()
	{

		if (!class_exists('Tpl')) {
			/***
			 * Template Engine
			 */
			include(XAPP_CONNECT_LEGACY . 'TplNew.php');
		}

		Tpl::$cache_dir = XAPP_BASEDIR . '/cache/';
	}

	/***
	 * Include and setup RainTpl
	 */
	public static function setupLucene()
	{

		if (!class_exists('LuceneIndexer')) {

			if (file_exists(XAPP_LIB . "lucene/LuceneIndexer.php")) {

				include_once XAPP_LIB . "lucene/LuceneIndexer.php";
				set_include_path(get_include_path() . PATH_SEPARATOR . XAPP_LIB . "/lucene");
			}
		}

	}

	/***
	 * Setup logger with writer
	 */
	public static function setupLogger($writer = false)
	{

		$writer = null;
		$log = null;
		$logging_options = null;

		$logDir = realpath(XAPP_BASEDIR . '/cacheDir/');
		if (!is_dir($logDir) && !is_writable($logDir)) {
			@mkdir($logDir, 777, true);
		}
		/*
		if(file_exists($logDir)){
			xapp_import('xapp.File.Utils');
			//XApp_File_Utils::mkDir(XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'cacheDir');
		}
		*/
		if ($writer) {

			$writer = new Xapp_Log_Writer_File(XAPP_BASEDIR . '/cacheDir/');
			$logging_options = array(
				Xapp_Log::PATH => XAPP_BASEDIR . '/cacheDir/',
				Xapp_Log::EXTENSION => 'log',
				Xapp_Log::NAME => 'error',
				Xapp_Log::WRITER => array($writer),
				Xapp_Log_Error::STACK_TRACE => false
			);
			$log = new Xapp_Log_Error($logging_options);
		} else {
			$logging_options = array(
				Xapp_Log::PATH => XAPP_BASEDIR . '/cacheDir/',
				Xapp_Log::EXTENSION => 'log',
				Xapp_Log::NAME => 'error',
				Xapp_Log_Error::STACK_TRACE => false
			);
			$log = new Xapp_Log_Error($logging_options);
		}

		return $log;
	}

	public static function adjustPaths($path)
	{
		$os = PHP_OS;
		$separator = '/';
		switch ($os) {
			case "Linux":
				$separator = "/";
				break;
			case "Windows":
				$separator = "\\";
				break;
		}
		return str_replace('/', $separator, $path);
	}

	/***
	 * Include XApp-JSON-Store Files
	 */
	public static function includeXAppJSONStoreClasses()
	{

		if (!class_exists('Xapp_Util_JsonStorage')) {

			/***
			 * Import JSON-Store classes from 'xapp/Util'
			 */

			require_once(self::adjustPaths(XAPP_BASEDIR . '/Util/Storage.php'));
			if (!class_exists('Xapp_Util_Std')) {
				require_once(self::adjustPaths(XAPP_BASEDIR . '/Util/Std/Std.php'));
				require_once(self::adjustPaths(XAPP_BASEDIR . '/Util/Std/Query.php'));
				require_once(self::adjustPaths(XAPP_BASEDIR . '/Util/Std/Store.php'));
				require_once(self::adjustPaths(XAPP_BASEDIR . '/Util/Json/Json.php'));
				require_once(self::adjustPaths(XAPP_BASEDIR . '/Util/Json/Query.php'));
				require_once(self::adjustPaths(XAPP_BASEDIR . '/Util/Json/Store.php'));
			}
		}
	}

	/***
	 * Include XApp-PHP-Core files
	 */
	public static function includeXAppCore()
	{

		if (!defined('XAPP')) {
			include_once(self::adjustPaths(XAPP_BASEDIR . '/Core/core.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Xapp.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Autoloader.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Cli.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Console.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Debug.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Error.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Event.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Option.php'));
			require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Reflection.php'));
		}
	}

	/***
	 * Include XApp-PHP-Rpc files
	 */
	public static function includeXAppRPC()
	{

		include_once(self::adjustPaths(XAPP_BASEDIR . '/Rpc/Exception.php'));

		include_once(self::adjustPaths(XAPP_BASEDIR . '/Rpc/Gateway.php'));

		include_once(self::adjustPaths(XAPP_BASEDIR . 'Rpc/Interface/Callable.php'));


		include_once(self::adjustPaths(XAPP_BASEDIR . 'Rpc/Smd/Exception/Exception.php'));
		include_once(self::adjustPaths(XAPP_BASEDIR . 'Rpc/Smd/Json.php'));
		include_once(self::adjustPaths(XAPP_BASEDIR . 'Rpc/Smd/Jsonp.php'));


		include_once(self::adjustPaths(XAPP_BASEDIR . 'Rpc/Smd/Jsonp.php'));
		include_once(self::adjustPaths(XAPP_BASEDIR . 'Rpc/Server/Json.php'));
	}


	public static function includeXAppRegistry()
	{

		require_once(self::adjustPaths(XAPP_BASEDIR . '/Xapp/Registry.php'));
	}

	/***
	 * Include XApp-PHP-Core files
	 */
	public static function includeXAppDebugTools()
	{

		//require_once(self::adjustPaths(XAPP_BASEDIR . "connect/utils/Debugging.php"));
		xapp_import('xapp.Utils.Debugging');
	}

	/***
	 * Include XApp-JSON-Utils
	 */
	public static function includeXAppJSONTools()
	{

		xapp_import('xapp.Utils.JSONUtils');
	}


	/***
	 * Include XApp-Connect-Core Classes
	 */
	public static function includeXAppConnectCore()
	{

		if (!function_exists('xc_conf')) {
			require_once(dirname(__FILE__) . '/connect/legacy/conf.php');
		}

		include(self::adjustPaths(XAPP_BASEDIR . "connect/Indexer.php"));//lucene wrapper
		include(self::adjustPaths(XAPP_BASEDIR . "connect/Plugin.php"));//plugin def
		include(self::adjustPaths(XAPP_BASEDIR . "connect/IPlugin.php"));//to be implemented
		include(self::adjustPaths(XAPP_BASEDIR . "connect/RPCPlugin.php"));//base class
		include(self::adjustPaths(XAPP_BASEDIR . "connect/Configurator.php"));//remove !
		include(self::adjustPaths(XAPP_BASEDIR . "connect/joomla/JoomlaPlugin.php"));//joomla basics
		include(self::adjustPaths(
			XAPP_BASEDIR . "connect/FakePlugin.php"
		));//Fake plugin will emulate a RPC plugin for older versions of XApp-Connect-Types.
		include(self::adjustPaths(XAPP_BASEDIR . "connect/CustomTypeManager.php"));//Sync and tools to xapp-studio.com !
		include(self::adjustPaths(
			XAPP_BASEDIR . "connect/PluginManager.php"
		));//Sends Messages to ./connect/Joomla/* or /connect/wordpress
		include(self::adjustPaths(XAPP_BASEDIR . "connect/filter/Filter.php"));//base filter class
		include(self::adjustPaths(
			XAPP_BASEDIR . "connect/filter/Schema.php"
		));//schema filter (Supports : Inline PHP scripting from client : Applies Result Schema on MySQL or Class queries)

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

	/***
	 * Little helper to determine a RPC2.0 method from RAW-POST
	 * @return null
	 */
	public static function isJSONP()
	{

		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'GET') {
			$pageURL = self::getUrl();
			if (strpos($pageURL, 'xapp_get_plugin_infos') !== false) {
				return true;
			}
			if (strpos($pageURL, 'callback') !== false) {
				return true;
			}
		}
		return false;
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

	/**
	 * Return true if we are on https
	 * @return bool
	 */
	public static function isSecure()
	{
		return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
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

	public static function isPictureService()
	{


		$domain = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$referer = XApp_Service_Entry_Utils::getReferer(false);
		$pageURL = self::getUrl();
		if (self::isDownload() || strpos($pageURL, 'fileUpdate') !== false) {
			xapp_import('xapp.Utils.Strings');

			if ($domain === 'ec2-75-101-241-140.compute-1.amazonaws.com') {
				return true;
			}

			if ($domain === 'ec2-50-19-185-53.compute-1.amazonaws.com') {
				return true;
			}

			if (XApp_Utils_Strings::startsWith($referer, 'http://cdn.pixlr.com/editor/')) {
				return true;
			}
		}
		return false;
	}

	public static function isPictureService2()
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

	public static function isDownload()
	{


		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'GET') {
			$pageURL = self::getUrl();
			if (strpos($pageURL, 'service=XCOM_Directory_Service.download') !== false) {
				return true;
			}
			if (strpos($pageURL, 'service=XIDE_VE_Service.view') !== false) {
				return true;
			}

			if (strpos($pageURL, 'service=XCOM_Directory_Service.get') !== false) {
				return true;
			}
		}
		return false;
	}

	/***
	 *
	 * @return null
	 */
	public static function isCBTree()
	{

		$pageURL = self::getUrl();
		if (strpos($pageURL, 'view=cbtree') !== false) {
			return true;
		}
		return false;
	}

	/**
	 * get the server referer from request. returns null if not found
	 *
	 * @error 14417
	 * @return null|string
	 */
	public static function getReferer($domainOnly = true)
	{
		if (strtolower(php_sapi_name()) !== 'cli') {
			if (getenv('HTTP_ORIGIN') && strcasecmp(getenv('HTTP_ORIGIN'), 'unknown')) {
				$ref = getenv('HTTP_ORIGIN');
			} else {
				if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] && strcasecmp(
						$_SERVER['HTTP_ORIGIN'],
						'unknown'
					)
				) {
					$ref = $_SERVER['HTTP_ORIGIN'];
				} else {
					if (getenv('HTTP_REFERER') && strcasecmp(getenv('HTTP_REFERER'), 'unknown')) {
						$ref = getenv('HTTP_REFERER');
					} else {
						if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && strcasecmp(
								$_SERVER['HTTP_REFERER'],
								'unknown'
							)
						) {
							$ref = $_SERVER['HTTP_REFERER'];
						} else {
							$ref = false;
						}
					}
				}
			}

			if ($ref !== false && !empty($ref)) {
				if (!$domainOnly) {
					return $ref;
				}
				if (($host = parse_url($ref, PHP_URL_HOST)) !== false) {
					return trim($host);
				}
			}
		}
		return null;
	}

	/***
	 * Little helper to determine debug config
	 * @return null
	 */
	public static function isDebug()
	{
		$pageURL = self::getUrl();
		if (strpos($pageURL, 'debug=true') !== false) {
			return true;
		}
		return false;
	}

	/***
	 * Little helper to determine debug config
	 * @return null
	 */
	public static function isReleaseDebug()
	{
		$pageURL = self::getUrl();
		if (strpos($pageURL, 'release-debug=true') !== false) {
			return true;
		}
		return false;
	}

	/***
	 * Little helper to determine debug config
	 * @return null
	 */
	public static function isDesktopApp()
	{
		$pageURL = self::getUrl();

		if (strpos($pageURL, 'isDesktop=true') !== false) {
			return true;
		}
		return false;
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

	public static function isRaw()
	{
		$pageURL = self::getUrl();
		if (strpos($pageURL, 'raw=html') !== false) {
			return true;
		}
		if (strpos($pageURL, 'raw=yes') !== false) {
			return true;
		}
		return false;
	}

	/***
	 * Little helper to determine run time configuration
	 * @return string
	 */
	public static function getRunTimeConfiguration()
	{
		if(self::isReleaseDebug()){
			//return 'release-debug';
		}

		return self::isDebug() ? 'debug' : 'release';
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
			$inUrl = '' . XApp_Service_Entry_Utils::getUrl();
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

	/**
	 * Sanitizes a string key.
	 *
	 * Keys are used as internal identifiers. Lowercase & uppercase alphanumeric characters, dashes, comma and underscores are allowed.
	 *
	 * @param string $key String key
	 * @return string Sanitized key
	 */
	public static function _sanitize_key( $key ) {
		return preg_replace( '/[^A-Za-z0-9_.\/\%-:\\\\]/', '', $key );
	}

	/**
	 * Return a _GET key but sanitized
	 * @param $key
	 * @param $default
	 * @return string
	 */
	public static function _getKey($key,$default=''){
		if(isset($_GET[$key])){
			return self::_sanitize_key($_GET[$key]);
		}
		return $default;
	}
}

if (!function_exists('xcom_event')) {
	/**
	 * @param $operation
	 * @param string $suffix
	 * @param $args
	 * @param null $callee
	 */
	function xcom_event($operation, $suffix = '', $args, $callee = null)
	{
		if ($callee !== null && $args !== null) {
			$args[XAPP_EVENT_KEY_CALLEE] = $callee;
		}
		xapp_event(XAPP_EVENT_PREFIX . $suffix . $operation, array($args));
	}
}
if (!function_exists('xcom_subscribe')) {

	/**
	 * @param $operation
	 * @param $mixed
	 * @param string $suffix
	 */
	function xcom_subscribe($operation, $mixed, $suffix = '')
	{
		Xapp_Event::listen(XAPP_EVENT_PREFIX . $operation . $suffix, $mixed);
	}
}
