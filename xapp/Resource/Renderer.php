<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Defines the resource type CSS. This will be used to include an uri to a CSS file into the HTML head section
 * @const XAPP_RESOURCE_TYPE_CSS
 */
define('XAPP_RESOURCE_TYPE_CSS', 'CSS');
/**
 * Defines the resource type CSS. This will be used to include an uri to a CSS file into the HTML head section
 * @const XAPP_RESOURCE_TYPE_CSS
 */
define('XAPP_RESOURCE_TYPE_HTML', 'HTML');

define('XAPP_RESOURCE_TYPE_LOGIN', 'LOGIN');
/**
 * Defines the resource type Javascript. This will be used to include an uri to a Javascript file into the HTML head section
 * @const XAPP_RESOURCE_TYPE_JS_INCLUDE
 */
define('XAPP_RESOURCE_TYPE_JS_INCLUDE', 'JS-HEADER-INCLUDE');
/**
 * Defines the resource type Javascript. This will be used to include an uri to a Javascript file into the HTML head section
 * @const XAPP_RESOURCE_TYPE_JS_INCLUDE
 */
define('XAPP_RESOURCE_TYPE_JS_INCLUDE_BODY', 'JS-BODY-INCLUDE');
/**
 * Defines the resource type Javascript header tag. This will be used to include the content of a Javascript file into the HTML head section
 * @const XAPP_RESOURCE_TYPE_JS_HEADER_SCRIPT_TAG
 */
define('XAPP_RESOURCE_TYPE_JS_HEADER_SCRIPT_TAG', 'JS-HEADER-SCRIPT-TAG');

/**
 * Defines the resource type Javascript Plugin.
 * @const XAPP_RESOURCE_TYPE_JS_PLUGIN
 */
define('XAPP_RESOURCE_TYPE_JS_PLUGIN', 'JS-PLUGIN');

/**
 * Defines the resource type File Proxy. This will be used to mount local folder at a given path.
 * @const XAPP_RESOURCE_TYPE_FILE_PROXY
 */
if(!defined('XAPP_RESOURCE_TYPE_FILE_PROXY')){
    define('XAPP_RESOURCE_TYPE_FILE_PROXY', 'FILE_PROXY');
}

/**
 * Defines the resource type File Proxy. This will be used to mount local folder at a given path.
 * @const XAPP_RESOURCE_TYPE_FILE_PROXY
 */
if(!defined('XAPP_RESOURCE_TYPE_REMOTE_FILE_PROXY')){
    define('XAPP_RESOURCE_TYPE_REMOTE_FILE_PROXY', 'REMOTE_FILE_PROXY');
}


/**
 * Defines the resource type File Proxy. This will be used to mount local folder at a given path.
 * @const XAPP_RESOURCE_TYPE_FILE_PROXY
 */
if(!defined('XAPP_RESOURCE_TYPE_NODEJS_SERVICE')){
    define('XAPP_RESOURCE_TYPE_NODEJS_SERVICE', 'NODE_JS_SERVICE');
}
/**
 * Defines the resource type Proxy. This will be used to mount external paths.
 * Use this to bypass cross domain restrictions of browsers
 * @const XAPP_RESOURCE_TYPE_FILE_PROXY
 */
define('XAPP_RESOURCE_TYPE_PROXY', 'PROXY');

/**
 * Defines the resource's url property which contains resource variables. This field should remain untouched!
 */
define('XAPP_RESOURCE_URL', 'url');

/**
 * Defines the resource's name property.
 */
define('XAPP_RESOURCE_NAME', 'name');

/**
 * Defines the resource's url resolved property.
 */
define('XAPP_RESOURCE_URL_RESOLVED', 'urlResolved');

/**
 * Defines the CSS 'flatten' property
 */
define('XAPP_RESOURCE_FLATTEN', 'flatten');

/**
 * Defines the resource's resolved absolute path.
 */
define('XAPP_RESOURCE_PATH_ABSOLUTE', 'pathResolved');

/**
 * Defines the resource's path property.
 */
define('XAPP_RESOURCE_PATH', 'path');



/***
 * Class XApp_Resource_Renderer
 * Singleton resource processor. It takes decoded json data in this format below and resolves
 * registered resources variables like %XASWEB% to urls and absolute local paths on disc.

    {
        "class":"cmx.types.Resources",
        "items":[
            {
                "class":"cmx.types.Resource",
                "type":"FILE_PROXY",
                "url":"/PMaster/xasweb/docroot/",
                "path":"/xasweb",
                "name":"xasweb"
            },
            {
                "class":"cmx.types.Resource",
                "type":"CSS",
                "url":"%XASWEB%/%XLIB%/%XMCSS%/themes/iphone/gallery.css",
                "addFileTime":true
            },
            {
                "class":"cmx.types.Resource",
                "type":"JS-HEADER-INCLUDE",
                "url":"%XASWEB%/%XLIB%/external/soundmanager2.js"
            },
            {
                "class":"cmx.types.Resource",
                "type":"JS-HEADER-SCRIPT-TAG",
                "url":"%XASWEB%/xapp/jsCommon/headScriptCommon.js"
            }
        ]
    }
 */
class XApp_Resource_Renderer implements Xapp_Singleton_Interface
{

    /**
     * contains the singleton instance created with create function
     *
     * @var null|XApp_Resource_Renderer
     */
    protected static $_instance = null;

    /***
     * current queued CSS includes, tracked to avoid duplicates
     * @var array
     */
    protected  $_cssQueue = array();
    /***
     * current queued Javascript includes, tracked to avoid duplicates
     * @var array
     */
    protected  $_jsQueue = array();

    /***
     * current queued Javascript header tags, tracked to avoid duplicates
     * @var array
     */
    private $_jsTagQueue = array();

    /***
     * Render delegate
     */
    const RENDER_DELEGATE   = "XAPP_RESOURCE_RENDER_DELEGATE";

    /***
     * Relative url to the client's app doc root
     */
    const RESOURCES_DATA   = "XAPP_RESOURCE_DATA";

    /***
    * Relative url to the client's app doc root
    */
    const DOC_ROOT   = "XAPP_RESOURCE_DOC_ROOT";

    /***
     * Absolute path to the client's app doc root
     */
    const DOC_ROOT_PATH   = "XAPP_RESOURCE_DOC_ROOT_PATH";

    /***
     * Specify the doc roots variable name
     */
    const DOC_ROOT_VAR_NAME   = "XAPP_RESOURCE_DOC_ROOT_VAR_NAME";

    /***
     * Namespace of the relative registry
     */
    const RELATIVE_REGISTRY_NAMESPACE   = "XAPP_RESOURCE_REGISTRY_NS_RELATIVE";

    /***
     * Namespace of the absolute registry
     */
    const ABSOLUTE_REGISTRY_NAMESPACE   = "XAPP_RESOURCE_REGISTRY_NS_ABSOLUTE";

    /***
     * Namespace of the absolute registry
     */
    const RESOURCE_VARIABLE_DELIMITER   = "XAPP_RESOURCE_VARIABLE_DELIMITER";

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::DOC_ROOT                      => XAPP_TYPE_STRING,
        self::DOC_ROOT_PATH                 => XAPP_TYPE_STRING,
        self::DOC_ROOT_VAR_NAME             => XAPP_TYPE_STRING,
        self::RESOURCES_DATA                => XAPP_TYPE_OBJECT,
        self::RENDER_DELEGATE               => XAPP_TYPE_OBJECT,
        self::RELATIVE_REGISTRY_NAMESPACE   => XAPP_TYPE_STRING,
        self::ABSOLUTE_REGISTRY_NAMESPACE   => XAPP_TYPE_STRING,
        self::RESOURCE_VARIABLE_DELIMITER   => XAPP_TYPE_STRING,
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::DOC_ROOT                      => 0,
        self::DOC_ROOT_PATH                 => 0,
        self::DOC_ROOT_VAR_NAME             => 0,
        self::RESOURCES_DATA                => 0,
        self::RENDER_DELEGATE               => 0,
        self::RELATIVE_REGISTRY_NAMESPACE   => 0,
        self::ABSOLUTE_REGISTRY_NAMESPACE   => 0,
        self::RESOURCE_VARIABLE_DELIMITER   => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::DOC_ROOT                      => null,
        self::DOC_ROOT_PATH                 => null,
        self::DOC_ROOT_VAR_NAME             => 'XASWEB',
        self::RESOURCES_DATA                => null,
        self::RENDER_DELEGATE               => null,
        self::RELATIVE_REGISTRY_NAMESPACE   => 'xapp_relative_resource_ns',
        self::ABSOLUTE_REGISTRY_NAMESPACE   => 'xapp_absolute_resource_ns',
        self::RESOURCE_VARIABLE_DELIMITER   => '%',

    );

    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($mixed = null, $options = null)
    {
        xapp_set_options($options, $this);
    }

    public function printPaths(){
        echo('<br/> XAPP RESOURCE RENDERER - PATHS<br/>');
        echo('DOC ROOT : ' . xapp_get_option(self::DOC_ROOT,$this) . '<br/>');
        echo('DOC ROOT PATH : ' . xapp_get_option(self::DOC_ROOT_PATH,$this) . '<br/>');

        echo('RESOURCE DATA' . json_encode(xapp_get_option(self::RESOURCES_DATA,$this)) . '<br/>');

        $keyValues = $this->registryToKeyValues(xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this));

        echo('RESOURCE RELATIVE VARIABLES' . json_encode($keyValues) . '<br/>');

        $keyValues = $this->registryToKeyValues(xapp_get_option(self::ABSOLUTE_REGISTRY_NAMESPACE,$this));

        echo('RESOURCE ABSOLUTE VARIABLES' . json_encode($keyValues) . '<br/>');

    }
    /**
     * creates singleton instance for this class passing options to constructor
     *
     * @error 11102
     * @param null|mixed $options expects valid option object
     * @return null|Xapp_Registry
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }


    /***
     * Return an instance to a xapp-registry instance by a given namespace
     * @param $namespace
     * @return null|Xapp_Registry
     */
    public function getRegistry($namespace){
        return Xapp_Registry::instance(array(Xapp_Registry::DEFAULT_NS =>  $namespace));
    }

    /***
     * Register a resource variable in the relative namespace
     * @param $key
     * @param $relative
     * @param $absolute
     */
    public function registerRelative($key,$value){
        $resourceNamespace = xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this);
        $registry = $this->getRegistry($resourceNamespace);
        $res = $registry->set(
            $key,
            $value,
            $resourceNamespace,$this);
        return $res;

    }
    /***
     * Register a resource variable in the absolute namespace
     * @param $key
     * @param $relative
     * @param $absolute
     */
    public function registerAbsolute($key,$value){
        $resourceNamespace = xapp_get_option(self::ABSOLUTE_REGISTRY_NAMESPACE,$this);
        $registry = $this->getRegistry($resourceNamespace);
        $registry->set(
            $key,
            $value,
            $resourceNamespace);
    }

    /***
     * Register default resource variables
     */
    public function registerDefault(){

        //register our doc root relative path
        $docRootRelative = xapp_get_option(self::DOC_ROOT,$this);
        if($docRootRelative){
            $this->registerRelative(
                xapp_get_option(self::DOC_ROOT_VAR_NAME,$this),
                $docRootRelative);
        }

        //register our doc root absolute path
        $docRootAbsolute = xapp_get_option(self::DOC_ROOT_PATH,$this);
        if($docRootRelative){
            $this->registerAbsolute(
                xapp_get_option(self::DOC_ROOT_VAR_NAME,$this),
                $docRootAbsolute);
        }
    }
    /***
     * @param $key
     * @param $relative
     * @param $absolute
     */
    public function registerResource($key,$relative,$absolute=null){
    }
    /***
     * Returns all registered resource variables in a given namespace
     * @param $key
     * @param $relative
     * @param $absolute
     */
    public  function registryToKeyValues($namespace){
        $registry = $this->getRegistry($namespace);
        $result = $registry->getAll($namespace);
        return $result;
    }

    /***
     * Resolves a string with resource variables
     * For instance : %XASWEB%/xasthemes/claro/document.css
     * @param $string
     */
    public function resolveRelative($string){

        //pick up registry values
        $keyValues = $this->registryToKeyValues(xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this));
        return $this->_replaceResourceVariables($string,$keyValues);

    }

    /***
     * Resolves a string with resource variables to absolute paths
     * For instance : %XASWEB%/xasthemes/claro/document.css
     * @param $string
     */
    public function resolveAbsolute($string){

        //pick up registry values
        $keyValues = $this->registryToKeyValues(xapp_get_option(self::ABSOLUTE_REGISTRY_NAMESPACE,$this));
        return $this->_replaceResourceVariables($string,$keyValues);

    }

    /***
     * Does a multiple search and replace on a string using a key/value array.
     * @param $str
     * @param $vars
     * @return string
     */
    private function _replaceResourceVariables($str,$vars){

        $result =  '' . $str;
        $variableDelimiter = xapp_get_option(self::RESOURCE_VARIABLE_DELIMITER,$this);
        $userVars= (array)$vars;
        if($userVars){
            $_keys = array();
            $_values = array();
            foreach ($userVars as $key => $value)
            {
                array_push($_keys,$variableDelimiter.$key.$variableDelimiter);
                array_push($_values,$value);
            }
            $result = str_replace($_keys,$_values,$result
            );
        }
        return $result;
    }

    /***
     * Resolve the url of a given resource object
     * @param $resource
     */
    public function resolveResource($resource){

        if($resource!==null && is_object($resource)){
            if(xapp_property_exists($resource,XAPP_RESOURCE_URL)){
                $resource->{XAPP_RESOURCE_URL_RESOLVED}=$this->resolveRelative(xapp_property_get($resource,XAPP_RESOURCE_URL));                $resource->{XAPP_RESOURCE_PATH_ABSOLUTE}=$this->resolveAbsolute(xapp_property_get($resource,XAPP_RESOURCE_URL));
            }
        }else{
            error_log('resource/Renderer : have no resources!!!');
        }
        return $resource;
    }

    /***
     *  Resolves web urls and absolute paths
     */

    /***
     *  Resolves web urls and absolute paths
     */
    public function resolveResources(){
        $resourceData = xapp_get_option(self::RESOURCES_DATA,$this);
        if($resourceData!==null){

            $resourceItems = (array)xapp_object_find($resourceData,'.items',array('class='.'cmx.types.Resource'));
            if($resourceItems!=null && count($resourceItems)){
                foreach($resourceItems as $resourceItem){

                    if(!xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){
                        $this->resolveResource($resourceItem);
                    }
                }
            }
        }else{
            return false;
        }
       return true;
    }

    /***
     * @param string    $type
     * @param bool      $enabledOnly
     * @return array|null
     */
    public function getResourcesByType($type,$enabledOnly=true){
        $resourceData = xapp_get_option(self::RESOURCES_DATA,$this);
        if($resourceData!==null){
            $resourceItems = (array)xapp_object_find($resourceData,'.items',array('class='.'cmx.types.Resource','type='.$type,'enabled='.$enabledOnly));
            if($resourceItems!=null && count($resourceItems)){
                return $resourceItems;
            }

        }
        return null;
    }

    public function renderHTML($type=XAPP_RESOURCE_TYPE_HTML){

        $html = '';
        $cssItems = $this->getResourcesByType($type,true);
        if($cssItems!=null && count($cssItems)){

            foreach($cssItems as $resourceItem){

                if(!xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)){
                    $resourceItem = $this->resolveResource($resourceItem);
                }

                if(xapp_property_exists($resourceItem,XAPP_RESOURCE_PATH_ABSOLUTE)){
                    $path = xapp_property_get($resourceItem,XAPP_RESOURCE_PATH_ABSOLUTE);
                    if(file_exists($path)){
                        $content = file_get_contents($path);
                        if(($content !== false)){
                            $keyValues = $this->registryToKeyValues(xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this));
                            $content = $this->resolveRelative($content);
                            $html.=$content;
                        }
                    }else{

                    }
                }else{

                }
            }
        }else{

        }
        return $html;
    }
    public function getPluginResources(){}
    public function addJSIncludes(){}
    public function renderJavascriptHeaderTags(){}
    public function renderJavascriptBodyTags(){}
    public function renderCSS(){}

	public static function isSecure(){
		return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
	}

    public function render(){
        $this->addJSIncludes();
        $this->renderCSS();
        $this->renderJavascriptHeaderTags();
    }

}
