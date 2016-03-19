<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * @param $dict
 * @param $opt
 * @param $override
 * @param $key
 */
function mergeOverrideToConfig($dict,&$opt,$override,$_key){

	if(isset($override[$_key])){

		$_src = &$override[$_key];
		$_dst = &$opt[$_key];

		foreach ($dict as $key => $val) {

			if(is_string($key)){

				if(is_object($_src)) {

					$v = $_src->get($key);//warm up

					if ($_src->has($key) || ($_src->get($key) === false)) {
						$_dst[$key] = $_src->get($key);
					}

				}else if (is_array($_src)){
					if(isset($_src[$key])){
						$_dst[$key] = $_src[$key];
					}
				}
			}
		}

		if($_dst){
			$opt[$_key] = $_dst;
		}
	}
	return $opt;
}
/***
 * @param $XAPP_BASE_DIRECTORY
 * @param $XAPP_APP_NAME
 * @param $XAPP_CLIENT_DIRECTORY
 * @param $REPOSITORY_ROOT
 * @param $REPOSITORY_START_PATH
 * @param $UPLOAD_EXTENSIONS
 * @param $XFILE_CONFIG
 * @param string $XAPP_JQUERY_THEME
 * @param $XAPP_SITE_URL
 * @param $XAPP_PLUGIN_URL
 * @param $FILE_STORE_URL
 * @param $XAPP_AUTH_DELEGATE
 * @param $XAPP_AUTH_PREFIX
 * @param $XAPP_AUTH_SUFFIX
 * @param $XAPP_SERVICE_DIRECTORY
 * @param $XAPP_LOG_DIR
 * @param $PROHIBITED_PLUGINS
 * @param $RESOURCE_PREFIX
 * @param $RESOURCE_RENDERER
 * @param $RESOURCE_CONFIG_PREFIX
 * @param $RENDER_DELEGATE
 *
 * @param $ALLOW_IP
 * @param $DENY_IP
 * @param $ALLOW_HOST
 * @param $DENY_HOST
 *
 * @param $RPC_TARGET
 * @param $RPC_URL
 *
 * @param $RELATIVE_VARIABLES
 * @return array
 */

function xapp_commander_render_app(
	$XAPP_BASE_DIRECTORY,
	$XAPP_APP_NAME,
	$XAPP_CLIENT_DIRECTORY,
	$REPOSITORY_ROOT,
	$REPOSITORY_START_PATH,
	$UPLOAD_EXTENSIONS,
	$XFILE_CONFIG,
	$XAPP_JQUERY_THEME = 'transparent',
	$XAPP_SITE_URL,
	$XAPP_PLUGIN_URL,
	$FILE_STORE_URL,
	$XAPP_AUTH_DELEGATE,
	$XAPP_AUTH_PREFIX,
	$XAPP_AUTH_SUFFIX,
	$XAPP_LOG_DIR,
	$PROHIBITED_PLUGINS,
	$RESOURCE_PREFIX,
	$RESOURCE_RENDERER,
	$RESOURCE_CONFIG_PREFIX,
	$RENDER_DELEGATE,
	$ALLOW_IP,
	$DENY_IP,
	$ALLOW_HOST,
	$DENY_HOST,
	$RPC_TARGET,
	$RPC_URL,
	$STORE_CLASS,
	$STORE_FILE,
	$XAPP_SALT_KEY,
	$RELATIVE_VARIABLES,
	$_DEBUG,
	$XAPP_COMPONENTS,
	$XAPP_RESOURCE_CONFIG,
	$XAPP_BOOTSTRAP_OVERRIDE
) {

	$_REQUEST_TYPE = XApp_Service_Entry_Utils::getServiceType();

	$XAPP_USER_CONFIG_PATH = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Users.php';

	$_IS_RPC = $_REQUEST_TYPE == XApp_Service_Entry_Utils::LOGIN ||
		$_REQUEST_TYPE == XApp_Service_Entry_Utils::SMD_CALL ||
		$_REQUEST_TYPE == XApp_Service_Entry_Utils::SMD_GET ||
		$_REQUEST_TYPE == XApp_Service_Entry_Utils::UPLOAD ||
		$_REQUEST_TYPE == XApp_Service_Entry_Utils::DOWNLOAD;

	if ($_IS_RPC) {

		/***
		 * Ajax - calls go through here too! In case its RPC, we do here the 1st security pass, further checks are done in XFile.php in conjunction with the Joomla-Component-Parameters
		 */
		switch (XApp_Service_Entry_Utils::getServiceType()) {
			/***
			 * JSON-RPC-2.0 call
			 */
			case XApp_Service_Entry_Utils::SMD_CALL: {
				$operation = XApp_Service_Entry_Utils::getXCommanderOperation();
				$authorized = $XAPP_AUTH_DELEGATE::authorize($XAPP_AUTH_PREFIX . $operation, $XAPP_AUTH_SUFFIX);
				if (!$authorized) {
					die (XApp_Service_Entry_Utils::toRPCErrorStd(1, XAPP_TEXT('AUTHORIZATION_ERROR')));
				}
				break;
			}

			/***
			 * JSON-RPC-2.0 Service Introspection. You can see the full RPC class by opening http://localhost/joomla251/administrator/index.php?option=com_xappcommander&view=rpc
			 */
			case XApp_Service_Entry_Utils::SMD_GET: {
				break;
			}
			/***
			 * Upload request. This is only a general check. More specific checks are done in the XFile RPC service class.
			 */
			case XApp_Service_Entry_Utils::UPLOAD: {
				$authorized = $XAPP_AUTH_DELEGATE::authorize(
					$XAPP_AUTH_PREFIX . XC_OPERATION_UPLOAD_STR,
					$XAPP_AUTH_SUFFIX
				);
				if (!$authorized) {
					die (XApp_Service_Entry_Utils::toRPCError(1, XAPP_TEXT('AUTHORIZATION_ERROR')));
				}
				break;
			}
			/***
			 * Download request. This is only a general check. More specific checks are done in the XFile RPC service class. The $UPLOAD_EXTENSIONS must be set here.
			 */
			case XApp_Service_Entry_Utils::DOWNLOAD: {
				$authorized = $XAPP_AUTH_DELEGATE::authorize(
					$XAPP_AUTH_PREFIX . XC_OPERATION_DOWNLOAD_STR,
					$XAPP_AUTH_SUFFIX
				);
				if (!$authorized) {
					die (XAPP_TEXT('AUTHORIZATION_ERROR'));
				}
				break;
			}
		}


		/***
		 * Now authorize RPC router, further security checks are done in xapp/RPC/Gateway and other files, requiring signed client requests and all
		 * client actions need to be enabled by the component's ACL settings.
		 */
		define('_XAPP_AUTH_DONE_', true);

		/***
		 * 2nd pass and final RPC rendering
		 */
		switch (XApp_Service_Entry_Utils::getServiceType()) {

			case XApp_Service_Entry_Utils::LOGIN:
			case XApp_Service_Entry_Utils::SMD_CALL:
			case XApp_Service_Entry_Utils::SMD_GET:
			case XApp_Service_Entry_Utils::UPLOAD:
			case XApp_Service_Entry_Utils::DOWNLOAD://RPC call
			{
				xapp_import('xapp.Service.Service');
				xapp_import('xapp.commander.Directory.Service');
				xapp_import('xapp.VFS.Base');
				xapp_import('xapp.VFS.Local');
				xapp_import('xapp.Option.Utils');

				xapp_import('xapp.Directory.Utils');

				xapp_import("xapp.xide.Models.User");
				xapp_import('xapp.xide.Controller.UserManager');
				xapp_import('xapp.xide.Controller.UserService');


				xapp_import('xapp.Store.Json.Json');
				xapp_import('xapp.Resource.Service');
				xapp_import('xapp.Resource.ResourceManager');

				xapp_import('xapp.xide.Logging.Service');
				xapp_import('xapp.xide.Logging.LogManager');


				$XAPP_VFS_CONFIG_PATH = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vfs.php';
				if (!file_exists($XAPP_VFS_CONFIG_PATH)) {
					die('Have no vfs config at ' . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vfs.php');
				}

				$XAPP_USER_CONFIG_PATH = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Users.php';

				$XIDE_LOG_PATH = realpath(XAPP_BASEDIR . '..' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'all.log');
				if (!$XIDE_LOG_PATH) {
					$XIDE_LOG_PATH = '';
				}


				$REPOSITORY_ROOT = str_replace('administrator', '', $REPOSITORY_ROOT);//no idea why
				if ($REPOSITORY_START_PATH != null) {
					$REPOSITORY_ROOT .= DIRECTORY_SEPARATOR . $REPOSITORY_START_PATH . DIRECTORY_SEPARATOR;//
				}

				if ($UPLOAD_EXTENSIONS == null) {
					$UPLOAD_EXTENSIONS = 'bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls';
				}

				$authDelegate = new $XAPP_AUTH_DELEGATE();
				//$SYSTEM_ROOT = realpath('/PMaster/') . DIRECTORY_SEPARATOR;

				Xapp_Rpc_Gateway::setSalt($XAPP_SALT_KEY);

				$isDirectoryService = strpos(
						XApp_Service_Entry_Utils::getSMDMethod(),
						'Directory_Service.ls'
					) !== false;
				$bootStrapFlags = null;

				if ($isDirectoryService) {

					$bootStrapFlags = array(
						XAPP_BOOTSTRAP_SETUP_XAPP,                  //takes care about output encoding and compressing
						XAPP_BOOTSTRAP_SETUP_RPC,                   //setup a RPC server
						XAPP_BOOTSTRAP_SETUP_STORE,                 //setup a settings store
						XAPP_BOOTSTRAP_SETUP_GATEWAY,               //setup a firewall
						XAPP_BOOTSTRAP_SETUP_SERVICES,              //setup a services
						XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION        //needs auth
					);
				} else {
					$bootStrapFlags = array(
						//ignored when XApp_Commander_Bootstrap::ALLOW_PLUGINS is off
						XAPP_BOOTSTRAP_LOAD_PLUGIN_RESOURCES,
						//ignored when XApp_Commander_Bootstrap::ALLOW_PLUGINS is off
						XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS,
						//takes care about output encoding and compressing
						XAPP_BOOTSTRAP_SETUP_XAPP,
						//setup a RPC server
						XAPP_BOOTSTRAP_SETUP_RPC,
						//setup a settings store
						XAPP_BOOTSTRAP_SETUP_STORE,
						//setup a firewall
						XAPP_BOOTSTRAP_SETUP_GATEWAY,
						//setup a services
						XAPP_BOOTSTRAP_SETUP_SERVICES,
						//needs auth
						XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION
					);
				}


				$services = null;

				if ($isDirectoryService) {


					$services = array(
						XApp_Service::factory(
							'XCOM_Directory_Service',
							array(

								XApp_Directory_Service::REPOSITORY_ROOT => $REPOSITORY_ROOT . DIRECTORY_SEPARATOR,
								XApp_Directory_Service::FILE_SYSTEM => 'XApp_VFS_Local',
								XApp_Directory_Service::VFS_CONFIG_PATH => $XAPP_VFS_CONFIG_PATH,
								XApp_Directory_Service::FILE_SYSTEM_CONF => array(
									XApp_VFS_Base::ABSOLUTE_VARIABLES => array(
										'root' => $REPOSITORY_ROOT . DIRECTORY_SEPARATOR
									),
									XApp_VFS_Base::RELATIVE_VARIABLES => array(),
								),
								XApp_Directory_Service::UPLOAD_EXTENSIONS => $UPLOAD_EXTENSIONS
							)
						)

					);
				} else {

					$services = array(

						XApp_Service::factory(
							'XCOM_Directory_Service',
							array(

								XApp_Directory_Service::REPOSITORY_ROOT => $REPOSITORY_ROOT . DIRECTORY_SEPARATOR,
								XApp_Directory_Service::FILE_SYSTEM => 'XApp_VFS_Local',
								XApp_Directory_Service::VFS_CONFIG_PATH => $XAPP_VFS_CONFIG_PATH,
								XApp_Directory_Service::FILE_SYSTEM_CONF => array(

									XApp_VFS_Base::ABSOLUTE_VARIABLES => array(
										'root' => $REPOSITORY_ROOT . DIRECTORY_SEPARATOR
									),
									XApp_VFS_Base::RELATIVE_VARIABLES => array(),
								),
								XApp_Directory_Service::UPLOAD_EXTENSIONS => $UPLOAD_EXTENSIONS
							)
						),
						XApp_Service::factoryEx(
							'XApp_Resource_Service',
							array(
								XApp_Service::MANAGED_CLASS => 'XApp_ResourceManager',//rpc auto wrapping class
								XApp_Service::MANAGED_CLASS_OPTIONS => array(
									XApp_ResourceManager::STORE_CONF => array(
										XApp_Store_JSON::CONF_FILE => $XAPP_VFS_CONFIG_PATH
									)
								)
							)
						),
						/**
						 * Register user service, needed to enable user logon and provide an ACL based
						 * permission system
						 */
						XApp_Service::factoryEx(
							'XApp_XIDE_Controller_UserService',
							array(

								XApp_Service::MANAGED_CLASS_OPTIONS => array(
									XApp_UserManager::STORE_CONF => array(
										XApp_Store_JSON::CONF_FILE => $XAPP_USER_CONFIG_PATH
									)
								)
							)
						),
						/***
						 * XIDE Logging service
						 */
						XApp_Service::factoryEx(
							'XIDE_Log_Service',
							array(

								XApp_Service::MANAGED_CLASS => 'XIDE_Log_Manager',
								XApp_Service::MANAGED_CLASS_OPTIONS => array(
									XIDE_Log_Manager::LOG_PATH => $XIDE_LOG_PATH
								)
							)
						)
					);
				}


				/**
				 * Register workbench service
				 */
				if (isset($XAPP_COMPONENTS['xideve']) && $XAPP_COMPONENTS['xideve']) {

					xapp_import('xapp.xide.Workbench.Service');
					xapp_import('xapp.xide.Directory.Service');
					xapp_import("xapp.xide.Models.User");
					$user = new XApp_User();
					$user->setName('A');
					$services[] = XApp_Service::factory(
						'xapp.xide.Workbench.Service',
						array(
							XApp_XIDE_Workbench_Service::WORKBENCH_USER => $user,
							XApp_XIDE_Workbench_Service::WORKBENCH_DIRECTORY => realpath(
								XAPP_BASEDIR . '/../user/A/ws/workspace/'
							),
							XApp_XIDE_Workbench_Service::SITE_CONFIG_DIRECTORY => realpath(
								XAPP_BASEDIR . '/xide/Workbench/siteconfig/'
							)
						)
					);
				}


				/***
				 * Build bootstrap config for the RPC service
				 */
				$opt = array(

					XApp_Commander_Bootstrap::BASEDIR => XAPP_BASEDIR,
					XApp_Commander_Bootstrap::APP_NAME => 'xfile',
					XApp_Commander_Bootstrap::APP_FOLDER => $XAPP_APP_NAME,
					XApp_Commander_Bootstrap::RESOURCE_CONFIG_SUFFIX => '',
					XApp_Commander_Bootstrap::RESOURCE_RENDERER_PREFIX => $RESOURCE_PREFIX,
					XApp_Commander_Bootstrap::RESOURCE_RENDERER_CLZ => $RESOURCE_RENDERER,
					XApp_Commander_Bootstrap::PLUGIN_DIRECTORY => XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR,
					XApp_Commander_Bootstrap::PLUGIN_MASK => 'XCOM',
					XApp_Commander_Bootstrap::ALLOW_PLUGINS => $authDelegate->authorize(XC_OPERATION_PLUGINS_STR),
					XApp_Commander_Bootstrap::PROHIBITED_PLUGINS => $PROHIBITED_PLUGINS,
					XApp_Commander_Bootstrap::FLAGS => $bootStrapFlags,
					XApp_Commander_Bootstrap::AUTH_DELEGATE => $authDelegate,
					XApp_Commander_Bootstrap::RPC_TARGET => $RPC_TARGET,
					XApp_Commander_Bootstrap::SIGNING_KEY => md5($authDelegate->getUserName()),
					XApp_Commander_Bootstrap::SIGNING_TOKEN => md5($authDelegate->getToken()),
					XApp_Commander_Bootstrap::SIGNED_SERVICE_TYPES => array(

						XAPP_SERVICE_TYPE_SMD_CALL,  //client must sign any RPC call
						XAPP_SERVICE_TYPE_DOWNLOAD,
						XAPP_SERVICE_TYPE_SMD_GET
					),
					XApp_Commander_Bootstrap::GATEWAY_CONF => array(

						Xapp_Rpc_Gateway::ALLOW_IP => $ALLOW_IP,
						Xapp_Rpc_Gateway::DENY_IP => $DENY_IP,
						Xapp_Rpc_Gateway::ALLOW_HOST => $ALLOW_HOST,
						Xapp_Rpc_Gateway::DENY_HOST => $DENY_HOST,
						Xapp_Rpc_Gateway::OMIT_ERROR => true
					),
					XApp_Commander_Bootstrap::LOGGING_FLAGS => array(
						XAPP_LOG_SHARED_LOGGER_PLUGINS,
						XAPP_LOG_XFILE_OPERATIONS
					),
					XApp_Commander_Bootstrap::LOGGING_CONF => array(
						Xapp_Log::PATH => $XAPP_LOG_DIR,
						Xapp_Log::EXTENSION => 'log',
						Xapp_Log::NAME => $XAPP_AUTH_SUFFIX
					),
					XApp_Commander_Bootstrap::XAPP_CONF => array(
						XAPP_CONF_DEBUG_MODE => null,
						XAPP_CONF_AUTOLOAD => false,
						XAPP_CONF_DEV_MODE => $_DEBUG,
						XAPP_CONF_HANDLE_BUFFER => !XApp_Service_Entry_Utils::isDownload(),
						XAPP_CONF_HANDLE_SHUTDOWN => false,
						XAPP_CONF_HTTP_GZIP =>false,// !XApp_Service_Entry_Utils::isDownload(),
						XAPP_CONF_CONSOLE => $_DEBUG ? XApp_Service_Entry_Utils::getConsoleType() : false,
						XAPP_CONF_HANDLE_ERROR => false,
						XAPP_CONF_HANDLE_EXCEPTION => false
					),
					XApp_Commander_Bootstrap::STORE_CONF => array(
						XApp_Store::READER_CLASS => $STORE_CLASS,
						XApp_Store::WRITER_CLASS => $STORE_CLASS,
						XApp_Store::PRIMARY_KEY => trim(preg_replace('/\s+/', '', $authDelegate->getUserName())),
						XApp_Store::IDENTIFIER => $XAPP_AUTH_SUFFIX,
						XApp_Store::CONF_FILE => $STORE_FILE
					),
					XApp_Commander_Bootstrap::SERIVCE_CONF => $services,
					XApp_Commander_Bootstrap::USER_CONF => $XAPP_USER_CONFIG_PATH
				);

				if($XAPP_BOOTSTRAP_OVERRIDE){
					//$opt = array_merge_recursive((array)$opt,(array)$XAPP_BOOTSTRAP_OVERRIDE);
				}

				/*
				if(isset($XAPP_BOOTSTRAP_OVERRIDE[XApp_Commander_Bootstrap::GATEWAY_CONF])){

					$_src = $XAPP_BOOTSTRAP_OVERRIDE[XApp_Commander_Bootstrap::GATEWAY_CONF];

					$_dst = $opt[XApp_Commander_Bootstrap::GATEWAY_CONF];
					foreach (Xapp_Rpc_Gateway::$optionsDict as $key => $val) {

						if(is_string($key)){
							$_val = $_src->get($key);
							if($_src->has($key) || ($_src->get($key) === false)){
								$_dst[$key] = $_src->get($key);
							}
						}
					}
					$opt[XApp_Commander_Bootstrap::GATEWAY_CONF] = $_dst;
				}
				*/

				mergeOverrideToConfig(Xapp_Rpc_Gateway::$optionsDict,$opt,$XAPP_BOOTSTRAP_OVERRIDE,XApp_Commander_Bootstrap::GATEWAY_CONF);
				$xappBootrapper = XApp_Commander_Bootstrap::instance($opt);
				$xappBootrapper->setupService();//tear down here
				break;//over and out

			}
		}
		exit;
	}


	/*******************************************************************************************/
	/*  Its not RPC, render UX                                                                 */
	/*******************************************************************************************/

	//Setup paths, variables and shit
	$XAPP_SERVICE_URL = $RPC_URL;
	$XAPP_APP_URL = $XAPP_SITE_URL . '/client/src';
	$XAPP_PLUGIN_URL = '' . $XAPP_PLUGIN_URL;
	$XAPP_SERVICE_URL_FULL = $XAPP_SITE_URL . '/' . $RPC_URL;

	$XAPP_RELATIVE_VARIABLES = array(
		'APP_URL' => $XAPP_APP_URL,
		'SITEURL' => $XAPP_SITE_URL,
		'XCOM_ROOT' => $XAPP_PLUGIN_URL,
		'FILE_SERVICE' => $XAPP_SERVICE_URL,
		'XFILE_CONFIG_MIXIN' => $XFILE_CONFIG,
		'FILES_STORE_URL' => $FILE_STORE_URL,
		'FILE_SERVICE_FULL' => $XAPP_SERVICE_URL_FULL,
		'XCOM_PLUGINS_WEB_URL' => $XAPP_PLUGIN_URL . '/',
		'RPC_SIGNATURE_TOKEN' => md5($XAPP_AUTH_DELEGATE->getToken()),
		'RPC_USER_VALUE' => md5($XAPP_AUTH_DELEGATE->getUserName()),
		'RPC_URL' => $RPC_URL,
		'REPO_URL' => $XAPP_SITE_URL . '/' . $REPOSITORY_START_PATH,
		'PLUGIN_PACKAGE_ROOT_URL' => $XAPP_PLUGIN_URL,
		'THEME' => $XAPP_JQUERY_THEME,
		'INDEX' => xapp_fix_index(),
		'XIDEVE_DOJO_BASE_URL' => $XAPP_APP_URL . '/lib/dojo',
		'XIDEVE_DOJO_URL' => $XAPP_APP_URL . '/lib/dojo/dojo.js',
		'XIDEVE_LIB_ROOT' => $XAPP_APP_URL . '/lib/'
	);

	if ($RELATIVE_VARIABLES) {
		$XAPP_RELATIVE_VARIABLES = array_merge($XAPP_RELATIVE_VARIABLES, $RELATIVE_VARIABLES);
	}

	/***
	 * Setup xapp commander bootstrap
	 */
	$options = array(
		XApp_Commander_Bootstrap::RESOURCE_RENDERER_PREFIX => $RESOURCE_PREFIX,
		XApp_Commander_Bootstrap::RESOURCE_CONFIG_SUFFIX => $RESOURCE_CONFIG_PREFIX,
		XApp_Commander_Bootstrap::BASEDIR => XAPP_BASEDIR,
		XApp_Commander_Bootstrap::DOC_ROOT => $XAPP_APP_URL,
		XApp_Commander_Bootstrap::APP_NAME => $XAPP_APP_NAME,
		XApp_Commander_Bootstrap::APP_FOLDER => 'xfile',
		XApp_Commander_Bootstrap::APPDIR => $XAPP_CLIENT_DIRECTORY,
		XApp_Commander_Bootstrap::SERVICE => $XAPP_SERVICE_URL,
		XApp_Commander_Bootstrap::PLUGIN_MASK => 'XCOM',
		XApp_Commander_Bootstrap::RENDER_DELEGATE => $RENDER_DELEGATE,
		XApp_Commander_Bootstrap::RESOURCE_RENDERER_CLZ => $RESOURCE_RENDERER,
		XApp_Commander_Bootstrap::ALLOW_PLUGINS => $XAPP_AUTH_DELEGATE::authorize(
			$XAPP_AUTH_PREFIX . XC_OPERATION_PLUGINS_STR,
			$XAPP_AUTH_SUFFIX
		),
		XApp_Commander_Bootstrap::PLUGIN_DIRECTORY => XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR,
		XApp_Commander_Bootstrap::PROHIBITED_PLUGINS => $PROHIBITED_PLUGINS,
		XApp_Commander_Bootstrap::RELATIVE_VARIABLES => $XAPP_RELATIVE_VARIABLES,
		XApp_Commander_Bootstrap::FLAGS => array(
			XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION
			//XAPP_BOOTSTRAP_ENABLE_GOOGLE_ANALYTICS
		),
		XApp_Commander_Bootstrap::USER_CONF => $XAPP_USER_CONFIG_PATH,
		XApp_Commander_Bootstrap::XAPP_RESOURCE_CONFIG => $XAPP_RESOURCE_CONFIG
	);


	if($XAPP_BOOTSTRAP_OVERRIDE){
		$options = array_merge_recursive((array)$options,(array)$XAPP_BOOTSTRAP_OVERRIDE);
	}

	//create bootstrap
	$xappBootrapper = new XApp_Commander_Bootstrap($options);



	//do the bootstrap
	$xappCommanderRenderer = $xappBootrapper->setup();

	//extract resource renderer
	$xappResourceRender = xapp_get_option(XApp_App_Commander::RESOURCE_RENDERER, $xappCommanderRenderer);

	$result = array(
		'renderer' => $xappCommanderRenderer,
		'resourceRender' => $xappResourceRender,
		'bootstrap' => $xappBootrapper
	);

	return $result;
}


/**
 * @param $XAPP_BASE_DIRECTORY
 * @param $XAPP_APP_NAME
 * @param $XAPP_CLIENT_DIRECTORY
 * @param $REPOSITORY_ROOT
 * @param $REPOSITORY_START_PATH
 * @param $UPLOAD_EXTENSIONS
 * @param $XFILE_CONFIG
 * @param string $XAPP_JQUERY_THEME
 * @param $LOG_DIRECTORY
 * @param $CONF_FILE
 * @param $XAPP_SALT_KEY
 * @param $XF_PROHIBITED_PLUGINS
 * @param $RELATIVE_VARIABLES
 * @param $XAPP_COMPONENTS
 * @param $XAPP_RESOURCE_CONFIG
 * @param null $XAPP_BOOTSTRAP_OVERRIDE
 *
 * @return array
 */
function createApp(
	$XAPP_BASE_DIRECTORY,
	$XAPP_APP_NAME,
	$XAPP_CLIENT_DIRECTORY,
	$REPOSITORY_ROOT,
	$REPOSITORY_START_PATH,
	$UPLOAD_EXTENSIONS,
	$XFILE_CONFIG,
	$XAPP_JQUERY_THEME = 'transparent',
	$LOG_DIRECTORY,
	$CONF_FILE,
	$XAPP_SALT_KEY,
	$XF_PROHIBITED_PLUGINS,
	$RELATIVE_VARIABLES,
	$XAPP_COMPONENTS,
	$XAPP_RESOURCE_CONFIG,
	$XAPP_BOOTSTRAP_OVERRIDE=null
) {
	/***
	 * prepare and adjust bootstrapper for stand-alone
	 */

	if (!defined('XAPP_BASEDIR')) {
		define('XAPP_BASEDIR', $XAPP_BASE_DIRECTORY);

	}
	require_once(XAPP_BASEDIR . 'XApp_Service_Entry_Utils.php');
	XApp_Service_Entry_Utils::includeXAppCore();
	XApp_Service_Entry_Utils::includeXAppRPC();
	require_once(XAPP_BASEDIR . 'app/Renderer.php');
	require_once(XAPP_BASEDIR . 'commander/Commander.php');

	XApp_App_Commander::loadDependencies();
	xapp_setup_language_standalone();
	xapp_import('xapp.Utils.Strings');
	xapp_import('xapp.Utils.Debugging');


	$urlParams = array();


	if (isset($_SERVER["QUERY_STRING"])) {
		XApp_Utils_Strings::parse_str($_SERVER["QUERY_STRING"], $urlParams);
		if (isset($urlParams['view'])) {
			unset($urlParams['view']);
		}
	}


	define('XAPP_INDEX', xapp_fix_index());

	/***
	 * Quick'n dirty auth delegate
	 * @TODO replace with new ACL/Permission system
	 */
	class XAPP_AUTH_DELEGATE
	{

		// salt key, passed from index.php
		public static $_salt;

		// xf config, passed from index.php
		public static $_config;

		/**
		 * Reject RPC methods
		 * @param $what
		 * @return bool
		 */
		public static function authorize($what)
		{

			/**
			 * Option 1. Use the xfile config passed from index.php
			 */
			if (self::$_config) {

				$data = (array)json_decode(self::$_config);
				$allowedActions = $data['ALLOWED_ACTIONS'];
				$intOp = (intval(XApp_Service_Entry_Utils::opToInteger($what)));
				if ($intOp != XC_OPERATION_UNKOWN) {
					if ($intOp > 0 && $intOp < count($allowedActions)) {//boundary check
						return $allowedActions[$intOp];
					}
				}
			}

			/**
			 * Option 2. Reject via string match if you like
			 */
			switch ($what) {
				case XC_OPERATION_COPY_STR:
				case XC_OPERATION_MOVE_STR:
				case XC_OPERATION_DELETE_STR:
				case XC_OPERATION_READ_STR:
				case XC_OPERATION_EDIT_STR:
				case XC_OPERATION_COMPRESS_STR:
				case XC_OPERATION_RENAME_STR:
				case XC_OPERATION_DOWNLOAD_STR:
				case XC_OPERATION_FILE_UPDATE_STR:
				case XC_OPERATION_NEW_DIRECTORY_STR:
				case XC_OPERATION_NEW_FILE_STR:
				case XC_OPERATION_UPLOAD:
				case XC_OPERATION_DOWNLOAD:
				case XC_OPERATION_EXTRACT: {
					return true;
				}
			}
			return true;
		}

		public function getUserName()
		{
			return 'admin';
		}

		public function getToken()
		{
			return md5(self::$_salt);
		}
	}

	XAPP_AUTH_DELEGATE::$_salt = $XAPP_SALT_KEY;
	XAPP_AUTH_DELEGATE::$_config = $XFILE_CONFIG;


	$authDelegate = new XAPP_AUTH_DELEGATE();

	$XAPP_XFILE_CONFIG_ARRAY = array();
	$XAPP_XFILE_CONFIG_ARRAY['XAPP_FILE_START_PATH'] = '';

	$XAPP_XFILE_CONFIG_ARRAY['XAPP_FILE_ROOT'] = $REPOSITORY_ROOT;

	require_once(XAPP_BASEDIR . 'lib/standalone/StoreDelegate.php');

	$extraParams = count($urlParams) ? '&' . http_build_query($urlParams) : '';

	if(!$XAPP_RESOURCE_CONFIG){
		$XAPP_RESOURCE_CONFIG='';
	}

	$renderStruct = xapp_commander_render_app(
		XAPP_BASEDIR,
		'xbox',
		$XAPP_CLIENT_DIRECTORY,
		$REPOSITORY_ROOT,
		$REPOSITORY_START_PATH,
		$UPLOAD_EXTENSIONS,
		$XFILE_CONFIG,
		$XAPP_JQUERY_THEME,
		dirname(XApp_Service_Entry_Utils::getUrl()) . '/',
		dirname(XApp_Service_Entry_Utils::getUrl()) . '/xapp/commander/plugins/',
		dirname(XApp_Service_Entry_Utils::getUrl()) . '/' . XAPP_INDEX . '?view=rpc',
		$authDelegate,
		'',
		'',
		$LOG_DIRECTORY,
		$XF_PROHIBITED_PLUGINS,
		'standalone',
		'XCOM_Resource_Renderer',
		'',
		new stdClass(),
		null,
		null,
		null,
		null,

		dirname(XApp_Service_Entry_Utils::getUrl()) . '/' . XAPP_INDEX . '?view=smdCall' . $extraParams,
		dirname(XApp_Service_Entry_Utils::getUrl()) . '/' . XAPP_INDEX . '?view=rpc' . $extraParams,

		'XApp_Store_Delegate',
		$CONF_FILE,
		$XAPP_SALT_KEY,
		$RELATIVE_VARIABLES,
		XApp_Service_Entry_Utils::isDebug() === true,
		$XAPP_COMPONENTS,
		$XAPP_RESOURCE_CONFIG,
		$XAPP_BOOTSTRAP_OVERRIDE
	);


	return $renderStruct;
}