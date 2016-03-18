<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html

 */

/**
 * XApp-Commander-Plugin base class, server side
 *
 * The class provides the minimum functionality like caching, logging and
 * resolving of run time variables.
 *
 * Typically, a plugin should run as a singleton !
 *
 * Remarks :
 *  - A plugin should implement the XApp-Plugin interface :
 *      setup()
 *      load()
 *  - You can pass an existing cache and log driver within the SERVICE_CONF, otherwise
 *    the service configuration will be used to create those logging & cache instances!
 *  - The plugin base class equals the concept of the "fat" interface and therefore
 *    rather "mixes in" common functionality (Horizontal programming). This is similar to
 *    "Traits" in Php5.4
 *
 * @package XApp-Commander\Plugin
 * @class Xapp_Commander_Plugin
 * @author  mc007
 */
class Xapp_Commander_Plugin implements Xapp_Singleton_Interface
{
    /**
     * option to specify a cache config
     *
     * @const BOOTSTRAP_CONF
     */
    const BOOTSTRAP_CONF         = 'XAPP_BOOTSTRAP_CONF';

    /**
     * option to specify a cache config
     *
     * @const CACHE_CONF
     */
    const CACHE_CONF         = 'XAPP_CACHE_CONF';

    /**
     * option to specify logging config
     *
     * @const LOGGING_CONF
     */
    const LOGGING_CONF         = 'XAPP_LOGGING_CONF';

    /**
     * option to specify service config
     *
     * @const SERVICE_CONF
     */
    const SERVICE_CONF         = 'XAPP_SERVICE_CONF';

    /***
     * internal logger or reference to external logger
     */
    var $logger=null;

    /***
     * service configuration shortcut.
     */
    var $serviceConfig=null;

    /***
     * internal cache instance
     */
    var $cache=null;

    /**
     * option to specify a cache namespace
     *
     * @const DEFAULT_NS
     */
    var $CACHE_NS         = 'Xapp_Commander_Plugin_CACHE_NS';

    /***
     * @var null
     */
    public $xfile=null;

	/***
	 * @var XCOM_Directory_Service
	 */
	public $directoryService=null;


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CACHE_CONF       => XAPP_TYPE_ARRAY,
        self::LOGGING_CONF     => XAPP_TYPE_ARRAY,
        self::SERVICE_CONF     => XAPP_TYPE_ARRAY,
        self::BOOTSTRAP_CONF   => XAPP_TYPE_ARRAY

    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CACHE_CONF         => 0,
        self::LOGGING_CONF       => 0,
        self::SERVICE_CONF       => 0,
        self::BOOTSTRAP_CONF     => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::CACHE_CONF            => null,
        self::LOGGING_CONF          => null,
        self::SERVICE_CONF          => null,
        self::BOOTSTRAP_CONF        => null
    );


    /**
     * contains the singleton instance for this class
     *
     * @var null|Xapp_Commander_Plugin
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
     * @return Xapp_Commander_Plugin
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
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
     * @param $message
     * @param string $ns
     * @param bool $stdError
     */
    public function log($message,$ns="",$stdError=true){
        if($this->logger){
            $this->logger->log($ns."::".$message);
        }else{
        }
        if($stdError){
            error_log('Error : '.$message);
        }
    }

    /***
     * @param $logger
     */
    public function _setLogger($logger){
        $this->logger=$logger;
    }

    private function init(){}

    /**
     * IPugin interface impl.
     *
     * setup() must be called before load
     *
     * @error 15404
     * @return integer Returns error code due to the initialization.
     */
    function setup(){

        //extract service configuration

        $this->serviceConfig = xapp_get_option(self::SERVICE_CONF,$this);

        //logging
        if(xapp_is_option(self::LOGGING_CONF, $this) && $this->serviceConfig){
            $logConfig = xapp_get_option(self::SERVICE_CONF);
            if($logConfig && $logConfig[XC_CONF_LOGGER]!=null){
                $this->logger=$logConfig[XC_CONF_LOGGER];
            }else{
                //setup logger
            }
        }


        //cache
        if(xapp_is_option(self::CACHE_CONF, $this) && $this->serviceConfig){
            $cacheConfig = xapp_get_option(self::CACHE_CONF);
            if($cacheConfig){
                $this->cache = Xapp_Cache::instance($this->CACHE_NS,"file",array(
                    Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$this->serviceConfig),
                    Xapp_Cache_Driver_File::CACHE_EXTENSION=>$this->CACHE_NS,
                    Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>200
                ));
            }
        }
    }

    /**
     * IPugin interface impl.
     *
     * load() does plugin specific 3th party imports
     *
     * @error 15404
     * @return integer Returns error code due to the initialization.
     */
    function load(){}

}