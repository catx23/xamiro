<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XIDE
 */

/***
 * Check we have at least the minimals
 */
if(!defined('XAPP')){
    include_once (XAPP_BASEDIR.'/Core/core.php');
    require_once (XAPP_BASEDIR.'/Xapp/Xapp.php');
    require_once (XAPP_BASEDIR.'/Xapp/Autoloader.php');
    require_once (XAPP_BASEDIR.'/Xapp/Cli.php');
    require_once (XAPP_BASEDIR.'/Xapp/Console.php');
    require_once (XAPP_BASEDIR.'/Xapp/Debug.php');
    require_once (XAPP_BASEDIR.'/Xapp/Error.php');
    require_once (XAPP_BASEDIR.'/Xapp/Event.php');
    require_once (XAPP_BASEDIR.'/Xapp/Option.php');
    require_once (XAPP_BASEDIR.'/Xapp/Option.php');
    require_once (XAPP_BASEDIR.'/Xapp/Reflection.php');
}

/***
 *
 */
xapp_import("xapp.Bootstrap");

/***
 * Class xide_Bootstrap extends the standard bootstrap
 */
class XIDE_Bootstrap extends XApp_Bootstrap{

    /***
     *
     * $serverApplicationClassName,
     * $clientApplicationName,
     * $clientDirectory
     *
     * Factory which creates a bootstrap config but also an instance of this upon the
     * the request type : RPC or Client app
     * @param $serverApplicationClassName
     * @param $clientApplicationName
     * @param $clientDirectory
     * @param $libOffset
     * @param $logDirectory
     * @param $xcvDataRoot
     * @param $xcvUserRoot
     * @param $xcvRoot
     * @param $serviceEntryPoint
     * @param $clientOffset
     * @param string $rootUrlOffset
     * @param $confDirectory
     * @return void|XCF_Bootstrap
     */
    public static function createInstance(

        $serverApplicationClassName,
        $clientApplicationName,
        $clientDirectory,
        $libOffset,
        $logDirectory,
        $xcvDataRoot,
        $xcvUserRoot,
        $xcvRoot,
        $serviceEntryPoint,
        $clientOffset,
        $rootUrlOffset = '',
        $confDirectory
    ) {
        if(self::isRPC()){
            return self::createServerInstance(
                $serverApplicationClassName,
                $clientApplicationName,
                $clientDirectory
            );
        }else{
            return self::createClientInstance(
                $serverApplicationClassName,
                $clientApplicationName,
                $clientDirectory
            );
        }
    }


    /***
     * Private factory to create a bootstrap instance for RPC requests
     * @param $serverApplicationClassName
     * @param $clientApplicationName
     * @param $clientDirectory
     * @return null
     */
    private  static function createServerInstance(
        $serverApplicationClassName,
        $clientApplicationName,
        $clientDirectory)
    {

        return null;

    }

    /***
     * @param $serverApplicationClassName
     * @param $clientApplicationName
     * @param $clientDirectory
     * @return XIDE_Bootstrap
     */
    private  static function createClientInstance($serverApplicationClassName,$clientApplicationName,$clientDirectory)
    {
        //instantiate the application class
        /***
         * Setup xapp commander bootstrap
         */
        $XAPP_SITE_URL              = dirname(self::getUrl()) . '/';
        $XAPP_APP_URL               = $XAPP_SITE_URL . '../../client/';
        $RPC_CALL_TARGET            = 'index.php?view=smdCall';
        $SMD_VIEW_TARGET            = 'index.php?view=rpc';
        $XAPP_SERVICE_URL_FULL      = $XAPP_SITE_URL . '/' . $SMD_VIEW_TARGET;

        $RESOURCE_PREFIX            = '';
        $RESOURCE_CONFIG_PREFIX     = '';


        $xappBootrapperOptions = array(
            self::SERVER_APPLICATION_CLASS=>  $serverApplicationClassName,
            self::RESOURCE_RENDERER_PREFIX=>  $RESOURCE_PREFIX,
            self::RESOURCE_CONFIG_SUFFIX  =>  $RESOURCE_CONFIG_PREFIX,
            self::BASEDIR                 =>  XAPP_BASEDIR,
            self::DOC_ROOT                =>  $XAPP_APP_URL,
            self::APP_NAME                =>  $clientApplicationName,
            self::APP_FOLDER              =>  'xfile',
            self::APPDIR                  =>  $clientDirectory,
            self::SERVICE                 =>  $RPC_CALL_TARGET,
            self::RESOURCE_RENDERER_CLZ   =>  'XIDE_Resource_Renderer',
            self::RELATIVE_VARIABLES      => array(
                'APP_URL'                   =>              $XAPP_APP_URL,
                'SITEURL'                   =>              $XAPP_SITE_URL,
            )
        );
        /*
         * Evaluated :
        Array
        (
            [XAPP_RESOURCE_RENDERER_PREFIX] =>
            [XAPP_RESOURCE_CONFIG_SUFFIX] =>
            [XAPP_BOOTSTRAP_BASEDIR] => /PMaster/x4mm/Code/trunk/xide-php/xapp/
            [XAPP_APP_DOC_ROOT] => http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp//client/
            [XAPP_APP_NAME] => xide
            [XAPP_APP_FOLDER] => xfile
            [XAPP_BOOTSTRAP_APP_DIR] => /PMaster/x4mm/Code/trunk/xide-php/client/
            [XAPP_BOOTSTRAP_SERVICE] => index.php?view=smdCall
            [XAPP_RESOURCE_RENDERER_CLZ] => XApp_Standalone_Resource_Renderer
            [XAPP_BOOTSTRAP_RELATIVE_VARS] => Array
            (
                [APP_URL] => http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp//client/
                [SITEURL] => http://192.168.1.37:81/x4mm/Code/trunk/xide-php/xapp/
            )

        )         ***/

        return self::instance($xappBootrapperOptions);

    }

    /**
     * @param bool|true $print
     * @param $xcvDataRoot
     * @return mixed
     */
    public function render($print = true,$xcvDataRoot,$xcvDataRoot){

        $this->setup();

        $head = $this->appRenderer->renderHead();
        $this->resourceRenderer->registerRelative('HTML_HEADER',$head);
        $body = $this->resourceRenderer->renderHTML();
        echo($body);
    }

    /***
     * @return null|XApp_App_Commander
     */
    public function setup(){


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

        //pull in custom resource Renderer
        xapp_import('xapp.xide.Resource.Renderer');


        /***
         * Prepare resource renderer
         */

        //clients resource config path
        $XAPP_RESOURCE_CONFIG_PATH = '' . xapp_get_option(self::APPDIR,$this) . DIRECTORY_SEPARATOR;
        if($XAPP_RUN_TIME_CONFIGURATION==='debug'){
            $XAPP_RESOURCE_CONFIG_PATH.='lib'.DIRECTORY_SEPARATOR. xapp_get_option(self::APP_NAME,$this) .DIRECTORY_SEPARATOR.'resources-'.$XAPP_RUN_TIME_CONFIGURATION. xapp_get_option(self::RESOURCE_CONFIG_SUFFIX,$this) . '.json';
        }else if($XAPP_RUN_TIME_CONFIGURATION==='release'){
            $XAPP_RESOURCE_CONFIG_PATH.= DIRECTORY_SEPARATOR . xapp_get_option(self::APP_FOLDER,$this) .DIRECTORY_SEPARATOR. xapp_get_option(self::APP_NAME,$this) .DIRECTORY_SEPARATOR.'resources-'.$XAPP_RUN_TIME_CONFIGURATION. xapp_get_option(self::RESOURCE_CONFIG_SUFFIX,$this) .'.json';
        }

        if(!file_exists($XAPP_RESOURCE_CONFIG_PATH)){
            $this->log('have no client resource configuration at ' . $XAPP_RESOURCE_CONFIG_PATH . ', aborting');
        }

        //load resource configuration
        $resources  = (object)XApp_Utils_JSONUtils::read_json($XAPP_RESOURCE_CONFIG_PATH,'json',false,true);
        $pluginResources=null;
        $resourceRendererOptions = array
        (
            XApp_Resource_Renderer::DOC_ROOT         =>xapp_get_option(self::DOC_ROOT,$this),
            XApp_Resource_Renderer::DOC_ROOT_PATH    =>xapp_get_option(self::APPDIR,$this),
            XApp_Resource_Renderer::RESOURCES_DATA   =>$resources
        );

        $clz = xapp_get_option(self::RESOURCE_RENDERER_CLZ,$this);


        $xappResourceRenderer = new $clz($resourceRendererOptions);
        $xappResourceRenderer->registerDefault();
        if(xapp_has_option(self::RELATIVE_VARIABLES)){
            $rVariables = xapp_get_option(self::RELATIVE_VARIABLES,$this);
            foreach($rVariables as $variable => $value){
                $xappResourceRenderer->registerRelative($variable,$value);
            }
        }
        //determine Dojo package paths and store them as JSON serialized resource variable
        $XAPP_DOJO_PACKAGE_LOCATION_PREFIX=$xappResourceRenderer->resolveRelative('%PLUGIN_PACKAGE_ROOT_URL%');

        $javascriptPlugins = $xappResourceRenderer->getJavascriptPlugins();

        $dojoPackages = $this->getDojoPackages($XAPP_DOJO_PACKAGE_LOCATION_PREFIX,$javascriptPlugins,XApp_Service_Entry_Utils::isDebug());

        $xappResourceRenderer->registerRelative('XAPP_PLUGIN_RESOURCES',$dojoPackages['plugins']);
        $xappResourceRenderer->registerRelative('DOJOPACKAGES',$dojoPackages['packages']);

	    /****
         * Build XApp-App-Renderer - Config
         */
        $opt = array
        (
            XApp_App_Renderer::DOC_ROOT_PATH       =>xapp_get_option(self::APPDIR,$this),//complete url to the client app doc root : http://192.168.1.37/joomla352//administrator/components/com_xappcommander/client/
            XApp_App_Renderer::DOC_ROOT            =>xapp_get_option(self::DOC_ROOT,$this),//complete absolute path : /mnt/ssd2/htdocs/joomla352/administrator/components/com_xappcommander//client/
            XApp_App_Renderer::APP_NAME            =>xapp_get_option(self::APP_NAME,$this),//system application name
            XApp_App_Renderer::APP_FOLDER          =>xapp_get_option(self::APP_FOLDER,$this),//path prefix to the client application
            XApp_App_Renderer::CONFIG_NAME         =>$XAPP_RUN_TIME_CONFIGURATION,
            XApp_App_Renderer::SERVICE_URL         =>xapp_get_option(self::SERVICE,$this),
            XApp_App_Renderer::RESOURCE_RENDERER   =>$xappResourceRenderer///components/com_xappcommander/index.php?option=com_xappcommander&view=rpc///components/com_xappcommander/index.php?option=com_xappcommander&view=rpc
        );
        $this->appRenderer      = new XApp_App_Renderer($opt);
        $this->resourceRenderer = xo_get(XApp_App_Renderer::RESOURCE_RENDERER,$this->appRenderer);
    }


    /**
     * Xapp_Singleton interface impl.
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return XIDE_Bootstrap
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }
}