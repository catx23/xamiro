<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
class XApp_Variable_Mixin
{

    /////////////////////////////////////////////////////////////////////////////////////////
    //
    //  Hooks
    //
    ////////////////////////////////////////////////////////////////////////////////////////

    /***
     * Absolute path to a resource file
     */
    const RESOURCES_DATA   = "XAPP_RESOURCE_DATA";

    /***
     * Resource type to enumerate
     */
    const RESOURCES_TYPE   = "XAPP_RESOURCE_TYPE";

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


    /***
     * Relative variables
     */
    const RELATIVE_VARIABLES            = "XAPP_RELATIVE_VARS";

    /***
     * Relative variables
     */
    const ABSOLUTE_VARIABLES            = "XAPP_ABSOLUTE_VARS";

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::RESOURCES_TYPE                => XAPP_TYPE_STRING,
        self::RESOURCES_DATA                => array(XAPP_TYPE_OBJECT,XAPP_TYPE_ARRAY,XAPP_TYPE_STRING),
        self::RELATIVE_VARIABLES            => XAPP_TYPE_ARRAY,
        self::ABSOLUTE_VARIABLES            => XAPP_TYPE_ARRAY,
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
        self::RESOURCES_DATA                => 1,
        self::RESOURCES_TYPE                => 1,
        self::RELATIVE_VARIABLES            => 0,
        self::ABSOLUTE_VARIABLES            => 0,
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
        self::RESOURCES_DATA                => null,
        self::RESOURCES_TYPE                => 'unknown',
        self::RELATIVE_VARIABLES            => null,
        self::ABSOLUTE_VARIABLES            => null,
        self::RELATIVE_REGISTRY_NAMESPACE   => 'xapp_relative_ns',
        self::ABSOLUTE_REGISTRY_NAMESPACE   => 'xapp_absolute_ns',
        self::RESOURCE_VARIABLE_DELIMITER   => '%'
    );

    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    function __construct($options = null)
    {
        xapp_set_options($options, $this);
        $this->initVariables();
    }

    /***
     * Transfers all relative and absolute variables to the registry
     */
    public function initVariables(){

        $resourceData = xo_get(self::RESOURCES_DATA,$this);
        if(is_string($resourceData)){
            xo_set(self::RESOURCES_DATA,(object)XApp_Utils_JSONUtils::read_json($resourceData,'json',false,true),$this);
        }

        if(xapp_has_option(self::RELATIVE_VARIABLES)){
            $rVariables = xapp_get_option(self::RELATIVE_VARIABLES,$this);
            if(count($rVariables)){
                foreach($rVariables as $variable => $value){
                    $this->registerRelative($variable,$value);
                }
            }
        }

        if(xapp_has_option(self::ABSOLUTE_VARIABLES)){
            $variables = xapp_get_option(self::ABSOLUTE_VARIABLES,$this);
            if(count($variables)){
                foreach($variables as $variable => $value){
                    $this->registerAbsolute($variable,$value);
                }
            }
        }
    }

    /***
     * Debugging tools, print what you have
     */
    public function printPaths(){

        echo('<br/> XAPP RESOURCE RENDERER - PATHS<br/>');

        echo('RESOURCE DATA' . json_encode(xapp_get_option(self::RESOURCES_DATA,$this)) . '<br/>');

        $keyValues = $this->registryToKeyValues(xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this));

        echo('RESOURCE RELATIVE VARIABLES' . json_encode($keyValues) . '<br/>');

        $keyValues = $this->registryToKeyValues(xapp_get_option(self::ABSOLUTE_REGISTRY_NAMESPACE,$this));

        echo('RESOURCE ABSOLUTE VARIABLES' . json_encode($keyValues) . '<br/>');

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
        /*
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
        */
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
    public function _replaceResourceVariables($str,$vars){

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
            $result = str_replace(
                $_keys,
                $_values,
                $result
            );
        }
        return $result;
    }
    /***
     * Does a multiple search and replace on a string using a key/value array.
     * @param $str
     * @param $vars
     * @return string
     */
    public static function replaceResourceVariables($str,$vars,$variableDelimiter='%'){

        $result =  '' . $str;
        $userVars= (array)$vars;
        if($userVars){
            $_keys = array();
            $_values = array();
            foreach ($userVars as $key => $value)
            {
                array_push($_keys,$variableDelimiter.$key.$variableDelimiter);
                array_push($_values,$value);
            }
            $result = str_replace(
                $_keys,
                $_values,
                $result
            );
        }
        return $result;
    }

    /***
     * Resolve the url of a given resource object
     * @param $resource
     */
    public function resolveResource(&$resource){

        if($resource!==null && is_object($resource)){

            if( xapp_property_exists($resource,XAPP_RESOURCE_NAME)&&
                xapp_property_exists($resource,XAPP_RESOURCE_PATH)){
                //not resolved yet ?
                if(!xapp_property_exists($resource,XAPP_RESOURCE_PATH_ABSOLUTE)){
                    $resource->{XAPP_RESOURCE_PATH_ABSOLUTE}=$this->resolveAbsolute(xapp_property_get($resource,XAPP_RESOURCE_PATH));
                }
            }
        }else{
            error_log('resource/Renderer : have no resources!!!');
        }
        return $resource;
    }

    /***
     *  Resolves web urls and absolute paths
     */
    public function resolveResources(){
        $resourceData = xapp_get_option(self::RESOURCES_DATA,$this);
        if($resourceData!==null){

            $resourceItems = (array)xapp_object_find($resourceData,'.items',array('class='.'cmx.types.Resource'));
            if($resourceItems!=null && count($resourceItems)){
                foreach($resourceItems as $resourceItem){

                    if(
                        !xapp_property_exists($resourceItem,XAPP_RESOURCE_URL_RESOLVED)||
                        !xapp_property_exists($resourceItem,XAPP_RESOURCE_PATH_ABSOLUTE))
                    {
                        $this->resolveResource($resourceItem);
                    }
                }
                xo_set(self::RESOURCES_DATA,$resourceItems);
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
            $resourceItems = (array)xapp_object_find($resourceData,'.',array('class='.'cmx.types.Resource','type='.$type,'enabled='.$enabledOnly));
            if($resourceItems!=null && count($resourceItems)){
                return $resourceItems;
            }

        }
        return null;
    }

    /***
     *
     * @param string    $type
     * @param bool      $enabledOnly
     * @return array|null
     */
    public function getResource($name,$enabledOnly=true){
        $resourceData = xapp_get_option(self::RESOURCES_DATA,$this);
        if($resourceData!==null){
            $resourceItem = (array)xapp_object_find($resourceData,'.items',array('class='.'cmx.types.Resource','name='.$name,'type='. xo_get(self::RESOURCES_TYPE,$this),'enabled='.$enabledOnly));
            if(count($resourceItem) && is_object($resourceItem[0])){
                return $resourceItem[0];
            }
        }
        return null;
    }
}
