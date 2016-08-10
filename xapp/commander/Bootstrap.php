<?php
/**
 * @version 2.0.1
 * @author https://github.com/gbaumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
$global_xapp_logger = null;

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


xapp_import("xapp.Bootstrap");

/***
 * Class XApp_Commander_Bootstrap
 * Utility class to do the initial work.
 */
class XApp_Commander_Bootstrap extends XApp_Bootstrap implements Xapp_Singleton_Interface
{
    /**
     *
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::BASEDIR => XAPP_TYPE_STRING,
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
        self::ALLOW_PLUGINS => array(XAPP_TYPE_INT, XAPP_TYPE_BOOL, XAPP_TYPE_OBJECT),
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
        self::GOOGLE_ANALYTICS_ID => XAPP_TYPE_STRING,
        self::XAPP_RESOURCE_CONFIG => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::BASEDIR => 1,
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
        self::GOOGLE_ANALYTICS_ID => 0,
        self::XAPP_RESOURCE_CONFIG => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::BASEDIR => null,
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
        self::ALLOW_PLUGINS => false,
        self::FLAGS => 0,
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
        self::GOOGLE_ANALYTICS_ID => null,
        self::XAPP_RESOURCE_CONFIG => null
    );

    /**
     * contains the singleton instance for this class
     *
     * @var null|XApp_Commander_Bootstrap
     */
    protected static $_instance = null;

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

    /***
     * Render client ux
     */
    public function render($print = true)
    {

        $this->setup();
        xapp_setup_language_standalone();

        xapp_import('xapp.Service.Utils');

        $XAPP_COMPONENTS = array(

            'xfile' => true,
            'xnode' => false,
            'xideve' => false,
            'xblox' => false
        );

        $resourceRenderer = $this->getResourceRenderer();

        $resourceRenderer->registerRelative(
            'COMPONENTS',
            Xapp_Util_Json::prettify(json_encode($XAPP_COMPONENTS))
        );

        $resourceRenderer->registerRelative(
            'PACKAGE_CONFIG',
            XApp_Service_Utils::_getKey('run', 'run-release-debug')
        );

        $head = $this->appRenderer->renderHead();

        $resourceRenderer->registerRelative('HTML_HEADER', $head);
        $resourceRenderer->registerRelative('RPC_URL', self::getRpcViewUrl());

        $OVERRIDE = xo_get(self::OVERRIDE);


        /*
                $gatewayOpt     = xapp_has_option(self::GATEWAY_CONF) ? xapp_get_option(self::GATEWAY_CONF) : array();
                if(!$gatewayOpt){
                    error_log('nodfdf');
                }
                $SALT           = xo_get('RPC_GATEWAY_SALT',$gatewayOpt);

                error_log('salt ' . $SALT);
        */
        $signToken = md5($OVERRIDE['SALT']);

        $rpcUserName = md5('guest');

        $userManger = $this->getUserManager();
        $permissions = array();
        if ($userManger) {
            $user = $userManger->getUser();
            if ($user) {
                $rpcUserName = md5($user->getName());
                $permissions = $userManger->getUserPermissions();
            }
        }


        $this->resourceRenderer->registerRelative('permissions', Xapp_Util_Json::prettify(json_encode($permissions)));
        $this->resourceRenderer->registerRelative('RPC_SIGNATURE_TOKEN', $signToken);
        $this->resourceRenderer->registerRelative('RPC_USER_VALUE', $rpcUserName);


        //important: get the resource variables before adding 'head' otherwise it breaks the JSON structure!
        $resourceVariables = (array)$this->resourceRenderer->registryToKeyValues(
            xapp_get_option(XApp_Resource_Renderer::RELATIVE_REGISTRY_NAMESPACE, $this->resourceRenderer)
        );

        //exit;
        $resourceVariables['HTML_HEADER'] = array();
        $resourceVariables['XAPP_PLUGIN_RESOURCES'] = array();

        $this->resourceRenderer->registerRelative(
            'RESOURCE_VARIABLES',
            Xapp_Util_Json::prettify(json_encode($resourceVariables, true))
        );

        if ($this->isLoggedIn()) {
            $body = $this->resourceRenderer->renderHTML();
            echo($body);
            return $body;
        } else {

            $body = $this->resourceRenderer->renderHTML(XAPP_RESOURCE_TYPE_LOGIN);
            echo($body);
            return $body;
        }
    }

    /**
     * Prepare client ux
     * @throws Xapp_Util_Exception_Storage
     */
    public function setup()
    {

        $this->loadMin();
        /***
         * Get run-time configuration, there is 'debug' and 'release'. For both cases there are
         * different resources to load.
         */
        $XAPP_RUN_TIME_CONFIGURATION = XApp_Service_Entry_Utils::getRunTimeConfiguration();

        /***
         * Now include all xapp stuff
         */
        //pull in registry of xapp core framework
        XApp_Service_Entry_Utils::includeXAppRegistry();


        //pull in parts of xapp json framework
        self::loadXAppJSONStoreClasses();

        //pull in json utils (to read client app's resource configuration
        self::loadJSONTools();

        //some debugging tools
        self::loadDebuggingTools();

        //pull in legacy client app renderer
        xapp_import('xapp.app.Renderer');

        //pull in xapp resource renderer
        xapp_import('xapp.Resource.Renderer');

        //pull in xide resource Renderer
        xapp_import('xapp.xide.Resource.Renderer');

        /***
         * Prepare resource renderer
         */

        //clients resource config path
        $XAPP_RESOURCE_CONFIG_PATH = '' . xapp_get_option(self::APPDIR, $this) . DIRECTORY_SEPARATOR;


        $APP_FOLDER = xapp_get_option(self::APP_FOLDER, $this);
        $APP_NAME = xapp_get_option(self::APP_NAME, $this);
        $RESOURCE_CONFIG_SUFFIX = xapp_get_option(self::RESOURCE_CONFIG_SUFFIX, $this);

        if ($XAPP_RUN_TIME_CONFIGURATION === 'debug') {

            $XAPP_RESOURCE_CONFIG_PATH .= xapp_get_option(
                    self::RESOURCE_CONFIG_PREFIX,
                    $this
                ) . DIRECTORY_SEPARATOR . xapp_get_option(
                    self::APP_NAME,
                    $this
                ) . DIRECTORY_SEPARATOR . 'resources-' . $XAPP_RUN_TIME_CONFIGURATION . xapp_get_option(
                    self::RESOURCE_CONFIG_SUFFIX,
                    $this
                ) . '.json';
        } else {

            if ($XAPP_RUN_TIME_CONFIGURATION === 'release') {
                $APP_FOLDER = 'xfile';
                $XAPP_RESOURCE_CONFIG_PATH .= DIRECTORY_SEPARATOR . $APP_FOLDER . DIRECTORY_SEPARATOR . $APP_NAME . DIRECTORY_SEPARATOR . 'resources-' . $XAPP_RUN_TIME_CONFIGURATION . $RESOURCE_CONFIG_SUFFIX . '.json';
            }
        }

        if (!file_exists($XAPP_RESOURCE_CONFIG_PATH)) {
            $this->log('have no client resource configuration at ' . $XAPP_RESOURCE_CONFIG_PATH . ', aborting');
            error_log('have no client resource configuration at ' . $XAPP_RESOURCE_CONFIG_PATH . ', aborting ' . $XAPP_RUN_TIME_CONFIGURATION . '\n' . xapp_get_option(self::APPDIR, $this));
        }


        //load resource configuration
        $resources = (object)XApp_Utils_JSONUtils::read_json($XAPP_RESOURCE_CONFIG_PATH, 'json', false, true);
        $pluginResources = null;

        if (xapp_get_option(self::ALLOW_PLUGINS, $this) &&
            xapp_get_option(self::PLUGIN_DIRECTORY, $this) &&
            xapp_get_option(self::PLUGIN_MASK, $this)
        ) {

            self::loadXFilePluginDependencies();

            $xComPluginManager = new XApp_Commander_PluginManager();
            $loadedPlugins = null;
            $plugins = $xComPluginManager->loadPlugins(
                xapp_get_option(self::PLUGIN_DIRECTORY, $this),
                xapp_get_option(self::PLUGIN_DIRECTORY, $this),
                xapp_get_option(self::PLUGIN_MASK, $this)
            );
            $pluginResources = $this->getPluginResources($plugins, $XAPP_RUN_TIME_CONFIGURATION, null);
        }

        //now merge xfile plugin resources into app resources
        if ($pluginResources) {
            foreach ($pluginResources as $pluginResourceItems) {
                foreach ($pluginResourceItems as $pluginResource) {
                    //array_push($resources->items, $pluginResource);
                }
            }
        }

        $resourceRendererOptions = array(
            XApp_Resource_Renderer::DOC_ROOT => xapp_get_option(self::DOC_ROOT, $this),
            XApp_Resource_Renderer::DOC_ROOT_PATH => xapp_get_option(self::APPDIR, $this),
            XApp_Resource_Renderer::RESOURCES_DATA => $resources
        );

        //create client resource renderer, forward variables
        $clz = xapp_get_option(self::RESOURCE_RENDERER_CLZ, $this);
        $xappResourceRenderer = new $clz($resourceRendererOptions);
        $xappResourceRenderer->registerDefault();
        if (xapp_has_option(self::RELATIVE_VARIABLES)) {
            $rVariables = xapp_get_option(self::RELATIVE_VARIABLES, $this);
            foreach ($rVariables as $variable => $value) {
                $xappResourceRenderer->registerRelative($variable, $value);
            }
        }


        $RELATIVE_VARS = xapp_get_option(XApp_Bootstrap::RELATIVE_VARIABLES, $this);
        $SITE_URL = '' . $RELATIVE_VARS['SITEURL'];


        $SITE_URL = str_replace('xapp/commander/', '', $SITE_URL);


        $dojoPackages = array();
        $dojoPackages[] = array();

        $javascriptPlugins = $xappResourceRenderer->getJavascriptPlugins();
        $XAPP_DOJO_PACKAGE_LOCATION_PREFIX = $SITE_URL . 'xapp/commander/plugins/';
        $xappResourceRenderer->registerRelative('XCOM_PLUGINS_WEB_URL', $XAPP_DOJO_PACKAGE_LOCATION_PREFIX);
        if ($javascriptPlugins && count($javascriptPlugins)) {
            foreach ($javascriptPlugins as $plugin) {
                if (is_object($plugin)) {
                    array_push(
                        $dojoPackages,
                        array(
                            'name' => $plugin->name,
                            'location' => $XAPP_DOJO_PACKAGE_LOCATION_PREFIX . $plugin->name . '/client/'
                        )
                    );
                }
            }
            $javaScriptHeaderStr = '';
            $javaScriptHeaderStr .= '';
            $javaScriptHeaderStr .= json_encode($javascriptPlugins) . ';';
            $javaScriptHeaderStr .= '';
            $xappResourceRenderer->registerRelative(
                'XAPP_PLUGIN_RESOURCES',
                Xapp_Util_Json::prettify(json_encode($javascriptPlugins))
            );

        }

        $dojoPackagesString = $this->createDojoPackageString('', $dojoPackages, false);

        $xappResourceRenderer->registerRelative('DOJOPACKAGES', $dojoPackagesString);
        /****
         * Build XApp-App-Renderer - Config
         */
        $opt = array
        (
            XApp_App_Renderer::DOC_ROOT_PATH => xapp_get_option(self::APPDIR, $this),
            XApp_App_Renderer::DOC_ROOT => xapp_get_option(self::DOC_ROOT, $this),
            XApp_App_Renderer::APP_NAME => xapp_get_option(self::APP_NAME, $this),
            //system application name
            XApp_App_Renderer::APP_FOLDER => xapp_get_option(self::APP_FOLDER, $this),
            //path prefix to the client application
            XApp_App_Renderer::CONFIG_NAME => $XAPP_RUN_TIME_CONFIGURATION,
            XApp_App_Renderer::SERVICE_URL => xapp_get_option(self::SERVICE, $this),
            XApp_App_Renderer::RESOURCE_RENDERER => $xappResourceRenderer
        );
        $this->appRenderer = new XApp_App_Renderer($opt);
        $this->resourceRenderer = xo_get(XApp_App_Renderer::RESOURCE_RENDERER, $this->appRenderer);
    }


    /**
     * @param $serverApplicationClassName
     * @param $clientApplicationName
     * @param $clientDirectory
     * @param $libOffset
     * @param $logDirectory
     * @param $xcvDataRoot
     * @param $xcvRoot
     * @param $clientOffset
     * @param $confDirectory
     * @return XApp_Commander_Bootstrap
     */
    private static function createServerInstance(
        $serverApplicationClassName,
        $clientApplicationName,
        $clientDirectory,
        $libOffset,
        $logDirectory,
        $xcvDataRoot,
        $xcvRoot,
        $clientOffset,
        $confDirectory,
        $VFSConfig,
        $OVERRIDE
    )
    {

        /***
         * Pull in dependencies
         */
        self::loadMin();
        self::loadRPC();

        xapp_import('xapp.Log');
        xapp_import('xapp.Service');
        xapp_import('xapp.File.Utils');
        xapp_import('xapp.Directory.Utils');
        xapp_import('xapp.Directory.Service');
        xapp_import('xapp.Path.Utils');

        xapp_import('xapp.VFS.Interface.Access');
        xapp_import('xapp.VFS.Base');
        xapp_import('xapp.VFS.Local');

        xapp_import('xapp.Resource.Renderer');
        xapp_import('xapp.Xapp.Hook');
        xapp_import('xapp.Option.*');

        xapp_import("xapp.xide.Models.User");
        xapp_import('xapp.xide.Controller.UserManager');
        xapp_import('xapp.xide.Controller.UserService');

        xapp_import('xapp.Store.Json.Json');
        xapp_import('xapp.commander.Directory.Service');
        xapp_import('xapp.Commons.VariableMixin');
        xapp_import('xapp.Resource.Service');
        xapp_import('xapp.Resource.ResourceManager');

        /**
         * Logging Imports
         */
        self::loadCommons();
        self::loadXFilePluginDependencies();
        xapp_setup_language_standalone();
        $RPC_CALL_TARGET = self::getRpcCallUrl();

        /**
         * VFS Config
         */
        $vfsVariables = array();
        $XAPP_VFS_CONFIG_PATH = realpath($xcvDataRoot . '/VFS.json');
        if ($VFSConfig) {

            if (isset($VFSConfig['path'])) {
                $XAPP_VFS_CONFIG_PATH = $VFSConfig['path'];
            }
            if (isset($VFSConfig['variables'])) {
                $vfsVariables = $VFSConfig['variables'];
            }
        }

        $options = array(
            self::ALLOW_PLUGINS => true,
            self::PLUGIN_MASK => 'XCOM',
            self::PLUGIN_DIRECTORY => XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR,
            self::BASEDIR => XAPP_BASEDIR,
            self::FLAGS => array(
                XAPP_BOOTSTRAP_SETUP_XAPP,              //takes care about output encoding and compressing
                XAPP_BOOTSTRAP_SETUP_RPC,               //setup a RPC server
                XAPP_BOOTSTRAP_SETUP_LOGGER,            //setup a logger
                XAPP_BOOTSTRAP_SETUP_GATEWAY,           //setup a gateway
                XAPP_BOOTSTRAP_SETUP_SERVICES,          //setup services,
                XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION,     //we need to be logged in
                XAPP_BOOTSTRAP_LOAD_PLUGIN_RESOURCES,
                XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS,
                //setup a settings store
                XAPP_BOOTSTRAP_SETUP_STORE
            ),
            self::RPC_TARGET => $RPC_CALL_TARGET,
            self::SIGNED_SERVICE_TYPES => array(
                XAPP_SERVICE_TYPE_SMD_CALL,  //client must sign any RPC call
                XAPP_SERVICE_TYPE_DOWNLOAD,
                XAPP_SERVICE_TYPE_SMD_GET
            ),
            self::GATEWAY_CONF => array(
                Xapp_Rpc_Gateway::OMIT_ERROR => XApp_Service_Entry_Utils::isDebug()
            ),
            self::LOGGING_FLAGS => array(
                XAPP_LOG_SHARED_LOGGER_SERVICES
            ),
            self::LOGGING_CONF => array(
                Xapp_Log::PATH => $logDirectory,
                Xapp_Log::EXTENSION => 'log',
                Xapp_Log::NAME => $serverApplicationClassName
            ),
            self::XAPP_CONF => array(
                XAPP_CONF_DEBUG_MODE => null,
                XAPP_CONF_AUTOLOAD => false,
                XAPP_CONF_DEV_MODE => true,
                XAPP_CONF_HANDLE_BUFFER => false,
                XAPP_CONF_HANDLE_SHUTDOWN => true,
                XAPP_CONF_HTTP_GZIP => true,
                XAPP_CONF_CONSOLE => null,
                XAPP_CONF_HANDLE_ERROR => true,
                XAPP_CONF_HANDLE_EXCEPTION => false
            ),
            self::SERIVCE_CONF => array(
                XApp_Service::factoryEx(
                    'XCOM_Directory_Service',
                    array(
                        XApp_Directory_Service::FILE_SYSTEM => 'XApp_VFS_Local',
                        XApp_Directory_Service::VFS_CONFIG_PATH => $XAPP_VFS_CONFIG_PATH,
                        XApp_Directory_Service::FILE_SYSTEM_CONF => array(
                            XApp_VFS_Base::ABSOLUTE_VARIABLES => $vfsVariables,
                            XApp_VFS_Base::RELATIVE_VARIABLES => array()
                        )
                    )
                ),
                XApp_Service::factory(
                    'XApp_Resource_Service',
                    array(
                        XApp_Service::MANAGED_CLASS => 'XApp_ResourceManager',//rpc auto wrapping
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
                                XApp_Store_JSON::CONF_FILE => $xcvDataRoot . "Users.json"
                            )
                        )
                    )
                )

            ),
            self::USER_CONF => $xcvDataRoot . "Users.json",
            self::OVERRIDE => $OVERRIDE
        );
        return self::instance($options);
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

    /***
     * Setup logger
     */
    private function setupLogger($loggingConf)
    {
        $logginConf[Xapp_Log::WRITER] = array(new Xapp_Log_Writer_File(xapp_get_option(Xapp_Log::PATH, $loggingConf)));
        return new Xapp_Log_Error($loggingConf);
    }

    /***
     * run xapp to init your application and make use of all in build features such as
     * debugging, auto-loader, error logging. xapp can only be initialized with this method
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
     *  Setup RPC-Endpoint
     */
    private function setupRPC()
    {

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
            'searchTest',
            'convert_size_to_num'
        );

        if (xapp_get_option(self::IGNORED_RPC_METHODS, $this)) {
            $ignoredRPCMethods = array_merge(xapp_get_option(self::IGNORED_RPC_METHODS, $this), $ignoredRPCMethods);
        } elseif (xapp_has_option(self::AUTH_DELEGATE, $this)) {

            /***
             * Additional security here, mark each service method which has not been authorized by the
             * auth delegate as ignored!
             *
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
                Xapp_Rpc_Smd::IGNORE_PREFIXES => array('_', '__'),
                Xapp_Rpc_Smd::SERVICE_OVER_GET => true
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
                Xapp_Rpc_Server::DEBUG => false,
                Xapp_Rpc_Server::OMIT_ERROR => false,
                Xapp_Rpc_Server::SERVICE_OVER_GET => true,
                Xapp_Rpc_Server::ALLOW_FUNCTIONS => true,
                Xapp_Rpc_Server::ALLOW_BATCHED_REQUESTS => true,
                Xapp_Rpc_Server::APPLICATION_ERROR => false,
                Xapp_Rpc_Server::METHOD_AS_SERVICE => false,
                Xapp_Rpc_Server::PARAMS_AS_ARRAY => true,
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
     * @param $flags
     */
    private function loadDependencies($flags)
    {
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
        if ($flags & XAPP_BOOTSTRAP_CREATE_RESOURCE_RENDERER) {

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
         * Load google analytics
         */
        if (in_array(XAPP_BOOTSTRAP_ENABLE_GOOGLE_ANALYTICS, $flags)) {
            try {
                include_once(XAPP_BASEDIR . '/lib/google/autoload.php');

                $trackerClass = '\\UnitedPrototype\\GoogleAnalytics\\Tracker';

                // Initilize GA Tracker
                $tracker = new $trackerClass('UA-3652513-13', 'xappcommander.com');


                // Assemble Visitor information
                // (could also get unserialized from database)
                $visitorClass = '\\UnitedPrototype\\GoogleAnalytics\\Visitor';
                $visitor = new  $visitorClass();
                $visitor->setIpAddress($_SERVER['REMOTE_ADDR']);
                $visitor->setUserAgent($_SERVER['HTTP_USER_AGENT']);
                $visitor->setScreenResolution('1024x768');

// Assemble Session information
// (could also get unserialized from PHP session)
                $sessionClass = '\\UnitedPrototype\\GoogleAnalytics\\Session';
                $session = new $sessionClass();

// Assemble Page information
                $pageClass = '\\UnitedPrototype\\GoogleAnalytics\\Page';
                $page = new $pageClass('/client.html');
                $page->setTitle('XApp-Commander-Client-Page');

// Track page view
                $tracker->trackPageview($page, $session, $visitor);
            } catch (Exception $e) {

            }


        }

        /***
         * Load plugin manager
         */
        if ($flags & XAPP_BOOTSTRAP_LOAD_PLUGIN_RESOURCES || $flags & XAPP_BOOTSTRAP_REGISTER_SERVER_PLUGINS) {

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

    /**
     * Setup Storage
     * @param $storeConf
     * @return XApp_Store
     */
    public function setupStore($storeConf)
    {
        return new XApp_Store($storeConf);
    }

    /**
     * Setup RPC
     * @return $this
     */
    public function setupService()
    {


        $flags = xapp_get_option(self::FLAGS, $this);

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
            xapp_set_option(self::LOGGER, $logger, $this);
            if (!function_exists('xp_log')) {
                function xp_log($message)
                {
                    $bootstrap = XApp_Commander_Bootstrap::instance();
                    $log = xapp_get_option(XApp_Commander_Bootstrap::LOGGER, $bootstrap);
                    $log->log($message);
                }
            }

        } else {

            if (!function_exists('xp_log')) {
                //fake logger
                function xp_log($message)
                {
                }

                ;
            }
        }


        /***
         * Setup XApp-PHP
         */
        if (in_array(XAPP_BOOTSTRAP_SETUP_XAPP, $flags)) {
            $this->setupXApp(xapp_has_option(self::XAPP_CONF, $this) ? xapp_get_option(self::XAPP_CONF, $this) : null);
        }

        /***
         * Setup RPC Server
         */
        if (in_array(XAPP_BOOTSTRAP_SETUP_RPC, $flags)) {
            $this->setupRPC();
        }

        $storeService = null;
        /***
         * Setup storage
         */
        if (in_array(XAPP_BOOTSTRAP_SETUP_STORE, $flags) && xapp_has_option(self::STORE_CONF, $this)) {
            $storeService = $this->setupStore(xapp_get_option(self::STORE_CONF, $this));
            //$this->testStore($storeService);
            xapp_set_option(self::STORE, $storeService, $this);
        }

        /***
         * Prepare resource renderer
         */
        if (in_array(XAPP_BOOTSTRAP_LOAD_CLIENT_RESOURCES, $flags)) {

            //clients resource config path
            $XAPP_RESOURCE_CONFIG_PATH = '' . xapp_get_option(self::APPDIR, $this) . DIRECTORY_SEPARATOR;
            $XAPP_RESOURCE_CONFIG = xapp_get_option(self::XAPP_RESOURCE_CONFIG, $this);


            if (strlen($XAPP_RESOURCE_CONFIG)) {

                if ($XAPP_RUN_TIME_CONFIGURATION === 'debug') {
                    $XAPP_RESOURCE_CONFIG_PATH .= 'lib' . DIRECTORY_SEPARATOR .
                        xapp_get_option(self::APP_NAME, $this) . DIRECTORY_SEPARATOR .
                        $XAPP_RESOURCE_CONFIG . xapp_get_option(self::RESOURCE_CONFIG_SUFFIX, $this) . '.json';
                } else {
                    if ($XAPP_RUN_TIME_CONFIGURATION === 'release') {
                        $XAPP_RESOURCE_CONFIG_PATH .= DIRECTORY_SEPARATOR .
                            xapp_get_option(self::APP_FOLDER, $this) . DIRECTORY_SEPARATOR .
                            xapp_get_option(self::APP_NAME, $this) . DIRECTORY_SEPARATOR .
                            $XAPP_RESOURCE_CONFIG . xapp_get_option(self::RESOURCE_CONFIG_SUFFIX, $this) . '.json';
                    }
                }

            } else {

                if ($XAPP_RUN_TIME_CONFIGURATION === 'debug') {
                    $XAPP_RESOURCE_CONFIG_PATH .= 'lib' . DIRECTORY_SEPARATOR .
                        xapp_get_option(self::APP_NAME, $this) . DIRECTORY_SEPARATOR .
                        'resources-' . $XAPP_RUN_TIME_CONFIGURATION . xapp_get_option(
                            self::RESOURCE_CONFIG_SUFFIX,
                            $this
                        ) . '.json';
                } else {
                    if ($XAPP_RUN_TIME_CONFIGURATION === 'release') {
                        $XAPP_RESOURCE_CONFIG_PATH .= DIRECTORY_SEPARATOR .
                            xapp_get_option(self::APP_FOLDER, $this) . DIRECTORY_SEPARATOR .
                            xapp_get_option(self::APP_NAME, $this) . DIRECTORY_SEPARATOR .
                            'resources-' . $XAPP_RUN_TIME_CONFIGURATION . xapp_get_option(
                                self::RESOURCE_CONFIG_SUFFIX,
                                $this
                            ) . '.json';
                    }
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

                                        $pluginPath = xapp_get_option(self::PLUGIN_DIRECTORY, $this) .
                                            DIRECTORY_SEPARATOR . $pluginConfig->location . DIRECTORY_SEPARATOR . $pluginConfig->name . '.php';

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
                                            $pluginConfig->name,//class name,
                                            true,//yes, call plugin->load()
                                            array(),//no service configuration
                                            array(),//no logging configuration
                                            xapp_get_options(),//our own configuration,
                                            $pluginConfig   //the plugin info, including all client side resources
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
         * Register store service
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
        }

        //Pick directory service instance
        $directoryServiceInstance = null;
        foreach ($serviceConfigurations as $service) {
            if ($service[XApp_Service::XAPP_SERVICE_CLASS] === 'XCOM_Directory_Service') {
                $directoryServiceInstance = $service[XApp_Service::XAPP_SERVICE_INSTANCE];
                break;
            }
        }

        //share directory service instance in plugins
        if ($directoryServiceInstance) {
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

                }

                $this->setGatewayOptionArray(Xapp_Rpc_Gateway::ALLOW_IP, $opt);
                $this->setGatewayOptionArray(Xapp_Rpc_Gateway::DENY_IP, $opt);
                $this->setGatewayOptionArray(Xapp_Rpc_Gateway::ALLOW_HOST, $opt);
                $this->setGatewayOptionArray(Xapp_Rpc_Gateway::DENY_HOST, $opt);



                /***
                 * Create the gateway
                 */
                $gateway = Xapp_Rpc_Gateway::instance(xapp_get_option(self::RPC_SERVER, $this), $opt);

                /***
                 * Set the API key for signed requests
                 */
                if ($needsSigning) {

                    $gateway->addKey(
                        xapp_get_option(self::SIGNING_KEY, $this),
                        xapp_get_option(self::SIGNING_TOKEN, $this)
                    );
                }
                $gateway->run();

            } catch (Exception $e) {
                Xapp_Rpc_Server_Json::dump($e);
            }
        }

        return $this;
    }

    /**
     * Bootstrap logger
     * @param $message
     * @param string $prefix
     * @param bool|true $stdError
     * @return null
     */
    public function log($message, $prefix = '', $stdError = false)
    {

        if (function_exists('xp_log')) {
            xp_log('XCom-Bootstrap : ' . $message);
        }

        if ($stdError) {
            error_log('XCom-Bootstrap : ' . $message);
        }
        return null;
    }

    /**
     * Helper for gateway options
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

    /**
     * Basics
     */
    private static function loadXFilePluginDependencies()
    {
        //pull in xapp plugin manager
        xapp_import('xapp.commander.PluginManager');
        //pull in xapp commander plugin base class
        xapp_import('xapp.commander.Plugin');
        //pull in xapp commander plugin base class
        xapp_import('xapp.commander.defines');
        //pull in RPC interface
        if (!class_exists('Xapp_Rpc_Interface_Callable')) {
            //pull in xapp commander plugin base class
            xapp_import('xapp.Rpc.Interface.Callable.php');
        }
    }


    /***
     * @param $serverApplicationClassName
     * @param $clientApplicationName
     * @param $clientDirectory
     * @param $libOffset
     * @param $logDirectory
     * @param $xcvDataRoot
     * @param $xcvRoot
     * @param $clientOffset
     * @param $confDirectory
     * @return XCF_Bootstrap
     */
    private static function createClientInstance(
        $serverApplicationClassName,
        $clientApplicationName,
        $clientDirectory,
        $libOffset,
        $logDirectory,
        $xcvDataRoot,
        $xcvRoot,
        $clientOffset,
        $confDirectory,
        $VFS_CONFIG,
        $OVERRIDE
    )
    {


        xapp_import('xapp.Http.Url');
        $url  = new XApp_Http_Url(self::getUrl());
        $XAPP_SITE_URL = $url->getBaseUrl();

        $clientOffset = 'client/src';
        $XAPP_APP_URL = $XAPP_SITE_URL . $clientOffset;
        $RPC_CALL_TARGET = self::getRpcCallUrl();
        $SMD_VIEW_TARGET = self::getRpcViewUrl();

        $RESOURCE_PREFIX = '';
        $RESOURCE_CONFIG_PREFIX = '';

        self::loadCommons();

        $XF_THEME = self::_getKey('theme', 'white');

        $XAPP_XFILE_PLUGIN_URL = $XAPP_SITE_URL . '';

        $XAPP_APP_URL = str_replace('/xapp/commander/', '/client', $XAPP_APP_URL);

        $options = array(

            self::ALLOW_PLUGINS => true,
            self::PLUGIN_MASK => 'XCOM',
            self::PLUGIN_DIRECTORY => XAPP_BASEDIR . DIRECTORY_SEPARATOR . 'commander' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR,
            self::SERVER_APPLICATION_CLASS => $serverApplicationClassName,
            self::RESOURCE_RENDERER_PREFIX => $RESOURCE_PREFIX,
            self::RESOURCE_CONFIG_SUFFIX => $RESOURCE_CONFIG_PREFIX,
            self::RESOURCE_CONFIG_PREFIX => $libOffset,
            self::BASEDIR => XAPP_BASEDIR,
            self::DOC_ROOT => $XAPP_APP_URL,
            self::APP_NAME => $clientApplicationName,
            self::APP_FOLDER => $clientApplicationName,
            self::APPDIR => $clientDirectory,
            self::SERVICE => $RPC_CALL_TARGET,
            self::RESOURCE_RENDERER_CLZ => 'XIDE_Resource_Renderer',
            self::RELATIVE_VARIABLES => array(
                'APP_URL' => $XAPP_APP_URL,
                'SITEURL' => $XAPP_SITE_URL,
                'RPC_TARGET' => $SMD_VIEW_TARGET,
                'THEME' => $XF_THEME,
                'XCOM_PLUGINS_WEB_URL' => $XAPP_XFILE_PLUGIN_URL,
                'XIDEVE_DOJO_BASE_URL' => $XAPP_APP_URL . '/lib/dojo',
                'XIDEVE_DOJO_URL' => $XAPP_APP_URL . '/lib/dojo/dojo.js',
                'XIDEVE_LIB_ROOT' => $XAPP_APP_URL . '/lib/'

            ),
            self::FLAGS => array(
                XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION
            ),
            self::USER_CONF => $xcvDataRoot . "Users.json",
            self::OVERRIDE => $OVERRIDE
        );
        return self::instance($options);
    }

    /**
     * Factory which creates a bootstrap config but also an instance of this upon the
     * the request type : RPC or Client app
     * @param $serverApplicationClassName
     * @param $clientApplicationName
     * @param $clientDirectory
     * @param $libOffset
     * @param $logDirectory
     * @param $xcvDataRoot
     * @param $xcvRoot
     * @param $serviceEntryPoint
     * @param $clientOffset
     * @param string $rootUrlOffset
     * @param $confDirectory
     * @param $VFSConfig
     * @param $OVERRIDE
     * @return mixed
     */

    /**
     * @param $serverApplicationClassName
     * @param $clientApplicationName
     * @param $clientDirectory
     * @param $libOffset
     * @param $logDirectory
     * @param $xcvDataRoot
     * @param $xcvRoot
     * @param $clientOffset
     * @param $confDirectory
     * @param $VFSConfig
     * @param $OVERRIDE
     * @return XApp_Commander_Bootstrap|XCF_Bootstrap
     */
    public static function createInstance(
        $serverApplicationClassName,
        $clientApplicationName,
        $clientDirectory,
        $libOffset,
        $logDirectory,
        $xcvDataRoot,
        $xcvRoot,
        $clientOffset,
        $confDirectory,
        $VFSConfig,
        $OVERRIDE
    )
    {
        if (self::isRPC()) {
            return self::createServerInstance(
                $serverApplicationClassName,
                $clientApplicationName,
                $clientDirectory,
                $libOffset,
                $logDirectory,
                $xcvDataRoot,
                $xcvRoot,
                $clientOffset,
                $confDirectory,
                $VFSConfig,
                $OVERRIDE
            );
        } else {
            /***
             * @param $serverApplicationClassName
             * @param $clientApplicationName
             * @param $clientDirectory
             * @param $libOffset
             * @param $logDirectory
             * @param $xcvDataRoot
             * @param $xcvRoot
             * @param $clientOffset
             * @param $confDirectory
             * @return XCF_Bootstrap
             **/
            return self::createClientInstance(
                $serverApplicationClassName,
                $clientApplicationName,
                $clientDirectory,
                $libOffset,
                $logDirectory,
                $xcvDataRoot,
                $xcvRoot,
                $clientOffset,
                $confDirectory,
                $VFSConfig,
                $OVERRIDE
            );
        }
    }
}

