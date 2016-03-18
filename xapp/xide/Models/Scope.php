<?php
/**
 * @version 0.1.0
 *
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Models
 */

xapp_import("xapp.Commons.Entity");
xapp_import('xapp.Utils.Strings');
xapp_import('xapp.Option.Utils');
xapp_import('xapp.Xapp.Registry');
/***
 * Class XIDE_Scope
 */
class XIDE_Scope extends XApp_Entity
{
    ////////////////////////////////////////////////////////////////////////////
    //
    //  Constants
    //
    ////////////////////////////////////////////////////////////////////////////

    //default scope names
    const SYSTEM                        = "system";
    const USER                          = "user";
    const APP                           = "app";

    ////////////////////////////////////////////////////////////////////////////
    //
    //  Options
    //
    ////////////////////////////////////////////////////////////////////////////

    /***
     * Relative resource variables
     */
    const RELATIVE_VARIABLES            = "XAPP_SCOPE_RELATIVE_VARS";

    /***
     * Relative resource variables
     */
    const ABSOLUTE_VARIABLES            = "XAPP_ABSOLUTE_VARS";

    /***
     * Namespace of the relative registry
     */
    const RELATIVE_REGISTRY_NAMESPACE   = "XAPP_SCOPE_REGISTRY_NS_RELATIVE";

    /***
     * Namespace of the absolute registry
     */
    const ABSOLUTE_REGISTRY_NAMESPACE   = "XAPP_SCOPE_REGISTRY_NS_ABSOLUTE";

    /***
     * Namespace of the absolute registry
     */
    const SCOPE_VARIABLE_DELIMITER      = "XAPP_SCOPE_VARIABLE_DELIMITER";

    /***
     * Scope name
     */
    const SCOPE_NAME                    = "XAPP_SCOPE_NAME";

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::SCOPE_NAME                    => XAPP_TYPE_STRING,
        self::RELATIVE_REGISTRY_NAMESPACE   => XAPP_TYPE_STRING,
        self::ABSOLUTE_REGISTRY_NAMESPACE   => XAPP_TYPE_STRING,
        self::SCOPE_NAME                    => XAPP_TYPE_STRING,
        self::SCOPE_VARIABLE_DELIMITER      => XAPP_TYPE_STRING,
        self::RELATIVE_VARIABLES            => XAPP_TYPE_ARRAY,
        self::ABSOLUTE_VARIABLES            => XAPP_TYPE_ARRAY
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::SCOPE_NAME                    => 1,
        self::RELATIVE_REGISTRY_NAMESPACE   => 0,
        self::ABSOLUTE_REGISTRY_NAMESPACE   => 0,
        self::SCOPE_VARIABLE_DELIMITER      => 0,
        self::RELATIVE_VARIABLES            => 0,
        self::ABSOLUTE_VARIABLES            => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::SCOPE_NAME                    => null,
        self::RELATIVE_REGISTRY_NAMESPACE   => 'xapp_relative_scope_ns',
        self::ABSOLUTE_REGISTRY_NAMESPACE   => 'xapp_absolute_scope_ns',
        self::SCOPE_VARIABLE_DELIMITER      => '__',
        self::RELATIVE_VARIABLES            => null,
        self::ABSOLUTE_VARIABLES            => null

    );

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
        $this->parseOptions($options);
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //  Variable related implementation
    //
    ////////////////////////////////////////////////////////////////////////////

    /***
     * Create or wire the managed class instance
     * @param $options
     */
    protected  function parseOptions($options){

        /***
         * Register relative scope variables
         */
        if(xo_has(self::RELATIVE_VARIABLES,$options) && xo_get(self::RELATIVE_VARIABLES,$options)){
            $variables = xo_get(self::RELATIVE_VARIABLES,$options);
            foreach($variables as $variable => $value){
                $this->registerRelative($variable,$value);
            }
        }

        /***
         * Register absolute scope variables
         */
        if(xo_has(self::ABSOLUTE_VARIABLES,$options) && xo_get(self::ABSOLUTE_VARIABLES,$options)){
            $variables = xo_get(self::ABSOLUTE_VARIABLES,$options);
            foreach($variables as $variable => $value){
                $this->registerAbsolute($variable,$value);
            }
        }
    }

    /***
     * Factory to create default options
     * @param $name
     * @return array
     */
    public static function optionFactory($name,$relativeVariables=null,$absoluteVariables=null){
        $options = array(
            self::SCOPE_NAME =>$name
        );
        if($relativeVariables){
            $options[self::RELATIVE_VARIABLES]=$relativeVariables;
        }
        if($absoluteVariables){
            $options[self::ABSOLUTE_VARIABLES]=$absoluteVariables;
        }
        return $options;
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
     * Register a scope variable in the relative namespace
     * @param $key
     * @param $relative
     * @param $absolute
     */
    public function registerRelative($key,$value){
        $resourceNamespace = xo_get(self::RELATIVE_REGISTRY_NAMESPACE,$this);
        $registry = $this->getRegistry($resourceNamespace);
        $res = $registry->set(
            $key,
            $value,
            $resourceNamespace,$this);
        return $res;

    }
    /***
     * Register a scope variable in the absolute namespace
     * @param $key
     * @param $relative
     * @param $absolute
     */
    public function registerAbsolute($key,$value){
        $resourceNamespace = xo_get(self::ABSOLUTE_REGISTRY_NAMESPACE,$this);
        $registry = $this->getRegistry($resourceNamespace);
        $registry->set(
            $key,
            $value,
            $resourceNamespace);
    }
    /***
     * Resolves a string with resource variables
     * For instance : %XASWEB%/xasthemes/claro/document.css
     * @param $string
     */
    public function resolveRelative($string){

        //pick up registry values
        $keyValues = $this->registryToKeyValues(xapp_get_option(self::RELATIVE_REGISTRY_NAMESPACE,$this));
        return $this->_replaceScopeVariables($string,$keyValues);
    }

    /***
     * Resolves a string with resource variables to absolute paths
     * For instance : %XASWEB%/xasthemes/claro/document.css
     * @param $string
     */
    public function resolveAbsolute($string){

        //pick up registry values
        $keyValues = $this->registryToKeyValues(xapp_get_option(self::ABSOLUTE_REGISTRY_NAMESPACE,$this));
        return $this->_replaceScopeVariables($string,$keyValues);

    }

    /***
     * Does a multiple search and replace on a string using a key/value array.
     * @param $str
     * @param $vars
     * @return string
     */
    private function _replaceScopeVariables($str,$vars){

        $result =  '' . $str;
        $variableDelimiter = xapp_get_option(self::SCOPE_VARIABLE_DELIMITER,$this);
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
}


?>