<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package xapp
 */

/***
 * Processing Flag 'Load Client Resources' instructs the bootstrapper to gather a Dojo's application client side
 * resource files.
 */
define('XAPP_BOOTSTRAP_LOAD_CLIENT_RESOURCES', 55022);

/***
 * Processing Flag 'Load Plugin Resources' instructs the bootstrapper to gather a client side plugin resources
 */
define('XAPP_BOOTSTRAP_LOAD_PLUGIN_RESOURCES', 55023);

/***
 * Processing Flag 'Create resource renderer' instructs the bootstrapper to create a resource renderer. This is needed
 * when rendering a Dojo application
 */
define('XAPP_BOOTSTRAP_CREATE_RESOURCE_RENDERER', 55024);

/***
 * Processing Flag 'Register server plugins ' instructs the bootstrapper to register plugins on the RPC server
 */
define('XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS', 55025);

/***
 * Processing Flag 'Setup XApp' instructs the bootstrapper to setup xapp-php core, which handles output stream and some debugging options
 */
define('XAPP_BOOTSTRAP_SETUP_XAPP', 55026);

/***
 * Processing Flag 'Setup RPC Server' instructs the bootstrapper to create our standard RPC server
 */
define('XAPP_BOOTSTRAP_SETUP_RPC', 55027);

/***
 * Processing Flag 'Setup File Service' instructs the bootstrapper to create our standard file service
 */
define('XAPP_BOOTSTRAP_SETUP_XFILE', 55028);

/***
 * Processing Flag 'Setup Services' instructs the bootstrapper to register a number of services on the RPC server
 */
define('XAPP_BOOTSTRAP_SETUP_SERVICES', 55029);

/***
 * Processing Flag 'Setup Gateway'
 */
define('XAPP_BOOTSTRAP_SETUP_GATEWAY', 55030);

/***
 * Processing Flag 'Setup Logger'
 */
define('XAPP_BOOTSTRAP_SETUP_LOGGER', 55031);

/***
 * Processing Flag 'Setup Console'
 */
define('XAPP_BOOTSTRAP_SETUP_CONSOLE', 55032);

/***
 * Processing Flag 'Setup Store'
 */
define('XAPP_BOOTSTRAP_SETUP_STORE', 55033);

/***
 * Processing Flag 'Render Head'
 */
define('XAPP_BOOTSTRAP_RENDER_HEAD', 55034);

/***
 * Processing Flag 'Render Body'
 */
define('XAPP_BOOTSTRAP_RENDER_BODY', 55035);

/***
 * Processing Flag 'Render Body'
 */
define('XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION', 55036);

/***
 * Processing Flag 'Enable Google Analytics' instructs the bootstrapper to load Google Analytics
 */
define('XAPP_BOOTSTRAP_ENABLE_GOOGLE_ANALYTICS', 55037);
/***
 * Logging
 */
$global_xapp_logger = null;
/***
 * Processing Flag 'Setup Console'
 */
define('XAPP_LOG_PLUGIN_CREATION', 25030);
define('XAPP_LOG_XFILE_OPERATIONS', 25031);
define('XAPP_LOG_SHARED_LOGGER_PLUGINS', 25032);
define('XAPP_LOG_SHARED_LOGGER_SERVICES', 25033);

/***
 * Check we have at least the minimals
 */
if (!defined('XAPP')) {
	include_once(XAPP_BASEDIR . '/Core/core.php');
	require_once(XAPP_BASEDIR . '/Xapp/Xapp.php');
	require_once(XAPP_BASEDIR . '/Xapp/Autoloader.php');
	require_once(XAPP_BASEDIR . '/Xapp/Cli.php');
	require_once(XAPP_BASEDIR . '/Xapp/Console.php');
	require_once(XAPP_BASEDIR . '/Xapp/Debug.php');
	require_once(XAPP_BASEDIR . '/Xapp/Error.php');
	require_once(XAPP_BASEDIR . '/Xapp/Event.php');
	require_once(XAPP_BASEDIR . '/Xapp/Option.php');
	require_once(XAPP_BASEDIR . '/Xapp/Reflection.php');
}

/***
 * Class XApp_Bootstrap
 * Utility class to do the initial work.
 */
class XApp_Bootstrap
{
	/***
	 * File name of the resource config
	 */
	const XAPP_RESOURCE_CONFIG= "XAPP_RESOURCE_CONFIG";

	/***
	 * File name of the resource config
	 */
	const OVERRIDE = "XAPP_BOOTSTRAP_OVERRIDE";

	/***
	 * Flags to use whilst the bootstrapping
	 */
	const FLAGS = "XAPP_BOOTSTRAP_FLAGS";

	/***
	 * Logging flags
	 */
	const LOGGING_FLAGS = "XAPP_BOOTSTRAP_LOGGING_FLAGS";

	/***
	 * The RPC server. This should be in instance to a JSON-RPC or JSONP server.
	 */
	const RPC_SERVER = "XAPP_BOOTSTRAP_RPC_SERVER";

	/***
	 * The class name of the server application
	 */
	const SERVER_APPLICATION_CLASS = "XAPP_SERVER_APPLICATION_CLASS";

	/***
	 * Absolute path to xapp base directory
	 */
	const BASEDIR = "XAPP_BOOTSTRAP_BASEDIR";

	/***
	 * Absolute path to the xapp client directory
	 */
	const APPDIR = "XAPP_BOOTSTRAP_APP_DIR";

	/***
	 * Relative path to the client app's rpc service
	 */
	const SERVICE = "XAPP_BOOTSTRAP_SERVICE";

	/***
	 * Name of the client app.
	 */
	const APP_NAME = "XAPP_APP_NAME";


	/***
	 * Path segment to the Dojo app.
	 */
	const APP_FOLDER = "XAPP_APP_FOLDER";

	/***
	 * Relative resource variables
	 */
	const RELATIVE_VARIABLES = "XAPP_BOOTSTRAP_RELATIVE_VARS";

	/***
	 * Render delegate to render out client resources into the HTML head section
	 */
	const RENDER_DELEGATE = "XAPP_RESOURCE_RENDER_DELEGATE";

	/***
	 * Relative url to the client's app doc root
	 */
	const DOC_ROOT = "XAPP_APP_DOC_ROOT";

	/***
	 * Additional resource config prefix
	 */
	const RESOURCE_CONFIG_SUFFIX = "XAPP_RESOURCE_CONFIG_SUFFIX";
	/***
	 * Additional resource config prefix
	 */
	const RESOURCE_CONFIG_PREFIX = "XAPP_RESOURCE_CONFIG_PREFIX";

	/***
	 * Additional resource renderer prefix : wordpress/joomla
	 */
	const RESOURCE_RENDERER_PREFIX = "XAPP_RESOURCE_RENDERER_PREFIX";

	/***
	 * Resource renderer class
	 */
	const RESOURCE_RENDERER_CLZ = "XAPP_RESOURCE_RENDERER_CLZ";

	/***
	 * Plugin directory
	 */
	const PLUGIN_DIRECTORY = "XAPP_RESOURCE_PLUGIN_DIR";

	/***
	 * Plugin directory
	 */
	const PLUGIN_MASK = "XAPP_RESOURCE_PLUGIN_MASK";

	/***
	 * Allow plugins
	 */
	const ALLOW_PLUGINS = "XAPP_BOOTSTRAP_ALLOW_PLUGINS";

	/***
	 * Forbidden plugins, comma separated
	 */
	const PROHIBITED_PLUGINS = "XAPP_BOOTSTRAP_PROHIBITED_PLUGINS";

	/***
	 * Auth delegate
	 */
	const AUTH_DELEGATE = "XAPP_BOOTSTRAP_AUTH_DELEGATE";

	/***
	 * RPC target is the entry point for client calls
	 */
	const RPC_TARGET = "XAPP_BOOTSTRAP_RPC_TARGET";

	/***
	 * Ignored RPC methods. If an auth delegate is given, its trying to build this list on own
	 */
	const IGNORED_RPC_METHODS = "XAPP_BOOTSTRAP_IGNORED_RPC_METHODS";

	/***
	 * File service  configuration
	 */
	const XFILE_CONF = "XAPP_BOOTSTRAP_FILE_CONF";

	/***
	 * Service map
	 */
	const SERIVCE_CONF = "XAPP_BOOTSTRAP_RPC_SERVICES";

	/***
	 * Gateway configuration
	 */
	const GATEWAY_CONF = "XAPP_BOOTSTRAP_GATEWAY_CONF";

	/***
	 * XApp configuration
	 */
	const XAPP_CONF = "XAPP_BOOTSTRAP_XAPP_CONF";

	/***
	 * Logging configuration
	 */
	const LOGGING_CONF = "XAPP_BOOTSTRAP_LOGGING_CONF";

	/***
	 * Store configuration
	 */
	const STORE_CONF = "XAPP_BOOTSTRAP_STORE_CONF";

	/***
	 * Demand signing of this service types
	 */
	const SIGNED_SERVICE_TYPES = "XAPP_BOOTSTRAP_SIGNED_SERVICE_TYPES";

	/***
	 * Token to use for signed request
	 */
	const SIGNING_TOKEN = "XAPP_BOOTSTRAP_SIGNING_TOKEN";

	/***
	 * Signing key
	 */
	const SIGNING_KEY = "XAPP_BOOTSTRAP_SIGNING_KEY";

	/***
	 * Tracked logger instance, might be shared with plugins and other managed instances
	 */
	const LOGGER = "XAPP_BOOTSTRAP_LOGGER";

	/***
	 * Tracked store service instance, might be shared with plugins and other managed instances
	 */
	const STORE = "XAPP_BOOTSTRAP_STORE";

	/***
	 * Tracked gateway instance, might be shared with plugins and other managed instances
	 */
	const GATEWAY = "XAPP_BOOTSTRAP_GATEWAY";

	/***
	 * Tracked gateway instance, might be shared with plugins and other managed instances
	 */
	const USER_CONF = "XAPP_BOOTSTRAP_USER_CONFIGURATION";

	/***
	 * Google - Analytics
	 */
	const GOOGLE_ANALYTICS_ID = "XAPP_GA_ID";

	/**
	 * options dictionary for this class containing all data type values
	 *
	 * @var array
	 */
	public static $optionsDict = array(
			self::BASEDIR => XAPP_TYPE_STRING,
			self::SERVER_APPLICATION_CLASS => XAPP_TYPE_STRING,
			self::APPDIR => XAPP_TYPE_STRING,
			self::SERVICE => XAPP_TYPE_STRING,
			self::APP_NAME => XAPP_TYPE_STRING,
			self::APP_FOLDER => XAPP_TYPE_STRING,
			self::RELATIVE_VARIABLES => XAPP_TYPE_ARRAY,
			self::DOC_ROOT => XAPP_TYPE_STRING,
			self::RESOURCE_CONFIG_SUFFIX => XAPP_TYPE_STRING,
			self::RESOURCE_RENDERER_PREFIX => XAPP_TYPE_STRING,
			self::RESOURCE_RENDERER_CLZ => XAPP_TYPE_STRING,
			self::RENDER_DELEGATE => XAPP_TYPE_OBJECT,
			self::RPC_SERVER => XAPP_TYPE_OBJECT,
			self::PLUGIN_DIRECTORY => XAPP_TYPE_STRING,
			self::PLUGIN_MASK => XAPP_TYPE_STRING,
			self::ALLOW_PLUGINS => array(XAPP_TYPE_BOOL,XAPP_TYPE_INT),
			self::FLAGS => XAPP_TYPE_ARRAY,
			self::PROHIBITED_PLUGINS => XAPP_TYPE_STRING,
			self::AUTH_DELEGATE => XAPP_TYPE_OBJECT,
			self::RPC_TARGET => XAPP_TYPE_STRING,
			self::XFILE_CONF => XAPP_TYPE_ARRAY,
			self::XAPP_CONF => XAPP_TYPE_ARRAY,
			self::LOGGING_CONF => XAPP_TYPE_ARRAY,
			self::STORE_CONF => XAPP_TYPE_ARRAY,
			self::IGNORED_RPC_METHODS => XAPP_TYPE_ARRAY,
			self::GATEWAY_CONF => XAPP_TYPE_ARRAY,
			self::SIGNED_SERVICE_TYPES => XAPP_TYPE_ARRAY,
			self::SIGNING_TOKEN => XAPP_TYPE_STRING,
			self::SIGNING_KEY => XAPP_TYPE_STRING,
			self::LOGGING_FLAGS => XAPP_TYPE_ARRAY,
			self::LOGGER => XAPP_TYPE_OBJECT,
			self::STORE => XAPP_TYPE_OBJECT,
			self::GATEWAY => XAPP_TYPE_OBJECT,
			self::SERIVCE_CONF => XAPP_TYPE_ARRAY,
			self::USER_CONF => array(XAPP_TYPE_ARRAY, XAPP_TYPE_STRING, XAPP_TYPE_OBJECT),
			self::RESOURCE_CONFIG_PREFIX => XAPP_TYPE_STRING,
			self::OVERRIDE => XAPP_TYPE_ARRAY
	);

	/**
	 * options mandatory map for this class contains all mandatory values
	 *
	 * @var array
	 */
	public static $optionsRule = array(
			self::BASEDIR => 1,
			self::SERVER_APPLICATION_CLASS => 0,
			self::APPDIR => 0,
			self::SERVICE => 0,
			self::APP_NAME => 1,
			self::APP_FOLDER => 0,
			self::RELATIVE_VARIABLES => 0,
			self::DOC_ROOT => 0,
			self::RENDER_DELEGATE => 0,
			self::RESOURCE_CONFIG_SUFFIX => 0,
			self::RESOURCE_RENDERER_PREFIX => 0,
			self::RESOURCE_RENDERER_CLZ => 0,
			self::PLUGIN_DIRECTORY => 0,
			self::PLUGIN_MASK => 0,
			self::ALLOW_PLUGINS => 0,
			self::FLAGS => 0,
			self::RPC_SERVER => 0,
			self::PROHIBITED_PLUGINS => 0,
			self::AUTH_DELEGATE => 0,
			self::RPC_TARGET => 0,
			self::IGNORED_RPC_METHODS => 0,
			self::GATEWAY_CONF => 0,
			self::LOGGING_CONF => 0,
			self::SIGNED_SERVICE_TYPES => 0,
			self::SIGNING_TOKEN => 0,
			self::SIGNING_KEY => 0,
			self::XFILE_CONF => 0,
			self::XAPP_CONF => 0,
			self::LOGGING_FLAGS => 0,
			self::LOGGER => 0,
			self::STORE_CONF => 0,
			self::STORE => 0,
			self::GATEWAY => 0,
			self::SERIVCE_CONF => 0,
			self::USER_CONF => 0,
			self::RESOURCE_CONFIG_PREFIX => 0,
			self::OVERRIDE => 0
	);
	/**
	 * contains the singleton instance for this class
	 *
	 * @var null|XApp_Commander_Bootstrap
	 */
	protected static $_instance = null;
	/**
	 * options default value array containing all class option default values
	 *
	 * @var array
	 */
	public $options = array(
			self::BASEDIR => null,
			self::SERVER_APPLICATION_CLASS => null,
			self::APPDIR => null,
			self::SERVICE => null,
			self::APP_NAME => null,
			self::APP_FOLDER => null,
			self::RELATIVE_VARIABLES => null,
			self::DOC_ROOT => null,
			self::RENDER_DELEGATE => null,
			self::RESOURCE_CONFIG_SUFFIX => '',
			self::RESOURCE_RENDERER_PREFIX => 'joomla',
			self::RESOURCE_RENDERER_CLZ => 'XApp_Joomla_Resource_Renderer',
			self::PLUGIN_DIRECTORY => null,
			self::PLUGIN_MASK => null,
			self::ALLOW_PLUGINS => true,
			self::FLAGS => array(),
			self::RPC_SERVER => null,
			self::LOGGER => null,
			self::LOGGING_FLAGS => array(),
			self::PROHIBITED_PLUGINS => '',
			self::AUTH_DELEGATE => null,
			self::RPC_TARGET => null,
			self::IGNORED_RPC_METHODS => null,
			self::GATEWAY_CONF => null,
			self::LOGGING_CONF => null,
			self::SIGNED_SERVICE_TYPES => null, //sign all RPC calls
			self::SIGNING_TOKEN => null,
			self::SIGNING_KEY => null,
			self::XFILE_CONF => null,
			self::XAPP_CONF => null,
			self::STORE_CONF => null,
			self::STORE => null,
			self::GATEWAY => null,
			self::SERIVCE_CONF => null,
			self::USER_CONF => 0,
			self::RESOURCE_CONFIG_PREFIX => '',
			self::OVERRIDE => array()
	);
	/***
	 * @var null|XApp_App_Renderer
	 */
	protected $appRenderer = null;

	protected $resourceRenderer = null;

	/**
	 * class constructor
	 * call parent constructor for class initialization
	 *
	 * @error 14601
	 * @param null|array|object $options expects optional options
	 */
	public function __construct($options = null)
	{
		xapp_set_options($options, $this);
	}


	/**
	 * Main entry used by an index.php file
	 * @param string $dataRoot
	 */
	public function handleRequest($dataRoot='',$userRoot=''){

		if (self::isRPC()) {
			$this->initRpc();
			$this->runRpc();
		} else {
			//Restore user from session before client rendering.
			$um = $this->getUserManager();
			if ($um) {
				$user = $um->getUser();
				if ($user !== null) {
					$um->login($user->getName(), $user->getPassword());
				}else{

				}
			}
			$this->render(true,$dataRoot,$userRoot);
		}
	}

	/**
	 *
	 * @return XIDE_Resource_Renderer
	 */
	public function getResourceRenderer(){
		return $this->resourceRenderer;
	}

	public static function loadRPC()
	{
		if (!class_exists('XApp_Service_Entry_Utils')) {
			require_once(XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php');
		}
		XApp_Service_Entry_Utils::includeXAppRPC();

	}

	public static function loadMin(){

		xapp_import('xapp.Option.Utils');

		if (!class_exists('XApp_Service_Entry_Utils')) {
			require_once(XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php');
		}
		XApp_Service_Entry_Utils::includeXAppCore();
		xapp_setup_language_standalone('en');
		/*require_once(XAPP_BASEDIR . '/connect/utils/Debugging.php');*/
		require_once(XAPP_BASEDIR . '/Log/Exception/Exception.php');
		require_once(XAPP_BASEDIR . '/Log/Interface/Interface.php');
		require_once(XAPP_BASEDIR . '/Log/Log.php');
		require_once(XAPP_BASEDIR . '/Log/Writer.php');
		require_once(XAPP_BASEDIR . '/Log/Writer/File.php');
	}

	/**
	 *
	 */
	private static function loadXFilePluginDependencies(){
		//pull in xapp plugin manager
		include_once(XAPP_BASEDIR . '/commander/PluginManager.php');

		//pull in xapp commander plugin base class
		include_once(XAPP_BASEDIR . '/commander/Plugin.php');
		//pull in xapp commander plugin base class
		include_once(XAPP_BASEDIR . '/commander/defines.php');

		//pull in RPC interface
		if (!class_exists('Xapp_Rpc_Interface_Callable')) {
			//pull in xapp commander plugin base class
			include_once(XAPP_BASEDIR . '/Rpc/Interface/Callable.php');
		}

	}

	public static function isRPC()
	{
		if (!class_exists('XApp_Service_Entry_Utils')) {
			include_once(XAPP_BASEDIR . 'XApp_Service_Entry_Utils.php');
		}
		$_REQUEST_TYPE = XApp_Service_Entry_Utils::getServiceType();
		return
				$_REQUEST_TYPE == XApp_Service_Entry_Utils::SMD_CALL ||
				$_REQUEST_TYPE == XApp_Service_Entry_Utils::SMD_GET ||
				$_REQUEST_TYPE == XApp_Service_Entry_Utils::UPLOAD ||
				$_REQUEST_TYPE == XApp_Service_Entry_Utils::LOGIN ||
				$_REQUEST_TYPE == XApp_Service_Entry_Utils::DOWNLOAD;
	}

	/***
	 * Return current request url
	 * @return string
	 */
	public static function getUrl()
	{
		return XApp_Service_Entry_Utils::getUrl();
	}

	/***
	 *
	 */
	public static function loadDebuggingTools()
	{
		xapp_import('xapp.Utils.Debugging');
	}

	/***
	 * Imports needed for both type of of requests : client & RPC
	 */
	public static function loadCommons()
	{
		self::loadMin();
		xapp_import("xapp.xide.Models.User");
		xapp_import('xapp.xide.Controller.UserManager');
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
	 * @param $serviceName
	 *
	 * @return mixed
	 */
	public function getServiceConfiguration($serviceName){
		$serviceList = xapp_get_option(self::SERIVCE_CONF, $this);
		foreach ($serviceList as &$serviceConf) {
			$className = $serviceConf[ XApp_Service::XAPP_SERVICE_CLASS ];
			if($className===$serviceName){
				return $serviceConf;
			}
		}
		return null;
	}

	/***
	 *
	 */
	public function printPaths()
	{

		echo('XAPP BOOTSTRAP-PATHS' . '<br/>');
		echo('BASE DIR : ' . xapp_get_option(self::BASEDIR, $this) . '<br/>');
		echo('APP DIR : ' . xapp_get_option(self::APPDIR, $this) . '<br/>');
		echo('SERVICE : ' . xapp_get_option(self::SERVICE, $this) . '<br/>');
		echo('APP NAME : ' . xapp_get_option(self::APP_NAME, $this) . '<br/>');
		echo('APP FOLDER : ' . xapp_get_option(self::APP_FOLDER, $this) . '<br/>');
		echo('PLUGIN_DIR: ' . xapp_get_option(self::PLUGIN_DIRECTORY, $this) . '<br/>');
		echo('PLUGIN_MAST: ' . xapp_get_option(self::PLUGIN_MASK, $this) . '<br/>');
		echo('DOC ROOT : ' . xapp_get_option(self::DOC_ROOT, $this) . '<br/><br/>');
	}

	/**
	 * @param $plugins
	 * @param $runtTimeConfiguration
	 * @param $resourceConfig
	 * @return array
	 */
	public function getPluginResources($plugins, $runtTimeConfiguration,$resourceConfig)
	{
		$result = array();
		foreach ($plugins as $plugin) {

			if ($resourceConfig && $plugin->resources && property_exists($plugin->resources,$resourceConfig)) {
				$result[] = $plugin->resources->{$resourceConfig}->items;
			}

			if ($plugin->resources && $plugin->resources->{$runtTimeConfiguration}) {
				$result[] = $plugin->resources->{$runtTimeConfiguration}->items;
			}
		}

		return $result;
	}

	protected static function isWindows()
	{
		$os = PHP_OS;
		switch ($os) {
			case "WINNT": {
				return true;
			}
		}

		return false;
	}

	public function initRpc(){

		$flags = xapp_get_option(self::FLAGS, $this);
		self::$_instance = $this;

		/***
		 * The very basic paths
		 */
		if (!defined('XAPP_BASEDIR')) {
			define("XAPP_BASEDIR", xapp_get_option(self::BASEDIR, $this));
		}
		if (!defined('XAPP_LIB')) {
			define("XAPP_LIB", XAPP_BASEDIR . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR);
		}


		/***
		 * Load utils
		 */
		if (!class_exists('XApp_Service_Entry_Utils')) {
			include_once(XAPP_BASEDIR . 'XApp_Service_Entry_Utils.php');

		}

		/***
		 * Get run-time configuration, there is 'debug' and 'release'. For both cases there are
		 * different resources to load.
		 */
		$XAPP_RUN_TIME_CONFIGURATION = XApp_Service_Entry_Utils::getRunTimeConfiguration();

		/***
		 * Load dependencies
		 */
		$this->loadDependencies(xapp_get_option(self::FLAGS, $this));

		//some debugging tools
		XApp_Service_Entry_Utils::includeXAppDebugTools();


		$plugins = null;
		$pluginResources = null;
		$xComPluginManager = null;


		$logger = null;
		$loggingFlags = xapp_has_option(self::LOGGING_FLAGS, $this) ? xapp_get_option(
				self::LOGGING_FLAGS,
				$this
		) : array();

		/***
		 * Setup Logger
		 */
		if (in_array(XAPP_BOOTSTRAP_SETUP_LOGGER, $flags) && xapp_has_option(self::LOGGING_CONF, $this)) {
			$logger = $this->setupLogger(xapp_get_option(self::LOGGING_CONF));
			if($logger) {
				xapp_set_option(self::LOGGER, $logger, $this);


				if (!function_exists('xp_log')) {
					function xp_log($message)
					{
						$bootstrap = XApp_Bootstrap::instance();
						$log = xapp_get_option(XApp_Bootstrap::LOGGER, $bootstrap);
						$log->log($message);
					}
				}
			}

		} else {

			if (!function_exists('xp_log')) {
				//fake logger
				function xp_log($message)
				{};
			}
		}

		/***
		 * Setup XApp-PHP
		 */
		if (in_array(XAPP_BOOTSTRAP_SETUP_XAPP, $flags)) {
			$this->setupXApp(xapp_has_option(self::XAPP_CONF, $this) ? xapp_get_option(self::XAPP_CONF, $this) : null);
		}

		//Setup RPC Server
		if (in_array(XAPP_BOOTSTRAP_SETUP_RPC, $flags)) {
			$this->setupRPC();
		}

		$storeService = null;

		/***
		 * Setup user settings store
		 */
		if (in_array(XAPP_BOOTSTRAP_SETUP_STORE, $flags) && xapp_has_option(self::STORE_CONF, $this)) {
			$storeService = $this->setupStore(xapp_get_option(self::STORE_CONF, $this));
			xapp_set_option(self::STORE, $storeService, $this);
		}


		/***
		 * Prepare resource renderer
		 */
		if (in_array(XAPP_BOOTSTRAP_LOAD_CLIENT_RESOURCES, $flags)) {

			//clients resource config path
			$XAPP_RESOURCE_CONFIG_PATH = '' . xapp_get_option(self::APPDIR, $this) . DIRECTORY_SEPARATOR;
			if ($XAPP_RUN_TIME_CONFIGURATION === 'debug') {
				$XAPP_RESOURCE_CONFIG_PATH .= 'lib' . DIRECTORY_SEPARATOR . xapp_get_option(
								self::APP_NAME,
								$this
						) . DIRECTORY_SEPARATOR . 'resources-' . $XAPP_RUN_TIME_CONFIGURATION . xapp_get_option(
								self::RESOURCE_CONFIG_SUFFIX,
								$this
						) . '.json';
			} else {
				if ($XAPP_RUN_TIME_CONFIGURATION === 'release') {
					$XAPP_RESOURCE_CONFIG_PATH .= DIRECTORY_SEPARATOR . xapp_get_option(
									self::APP_FOLDER,
									$this
							) . DIRECTORY_SEPARATOR . xapp_get_option(
									self::APP_NAME,
									$this
							) . DIRECTORY_SEPARATOR . 'resources-' . $XAPP_RUN_TIME_CONFIGURATION . xapp_get_option(
									self::RESOURCE_CONFIG_SUFFIX,
									$this
							) . '.json';
				}
			}
		}

		/***
		 * Load plugin resources
		 */
		if (in_array(XAPP_BOOTSTRAP_LOAD_PLUGIN_RESOURCES, $flags) || in_array(
						XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS,
						$flags
				)
		) {

			if (xapp_get_option(self::ALLOW_PLUGINS, $this) && xapp_get_option(
							self::PLUGIN_DIRECTORY,
							$this
					) && xapp_get_option(self::PLUGIN_DIRECTORY, $this)
			) {

				if (!$xComPluginManager) {
					$xComPluginManager = new XApp_Commander_PluginManager();
				}
				$pluginResources = $xComPluginManager->getPluginResources(
						xapp_get_option(self::PLUGIN_DIRECTORY, $this),
						xapp_get_option(self::PLUGIN_DIRECTORY, $this),
						xapp_get_option(self::PLUGIN_MASK, $this)
				);
			}
		}

		/***
		 * Get plugins
		 */
		if (in_array(XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS, $flags)) {
			if (
					xapp_get_option(self::ALLOW_PLUGINS, $this) &&
					xapp_get_option(self::PLUGIN_DIRECTORY, $this) && xapp_get_option(self::PLUGIN_DIRECTORY, $this)
			) {

				if (!$xComPluginManager) {
					$xComPluginManager = new XApp_Commander_PluginManager();
				}


				$plugins = $xComPluginManager->getPlugins(
						xapp_get_option(self::PLUGIN_DIRECTORY, $this),
						xapp_get_option(self::PLUGIN_MASK, $this)
				);
			} else {

			}
		}

		$pluginInstances = array();

		/***
		 * Register server plugins
		 */
		if ($xComPluginManager !== null &&

				count($plugins) && //we have plugin descriptions

				xapp_get_option(self::RPC_SERVER, $this) && //there must be RPC server

				in_array(XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS, $flags)
		) //yes, we want plugins

		{
			switch (XApp_Service_Entry_Utils::getServiceType()) {

				/***
				 * JSON-RPC-2.0 call. In this case we are only loading the plugin which has been specified in the RPC call('service')
				 */
				case XApp_Service_Entry_Utils::SMD_CALL: {

					/***
					 * Load the plugin by its service class name. A RPC POST request looks like this :
					 * {
					 * "id": 0,
					 * "method": "Xapp_FileService.rename",
					 * "params": {
					 * "path": "./tmp/wp-mail.php",
					 * "newFileName": "wp-mail.php2"
					 * },
					 * "jsonrpc": "2.0"
					 * }
					 *
					 * In case we go over JSONP, the url looks like this :
					 *
					 * http://0.0.0.0/zoo254/components/com_xas/xapp/index.php?service=XLESS.test&id=4&callback=as
					 *
					 */
					$method = XApp_Service_Entry_Utils::getSMDMethod();
					$serviceClass = null;
					if ($method != null && strpos($method, '.') != -1) {
						$methodSplitted = explode('.', $method);
						if ($methodSplitted && count($methodSplitted) == 2) {
							$serviceClass = $methodSplitted[0];
						}
					}

					if ($serviceClass) {

						if (in_array(XAPP_LOG_PLUGIN_CREATION, $loggingFlags)) {
							$this->log('Calling service class::' . $serviceClass);
						}

						foreach ($plugins as $pluginConfig) {
							//load only when not prohibited
							$prohibited = explode(',', xapp_get_option(self::PROHIBITED_PLUGINS, $this));
							if (!in_array($serviceClass, $prohibited)) {

								if ($pluginConfig !== null && is_object($pluginConfig)) {

									//pull in if not done yet
									if (!class_exists($pluginConfig->name)) {

										$pluginPath = xapp_get_option(
														self::PLUGIN_DIRECTORY,
														$this
												) . DIRECTORY_SEPARATOR . $pluginConfig->location . DIRECTORY_SEPARATOR . $pluginConfig->name . '.php';
										if (in_array(XAPP_LOG_PLUGIN_CREATION, $loggingFlags)) {
											$this->log('loading plugin ' . $pluginConfig->name . ' at ' . $pluginPath);
										}
										if (file_exists($pluginPath)) {
											include_once $pluginPath;
										} else {
											continue;
										}
									}

									//create instance
									if (class_exists($serviceClass)) {
										$plugin = $xComPluginManager->createPluginInstance(
												$pluginConfig->name, //class name,
												true, //yes, call plugin->load()
												array(), //no service configuration
												array(), //no logging configuration
												xapp_get_options(), //our own configuration,
												$pluginConfig //the plugin info, including all client side resources
										);

										if ($plugin != null) {
											xapp_get_option(self::RPC_SERVER)->register($plugin, array('_load'));
											array_push($pluginInstances, $plugin);

											//share logger
											if (in_array(XAPP_LOG_SHARED_LOGGER_PLUGINS, $loggingFlags) && $logger) {
												$plugin->_setLogger($logger);
											}
										}
									}
								}
							}
						}
					}
					break;
				}

				/***
				 * JSON-RPC-2.0 Service Introspection. That means we expose all plugins as Dojo SMD! You can see the full RPC class by opening http://localhost/joomla251/administrator/index.php?option=com_xappcommander&view=rpc
				 */
				case XApp_Service_Entry_Utils::SMD_GET: {


					foreach ($plugins as $pluginConfig) {
						/***
						 * Skip black listed plugins
						 */
						$prohibited = explode(',', xapp_get_option(self::PROHIBITED_PLUGINS, $this));
						if (in_array($pluginConfig->name, $prohibited)) {
							continue;
						}

						//show only if the plugins wants to be exposed!
						if (property_exists($pluginConfig, 'showSMD') && $pluginConfig->showSMD == true) {

							if (!class_exists($pluginConfig->name)) {

								$pluginPath = xapp_get_option(
												self::PLUGIN_DIRECTORY,
												$this
										) . DIRECTORY_SEPARATOR . $pluginConfig->location . DIRECTORY_SEPARATOR . $pluginConfig->name . '.php';

								if (in_array(XAPP_LOG_PLUGIN_CREATION, $loggingFlags)) {
									$this->log('loading plugin ' . $pluginConfig->name . ' at ' . $pluginPath);
								}
								if (file_exists($pluginPath)) {
									include_once $pluginPath;
								} else {
									continue;
								}
							}

							//now register as RPC class
							if (class_exists($pluginConfig->name)) {
								xapp_get_option(self::RPC_SERVER)->register($pluginConfig->name);
							}

						}

					}
					break;
				}
			}
		}

		$xappFileService = null;
		/***
		 * Setup file service
		 */



		/***
		 * Setup store service
		 */
		if ($storeService) {
			xapp_get_option(self::RPC_SERVER)->register($storeService);
		}

		$serviceConfigurations = xapp_get_option(self::SERIVCE_CONF);
		/***
		 * More services
		 */
		if (in_array(XAPP_BOOTSTRAP_SETUP_SERVICES, $flags) &&

				xapp_has_option(self::SERIVCE_CONF, $this) &&
				xapp_get_option(self::RPC_SERVER, $this)
		) {
			//pull in registry of xapp core framework
			XApp_Service_Entry_Utils::includeXAppRegistry();

			$serviceConfigurations = $this->registerServices($serviceConfigurations, xapp_get_option(self::RPC_SERVER));
			//xapp_set_option(self::SERIVCE_CONF,$services);
			//xapp_clog(xapp_get_option(self::SERIVCE_CONF));
		}

		//Pick directory service instance
		$directoryServiceInstance = null;
		foreach($serviceConfigurations as $service){
			if($service[XApp_Service::XAPP_SERVICE_CLASS]==='XCOM_Directory_Service'){
				$directoryServiceInstance  = $service[XApp_Service::XAPP_SERVICE_INSTANCE];
				break;
			}
		}

		//share directory service instance in plugins
		if($directoryServiceInstance){
			foreach ($pluginInstances as $plugin) {
				$plugin->directoryService = $directoryServiceInstance;
			}
		}

		/***
		 * Setup gateway
		 */
		if (in_array(XAPP_BOOTSTRAP_SETUP_GATEWAY, $flags) &&

				xapp_get_option(self::RPC_SERVER, $this)
		) {

			try {
				$needsSigning = false;
				$opt = xapp_has_option(self::GATEWAY_CONF) ? xapp_get_option(self::GATEWAY_CONF) : array();
				/***
				 * Raise security and demand that the client did sign its request
				 */
				$signServiceTypes = xapp_get_option(self::SIGNED_SERVICE_TYPES, $this);

				if (in_array(XApp_Service_Entry_Utils::getServiceType(), $signServiceTypes)) {

					$needsSigning = true;
					//set signed
					$opt[Xapp_Rpc_Gateway::SIGNED_REQUEST] = true;

					//complete configuration

					if (!array_key_exists(Xapp_Rpc_Gateway::SIGNED_REQUEST_METHOD, $opt)) {
						$opt[Xapp_Rpc_Gateway::SIGNED_REQUEST_METHOD] = 'user';
					}

					if (!array_key_exists(Xapp_Rpc_Gateway::SIGNED_REQUEST_USER_PARAM, $opt)) {
						$opt[Xapp_Rpc_Gateway::SIGNED_REQUEST_USER_PARAM] = 'user';
					}

					if (!array_key_exists(Xapp_Rpc_Gateway::SIGNED_REQUEST_SIGN_PARAM, $opt)) {
						$opt[Xapp_Rpc_Gateway::SIGNED_REQUEST_SIGN_PARAM] = 'sig';
					}
					//complete configuration
				}

				$this->setGatewayOptionArray(Xapp_Rpc_Gateway::ALLOW_IP, $opt);
				$this->setGatewayOptionArray(Xapp_Rpc_Gateway::DENY_IP, $opt);
				$this->setGatewayOptionArray(Xapp_Rpc_Gateway::ALLOW_HOST, $opt);
				$this->setGatewayOptionArray(Xapp_Rpc_Gateway::DENY_HOST, $opt);


				//RPC_GATEWAY_SIGNED_REQUEST_USER_PARAM
				//RPC_GATEWAY_SIGNED_REQUEST_USER_PARAM
				//{RPC_GATEWAY_OMIT_ERROR: true, RPC_GATEWAY_SIGNED_REQUEST: true, RPC_GATEWAY_SIGNED_REQUEST_METHOD: "user", RPC_GATEWAY_SIGNED_REQUEST_USER_PARAM: "user"}

				//xapp_clog($opt);

				/***
				 * Create the gateway
				 */
				$gateway = Xapp_Rpc_Gateway::instance(xapp_get_option(self::RPC_SERVER, $this), $opt);

				$OVERRIDE       = xo_get(self::OVERRIDE);
				xapp_setup_language_standalone();
				if(!$OVERRIDE){
					$OVERRIDE = array(
							'SALT'=>'k?Ur$0aE#9j1+7ui'
					);
				}

				$SALT = $OVERRIDE['SALT'];
				//$SALT           = xo_get(Xapp_Rpc_Gateway::SALT_KEY,$opt);


				$signToken      = md5($SALT);

				//Xapp_Rpc_Gateway::setSalt($OVERRIDE['SALT']);

				xo_set(self::SIGNING_TOKEN,$signToken);
				$userManger = $this->getUserManager();
				if($userManger){
					$user = $userManger->getUser();
					if($user) {
						xo_set(self::SIGNING_KEY,md5($user->getName()));
					}

				}
				/***
				 * Set the API key for signed requests
				 */
				if ($needsSigning) {
					$gateway->addKey(
							xapp_get_option(self::SIGNING_KEY, $this),
							xapp_get_option(self::SIGNING_TOKEN, $this)
					);
				}
				xapp_set_option(self::GATEWAY, $gateway, $this);
			} catch (Exception $e) {
				Xapp_Rpc_Server_Json::dump($e);
			}
		}
		return $this;
	}

	/***
	 * Pulls in xapp-php dependencies
	 * @param $flags
	 */
	private function loadDependencies($flags){

		/***
		 * The very basic paths
		 */
		if (!defined('XAPP_BASEDIR')) {
			define("XAPP_BASEDIR", xapp_get_option(self::BASEDIR, $this));
		}
		if (!defined('XAPP_LIB')) {
			define("XAPP_LIB", XAPP_BASEDIR . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR);
		}


		/***
		 * Load utils
		 */
		if (!class_exists('XApp_Service_Entry_Utils')) {
			include_once(XAPP_BASEDIR . 'XApp_Service_Entry_Utils.php');
		}

		/***
		 * Now include all xapp stuff
		 */

		//pull in parts of xapp core framework
		XApp_Service_Entry_Utils::includeXAppCore();

		/***
		 * Load JSON deps
		 */
		if (in_array(XAPP_BOOTSTRAP_LOAD_CLIENT_RESOURCES, $flags) ||
				in_array(XAPP_BOOTSTRAP_SETUP_SERVICES, $flags) ||
				in_array(XAPP_BOOTSTRAP_LOAD_PLUGIN_RESOURCES, $flags)
		) {

			//pull in parts of xapp json framework
			XApp_Service_Entry_Utils::includeXAppJSONStoreClasses();

			//pull in json utils (to read client app's resource configuration
			XApp_Service_Entry_Utils::includeXAppJSONTools();
		}


		/***
		 * Load resource renderer dependencies
		 */
		if (in_array(XAPP_BOOTSTRAP_CREATE_RESOURCE_RENDERER, $flags)) {

			//pull in registry of xapp core framework
			XApp_Service_Entry_Utils::includeXAppRegistry();

			//pull in legacy client app renderer
			include_once(XAPP_BASEDIR . '/app/Renderer.php');

			//pull in xapp commander renderer
			/*include_once(XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'app'. DIRECTORY_SEPARATOR . 'Commander.php');*/


			//pull in xapp resource renderer
			include_once(XAPP_BASEDIR . '/Resource/Renderer.php');

			//pull in cms related resource renderer
			include_once(XAPP_LIB . DIRECTORY_SEPARATOR . xapp_get_option(
							self::RESOURCE_RENDERER_PREFIX,
							$this
					) . DIRECTORY_SEPARATOR . 'ResourceRenderer.php');
		}


		/***
		 * Load plugin manager
		 */
		if (in_array(XAPP_BOOTSTRAP_LOAD_PLUGIN_RESOURCES, $flags) || in_array(
						XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS,
						$flags
				)
		) {

			//pull in xapp plugin manager
			include_once(XAPP_BASEDIR . '/commander/PluginManager.php');

			//pull in xapp commander plugin base class
			include_once(XAPP_BASEDIR . '/commander/Plugin.php');
		}

		/***
		 * Load logging deps
		 */
		if (in_array(XAPP_BOOTSTRAP_SETUP_LOGGER, $flags)) {

			if (!class_exists('Xapp_Log_Exception')) {
				require_once(XAPP_BASEDIR . '/Log/Exception/Exception.php');
			}
			if (!class_exists('Xapp_Log_Interface')) {
				require_once(XAPP_BASEDIR . '/Log/Interface/Interface.php');
			}
			if (!class_exists('Xapp_Log')) {
				require_once(XAPP_BASEDIR . '/Log/Log.php');
			}
			if (!class_exists('Xapp_Log_Error')) {
				require_once(XAPP_BASEDIR . '/Log/Error.php');
			}

			if (!class_exists('Xapp_Log_Writer')) {
				require_once(XAPP_BASEDIR . '/Log/Writer.php');
			}
			if (!class_exists('File.php')) {
				require_once(XAPP_BASEDIR . '/Log/Writer/File.php');
			}
		}

		/***
		 * Load store dependencies
		 */
		if (in_array(XAPP_BOOTSTRAP_SETUP_STORE, $flags) && xapp_has_option(self::STORE_CONF, $this)) {
			require_once(XAPP_BASEDIR . '/Store/Store.php');
		}
	}

	/***
	 * Setup logger
	 */
	private function setupLogger($loggingConf){

		$path = realpath(Xapp_Log::PATH);
		if($path && is_writable($path)) {
			$logginConf[Xapp_Log::WRITER] = array(
					new Xapp_Log_Writer_File(
							xapp_get_option(Xapp_Log::PATH, $loggingConf)
					)
			);
			return new Xapp_Log_Error($loggingConf);
		}
		return null;
	}

	/**
	 * Xapp_Singleton interface impl.
	 *
	 * static singleton method to create static instance of driver with optional third parameter
	 * xapp options array or object
	 *
	 * @error 15501
	 * @param null|mixed $options expects optional xapp option array or object
	 * @return XApp_Commander_Bootstrap
	 */
	public static function instance($options = null)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($options);
		}

		return self::$_instance;
	}

	/***
	 * run xapp to init your application and make use of all in build features such as
	 * debuging, autoloading, error logging. xapp can only be initialized with this method
	 * expecting the optional xapp conf array which can also be set outside of xapp with
	 * the generic function xapp_conf.
	 */
	private function setupXApp($conf)
	{

		if (!defined(XAPP_PATH_BASE)) {
			define(XAPP_PATH_BASE, XAPP_BASEDIR);
		}

		if ($conf == null) {
			$conf = array
			(
					XAPP_CONF_DEBUG_MODE => null,
					XAPP_CONF_AUTOLOAD => false,
					XAPP_CONF_DEV_MODE => XApp_Service_Entry_Utils::isDebug(),
					XAPP_CONF_HANDLE_BUFFER => true,
					XAPP_CONF_HANDLE_SHUTDOWN => false,
					XAPP_CONF_HTTP_GZIP => true,
					XAPP_CONF_CONSOLE => false,
					XAPP_CONF_HANDLE_ERROR => true,
					XAPP_CONF_HANDLE_EXCEPTION => true,
			);


		}
		Xapp::run($conf);
		xapp_import('xapp.Rpc.*');
		xapp_import('xapp.Log.*');
	}

	/***
	 * Setup a JSON-RPC server, creates a JSON or JSONP server
	 */
	private function setupRPC(){

		/***
		 * We support JSONP for all services
		 */
		$isJSONP = false;
		$hasJSONP = true;
		if ($hasJSONP) {
			$isJSONP = XApp_Service_Entry_Utils::isJSONP();
		}
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method === 'POST') {
			$hasJSONP = false;
		}

		/***
		 * Filtered methods
		 */
		$ignoredRPCMethods = array(
				'load',
				'getObject',
				'init',
				'setup',
				'log',
				'onBeforeCall',
				'onAfterCall',
				'dumpObject',
				'applyFilter',
				'getLastJSONError',
				'cleanUrl',
				'rootUrl',
				'siteUrl',
				'getXCOption',
				'getIndexer',
				'getIndexOptions',
				'getIndexOptions',
				'indexDocument',
				'onBeforeSearch',
				'toDSURL',
				'searchTest'
		);

		$ignoredMethodsOption = xo_get(self::IGNORED_RPC_METHODS);

		if (xapp_get_option(self::IGNORED_RPC_METHODS, $this)) {

			$ignoredRPCMethods = array_merge(xapp_get_option(self::IGNORED_RPC_METHODS, $this), $ignoredRPCMethods);
		} elseif (xapp_has_option(self::AUTH_DELEGATE, $this)) {

			/***
			 * Additional security here, mark each service method which has not been authorized by the
			 * auth delegate as ignored!
			 */
			$authDelegate = xapp_get_option(self::AUTH_DELEGATE, $this);

			if (method_exists($authDelegate, 'authorize')) {
				$xCommanderFunctionTable = XApp_Service_Entry_Utils::getXCommanderFuncTable();
				foreach ($xCommanderFunctionTable as $key => $value) {
					if (!$authDelegate::authorize($value)) {
						array_push($ignoredRPCMethods, $value);
					}
				}
			}
		}

		$server = null;
		if ($hasJSONP && $isJSONP) {
			//Options for SMD based JSONP-RPC classes
			$opt = array
			(
					Xapp_Rpc_Smd::IGNORE_METHODS => $ignoredRPCMethods,
					Xapp_Rpc_Smd::IGNORE_PREFIXES => array('_', '__')
			);
			$smd = new Xapp_Rpc_Smd_Jsonp($opt);

			//Options for RPC server
			$opt = array
			(
					Xapp_Rpc_Server::ALLOW_FUNCTIONS => true,
					Xapp_Rpc_Server::APPLICATION_ERROR => false,
					Xapp_Rpc_Server::METHOD_AS_SERVICE => true,
					Xapp_Rpc_Server::DEBUG => XApp_Service_Entry_Utils::isDebug(),
					Xapp_Rpc_Server::SMD => $smd
			);
			$server = Xapp_Rpc::server(XApp_Service_Entry_Utils::isRaw() ? 'raw' : 'jsonp', $opt);

		} else {

			//Options for SMD based RPC classes
			$opt = array
			(
					Xapp_Rpc_Smd_Json::IGNORE_METHODS => $ignoredRPCMethods,
					Xapp_Rpc_Smd_Json::IGNORE_PREFIXES => array('_', '__'),
					Xapp_Rpc_Smd_Json::METHOD_TARGET => false,
					Xapp_Rpc_Smd_Json::SERVICE_OVER_GET => true,
					Xapp_Rpc_Smd_Json::TARGET => xapp_get_option(self::RPC_TARGET, $this)
			);
			$smd = new Xapp_Rpc_Smd_Json($opt);


			//Options for RPC server
			$opt = array
			(
					Xapp_Rpc_Server::ALLOW_FUNCTIONS => true,
					Xapp_Rpc_Server::APPLICATION_ERROR => false,
					Xapp_Rpc_Server::METHOD_AS_SERVICE => false,
					Xapp_Rpc_Server::ALLOW_BATCHED_REQUESTS =>true,
					Xapp_Rpc_Server::SERVICE_OVER_GET => true,
					Xapp_Rpc_Server::DEBUG => XApp_Service_Entry_Utils::isDebug(),
					Xapp_Rpc_Server::VALIDATE => !XApp_Service_Entry_Utils::isUpload(),
					Xapp_Rpc_Server::SMD => $smd
			);
			$server = Xapp_Rpc::server('json', $opt);
		}

		if ($server) {
			xapp_set_option(self::RPC_SERVER, $server, $this);
		}
	}


	/**
	 * @param $storeConf
	 * @return XApp_Store
	 */
	public function setupStore($storeConf)
	{
		return new XApp_Store($storeConf);
	}

	/**
	 * @param $message
	 * @param string $prefix
	 * @param bool $stdError
	 * @return null
	 */
	public function log($message, $prefix = '', $stdError = true){
		if (function_exists('xp_log')) {
			xp_log('XCom-Bootstrap : ' . $message);
		}
		if ($stdError) {
			error_log('XCom-Bootstrap : ' . $message);
		}
		return null;
	}

	/**
	 * @param $serviceList
	 * @param $rpcServer
	 * @param null $logger
	 * @return mixed
	 */
	public function registerServices($serviceList, $rpcServer, $logger = null)
	{

		$logger = $logger ?: xapp_get_option(XApp_Bootstrap::LOGGER, $this);

		$shareLogger = in_array(XAPP_LOG_SHARED_LOGGER_SERVICES, xo_get(self::LOGGING_FLAGS));

		foreach ($serviceList as &$serviceConf) {

			$instance = $serviceConf[XApp_Service::XAPP_SERVICE_INSTANCE];
			$className = $serviceConf[XApp_Service::XAPP_SERVICE_CLASS];
			$classConf = $serviceConf[XApp_Service::XAPP_SERVICE_CONF];
			//no instance yet, create one
			if ($instance == null) {

				if (!class_exists($className)) {
					$this->log('service class : ' . $className . ' doesnt exists');
					continue;
				}

				if (array_key_exists(XApp_Service::PUBLISH_METHODS, $classConf)) {
					//determine we have 'publish methods'
					$publishedMethods = $classConf[XApp_Service::PUBLISH_METHODS];
					//if we have published methods, we need to use the Class mixer to auto-wire
					//the service and the managed class all together
					if (count($publishedMethods)) {
						xapp_import('xapp.Commons.ClassMixer');
						xapp_import('xapp.Commons.Mixins');
					}
				}

				//mixin logger instance
				if ($shareLogger === true && $logger !== null) {
					$serviceConf[XApp_Service::LOGGER] = $logger;
				}

				$instance = new $className($serviceConf[XApp_Service::XAPP_SERVICE_CONF]);
				$serviceConf[XApp_Service::XAPP_SERVICE_INSTANCE] = $instance;
			}

			xapp_set_option(XApp_Service::BOOTSTRAP,$this,$instance);

			//share logger;
			if ($shareLogger === true && $logger !== null) {
				$instance->logger = $logger;

				if (method_exists($instance, 'getObject')) {

					$serviceObject = $instance->getObject();
					if ($serviceObject) {
						$serviceObject->logger = $logger;
					}
				}
			}
			$rpcServer->register($instance);
		}

		xapp_set_option(self::SERIVCE_CONF,$serviceList);

		return $serviceList;
	}

	/**
	 * @param $key
	 * @param $opt
	 */
	protected function setGatewayOptionArray($key, &$opt)
	{

		if (xapp_has_option($key, $opt) && is_array(xapp_get_option($key, $opt))) {

		} else {
			unset($opt[$key]);
		}
	}

	public function runRpc(){
		/***
		 * Setup gateway
		 */
		if (xapp_get_option(self::GATEWAY, $this)) {
			xapp_get_option(self::GATEWAY, $this)->run();
		}
	}

	public function testFlag($what,$where){
		return in_array($what, $where) && !in_array(-$what, $where);
	}

	protected $userManager = null;

	/**
	 * @return null|XApp_UserManager
	 */
	public function getUserManager()
	{
		if($this->userManager){
			return $this->userManager;
		}

		$flags = xapp_get_option(self::FLAGS);

		if ($this->testFlag(XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION,$flags) && xo_get(self::USER_CONF))
		{

			self::loadJSONTools();
			self::loadXAppJSONStoreClasses();
			xapp_import('xapp.Store.Json.Json');
			xapp_import('xapp.Service.Utils');
			$userMgr = new XApp_UserManager(
					Array(
							XApp_UserManager::STORE_CONF => array(
									XApp_Store_JSON::CONF_FILE => xapp_get_option(self::USER_CONF, $this)
							)
					)
			);
			$userMgr->init();
			$userMgr->initSessionStorage(null);
			$this->userManager = $userMgr;

			return $userMgr;
		}

		return null;
	}
	////////////////////////////////////////////////////////////////////////////
	//
	//  Auth helpers
	//
	/////////////////////////////////////////////////////////////////////////////
	public static function loadJSONTools()
	{
		if (!class_exists('XApp_Utils_JSONUtils')) {
			xapp_import('xapp.Utils.JSONUtils');
		}
	}

	/***
	 * Include XApp-JSON-Store Files
	 */
	public static function loadXAppJSONStoreClasses()
	{

		if (!class_exists('Xapp_Util_JsonStorage')) {

			/***
			 * Import JSON-Store classes from 'xapp/Util'
			 */
			xapp_import('xapp.Util.Storage');

			if (!class_exists('Xapp_Util_Std')) {
				xapp_import('xapp.Util.Std.Std');
				xapp_import('xapp.Util.Std.Query');
				xapp_import('xapp.Util.Std.Store');
				xapp_import('xapp.Util.Json.Json');
				xapp_import('xapp.Util.Json.Query');
				xapp_import('xapp.Util.Json.Store');
			}
		}
	}


	/**
	 * Logs user into session
	 * @param $user
	 * @param $pw
	 * @return bool
	 */
	public function login($user, $pw)
	{
		$flags = xapp_get_option(self::FLAGS);
		if ($this->testFlag(XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION,$flags) && xo_get(self::USER_CONF)){

			$userMgr = $this->getUserManager();
			$errors = array();
			$result = $userMgr->login($user, $pw, $errors);
			return $result;
		}
		return true;
	}



	////////////////////////////////////////////////////////////////////////////
	//
	//  Dojo helpers
	//
	/////////////////////////////////////////////////////////////////////////////

	public function isLoggedIn()
	{
		$flags = xapp_get_option(self::FLAGS);



		if ($this->testFlag(XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION,$flags) &&  xapp_get_option(self::USER_CONF, $this)){

			$userMgr = $this->getUserManager();

			/*
			self::loadJSONTools();
			self::loadXAppJSONStoreClasses();
			xapp_import('xapp.Store.Json.Json');
			xapp_import('xapp.Service.Utils');

			$userMgr = new XApp_UserManager(
				Array(
					XApp_UserManager::STORE_CONF => array(
						XApp_Store_JSON::CONF_FILE => xapp_get_option(self::USER_CONF, $this)
					)
				)
			);

			$userMgr->init();
			$userMgr->initSessionStorage(XApp_Service_Utils::getUrl());
			*/
			$result = $userMgr->isLoggedIn();
			return $result;
		}
		return true;
	}

	public function createDojoPackageString($prefix, $packages, $debug)
	{
		$dojoPackagesStr = '[';
		$pIdx = 0;
		foreach ($packages as $package) {

			if ($pIdx > 0) {
				$dojoPackagesStr .= ",";
			}
			if($package) {
				$dojoPackagesStr .= "{name:" . "'" . $package['name'] . "',";
				$dojoPackagesStr .= "location:" . "'" . $prefix . $package['location'] . "'}";
			}
			$pIdx++;

		}
		$dojoPackagesStr .= ']';

		return $dojoPackagesStr;
	}

	public function getDojoPackages($prefix, $plugins, $debug)
	{
		$XAPP_DOJO_PACKAGES = null;

		if ($plugins && count($plugins)) {

			if ($debug) {

				$dojoPackagesStr = '[';
				$pIdx = 0;
				foreach ($plugins as $plugin) {
					if (!is_object($plugin)) {
						continue;
					}
					if ($pIdx > 0) {
						$dojoPackagesStr .= ",";
					}
					$dojoPackagesStr .= "{name:" . "'" . $plugin->name . "',";
					$dojoPackagesStr .= "location:" . "'" . $prefix . $plugin->name . '/client/' . "'}";
					if ($pIdx < count($plugins) - 1) {
						$dojoPackagesStr .= ',';
					}
				}
				$dojoPackagesStr .= ']';
				$XAPP_DOJO_PACKAGES = $dojoPackagesStr;

			} else {
				$dojoPackages = array();
				array_push($dojoPackages, array('name' => 'dojo', 'location' => 'dojo'));
				array_push($dojoPackages, array('name' => 'dojox', 'location' => 'dojox'));
				array_push($dojoPackages, array('name' => 'dijit', 'location' => 'dijit'));
				array_push($dojoPackages, array('name' => 'orion', 'location' => 'orion-release/orion'));
				array_push($dojoPackages, array('name' => 'cbtree', 'location' => 'cbtree'));
				array_push($dojoPackages, array('name' => 'xfile', 'location' => 'xfile'));
				array_push($dojoPackages, array('name' => 'xide', 'location' => 'xide'));
				array_push($dojoPackages, array('name' => 'xwordpress', 'location' => 'xwordpress'));
				array_push($dojoPackages, array('name' => 'xbox', 'location' => 'xbox'));
				array_push($dojoPackages, array('name' => 'xjoomla', 'location' => 'xjoomla'));
				foreach ($plugins as $plugin) {
					if (is_object($plugin)) {
						array_push(
								$dojoPackages,
								array('name' => $plugin->name, 'location' => $prefix . $plugin->name . '/client/')
						);
					}
				}
				$XAPP_DOJO_PACKAGES = json_encode($dojoPackages);

			}


			/****
			 * Render plugin resources
			 */
			$javaScriptHeaderStr = '';
			$javaScriptHeaderStr .= 'var xappPluginResources=';
			$javaScriptHeaderStr .= json_encode($plugins) . ';';
			$javaScriptHeaderStr .= '';

			return array(
					'plugins' => $javaScriptHeaderStr,
					'packages' => $XAPP_DOJO_PACKAGES
			);
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
		return preg_replace( '/[^A-Za-z0-9_\-]/', '', $key );
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


	/**
	 * @return string
	 */
	public static function getRpcViewUrl(){
		xapp_import('xapp.Utils.Strings');

		$index = XApp_Service_Utils::getIndex();

		$urlParams = array();

		if (isset($_SERVER["QUERY_STRING"])) {
			XApp_Utils_Strings::parse_str($_SERVER["QUERY_STRING"], $urlParams);
			if (isset($urlParams['view'])) {
				unset($urlParams['view']);
			}
		}
		$extraParams = count($urlParams) ? '&' . http_build_query($urlParams) : '';
		xapp_import('xapp.Http.Url');
		$url  = new XApp_Http_Url(self::getUrl());
		return dirname($url->getBaseUrl() .'/fo.php')  . '/' . $index . '?view=rpc' . $extraParams;
	}

	/**
	 * @return string
	 */
	public static function getRpcCallUrl(){
		xapp_import('xapp.Utils.Strings');
		xapp_import('xapp.Service.Utils');
		$index = XApp_Service_Utils::getIndex();
		$urlParams = array();

		if (isset($_SERVER["QUERY_STRING"])) {
			XApp_Utils_Strings::parse_str($_SERVER["QUERY_STRING"], $urlParams);
			if (isset($urlParams['view'])) {
				unset($urlParams['view']);
			}
		}
		$extraParams = count($urlParams) ? '&' . http_build_query($urlParams) : '';

		xapp_import('xapp.Http.Url');
		$url  = new XApp_Http_Url(self::getUrl());
		//$res = dirname(XApp_Service_Entry_Utils::getUrl()) . '/' . $index . '?view=smdCall' . $extraParams;
		$res = dirname($url->getBaseUrl() .'/fo.php') . '/' . $index . '?view=smdCall' . $extraParams;

		return $res;
	}

}
