<?php
/**
 * @version 0.1.0
 *
 * @author Luis Ramos
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Controller
 */

xapp_import("xapp.Commons.Entity");
xapp_import('xapp.xide.Models.Scope');

/***
 * Class XIDE_Scoped contains a set of scopes
 */
class XIDE_Scoped
{
    /***
     * The name of the class as string or an instance. An instance does not need options of course.
     */
    const SCOPE_CONFIGS          = 'XAPP_SCOPE_CONFIGURATIONS';
    /***
     * The name of the class as string or an instance. An instance does not need options of course.
     */
    const SCOPES                 = 'XAPP_SCOPES';
    /***
     * The name of the class as string or an instance. An instance does not need options of course.
     */
    const SCOPE_NS_PREFIX        = 'XAPP_SCOPE_NS_PREFIX';
    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::SCOPE_CONFIGS      => XAPP_TYPE_ARRAY,
        self::SCOPES             => XAPP_TYPE_ARRAY,
        self::SCOPE_NS_PREFIX    => XAPP_TYPE_STRING
    );
    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::SCOPE_CONFIGS       => 0,
        self::SCOPES              => 0,
        self::SCOPE_NS_PREFIX     => 0
    );
    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::SCOPE_CONFIGS       => null,
        self::SCOPES              => array(),
        self::SCOPE_NS_PREFIX     => ''
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
        //standard constructor
        xapp_set_options($options, $this);

        $this->parseOptions($options);
    }

    /***
     * @param $name
     * @return null|XIDE_Scope
     */
    public function getScope($name){

        $scopes = xo_get(self::SCOPES,$this);
        foreach($scopes as $scope){
            if(xo_get(XIDE_Scope::SCOPE_NAME,$scope)===$name){

                return $scope;
            }
        }
        return null;
    }

    /***
     * Create or wire the managed class instance
     * @param $options
     */
    protected  function parseOptions($options){

        if(xo_has(self::SCOPE_CONFIGS,$options) && xo_get(self::SCOPE_CONFIGS,$options)){

            $scopeConfigs = xo_get(self::SCOPE_CONFIGS);
            $scopes       = array();
            foreach($scopeConfigs as $scopeConfig){
                $scopeConfig[XIDE_Scope::RELATIVE_REGISTRY_NAMESPACE]= xo_get(self::SCOPE_NS_PREFIX) .'.' . $scopeConfig[XIDE_Scope::SCOPE_NAME] . '.absolute';
                $scopeConfig[XIDE_Scope::ABSOLUTE_REGISTRY_NAMESPACE]= xo_get(self::SCOPE_NS_PREFIX) . '.' . $scopeConfig[XIDE_Scope::SCOPE_NAME] . '.relative';
                $scopes[]=new XIDE_Scope($scopeConfig);
            }
            xo_set(self::SCOPES,$scopes);
        }
    }

}


?>