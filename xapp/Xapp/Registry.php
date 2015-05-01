<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Xapp.Error');

/**
 * Registry class
 *
 * @package Registry
 * @class Xapp_Registry
 * @error 111
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Registry implements Xapp_Singleton_Interface
{
    /**
     * constant for define the default registry namespace
     *
     * @const DEFAULT_NS
     */
    const DEFAULT_NS        = 'REGISTRY_DEFAULT_DOMAIN';


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::DEFAULT_NS    => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::DEFAULT_NS    => 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::DEFAULT_NS    => 'global'
    );

    /**
     * contains the singleton instance created with create function
     *
     * @var null|Xapp_Registry
     */
    protected static $_instance = null;

    /**
     * contains all registry entries as key => value pairs
     *
     * @var array
     */
    private $_registry = array();


    /**
     * class constructor creates instance and inits options
     *
     * @error 11101
     * @param null|mixed $options expects valid option object
     */
    public function __construct($options = null)
    {
        xapp_init_options($options, $this);
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


    /**
     * get value from registry by key for the passed namespace ns or
     * get whole registry for ns if key is passed null
     *
     * @error 11103
     * @param null|string $key expects optional key to retrieve value for
     * @param null|string $ns expects the optional ns
     * @return null|mixed value for key
     * @throws Xapp_Error
     */
    public static function get($key = null, $ns = null)
    {
        $self = self::instance();
        if($ns !== null)
        {
            $ns = strtolower(trim((string)$ns));
        }else{
            $ns = xapp_get_option(self::DEFAULT_NS, $self);
        }
        if(isset($self->_registry[$ns]))
        {
            if($key !== null && array_key_exists($key, $self->_registry[$ns]))
            {
                return $self->_registry[$ns][$key];
            }else{
                return $self->_registry[$ns];
            }
        }else{
            throw new Xapp_Error(xapp_sprintf(_("unable to get registry since namespace: %s does not exist"), $ns), 1110301);
        }
    }


    /**
     * sets value to registry with key for value and optional namespace
     *
     * @error 11104
     * @param string $key expects the key name
     * @param null|mixed $val expects the value to store in registry
     * @param null|string $ns expects optional namespace
     * @return array current registry array
     */
    public static function set($key, $val = null, $ns = null)
    {
        $tmp = null;

        $self = self::instance();
        if($ns !== null)
        {
            $ns = strtolower(trim((string)$ns));
        }else{
            $ns = xapp_get_option(self::DEFAULT_NS, $self);
        }
        if(!isset($self->_registry[$ns]))
        {
            $self->_registry[$ns] = array();
        }
        if(!is_array($key))
        {
            return $self->_registry[$ns][$key] = $val;
        }else{
            if(is_array($key) && is_array($val) && sizeof($key) === sizeof($val))
            {
                $tmp = array_combine($key, $val);
            }else if(is_array($key) && $val === null){
                $tmp = $key;
            }
            foreach($tmp as $k => $v)
            {
                $self->_registry[$ns][$k] = $v;
            }
        }
        return $self->_registry[$ns];
    }


    /**
     * checks whether a key exists in registry or not
     *
     * @error 11105
     * @param null|string $key expects the key to check for
     * @param null|string $ns expects the optional namespace
     * @return bool
     */
    public static function has($key = null, $ns = null)
    {
        if($key !== null)
        {
            $self = self::instance();
            if($ns !== null)
            {
                $ns = strtolower(trim((string)$ns));
            }else{
                $ns = xapp_get_option(self::DEFAULT_NS, $self);
            }
            if(isset($self->_registry[$ns]) && array_key_exists($key, $self->_registry[$ns]))
            {
                return true;
            }
        }
        return false;
    }
	/**
	 * Return the entire namespace
	 * @error 11103
	 * @param null|string $ns expects the optional ns otherwise default registry will be used.
	 * @return array
	 * @throws Xapp_Error
	 */
	public static function getAll($ns = null)
	{
		$self = self::instance();
		if($ns !== null)
		{
			$ns = strtolower(trim((string)$ns));
		}else{
			$ns = xapp_get_option(self::DEFAULT_NS, $self);
		}
		if(isset($self->_registry[$ns]))
		{
			return $self->_registry[$ns];
		}else{
			throw new Xapp_Error("unable to get registry since namespace: $ns does not exist", 1110301);
		}
	}

    /**
     * unregister either key from registry or complete namespace. if first parameter $key
     * is null will unset passed namespace or reset default namespace.
     *
     * @error 11105
     * @param null|string $key expects optional registry key
     * @param null|string $ns expects optional namespace
     * @return void
     */
    public static function unregister($key = null, $ns = null)
    {
        $self = self::instance();
        if($ns !== null)
        {
            $ns = strtolower(trim((string)$ns));
        }else{
            $ns = xapp_get_option(self::DEFAULT_NS, $self);
        }
        if($key !== null)
        {
            if(self::has($key, $ns))
            {
                unset($self->_registry[$ns][$key]);
            }
        }else{
            if($ns === xapp_get_option(self::DEFAULT_NS, $self))
            {
                $self->_registry[$ns] = array();
            }else{
                if(array_key_exists($ns, $self->_registry))
                {
                    unset($self->_registry[$ns]);
                }
            }
        }
    }
}