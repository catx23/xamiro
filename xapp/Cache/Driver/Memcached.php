<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Cache.Driver');
xapp_import('xapp.Cache');
xapp_import('xapp.Cache.Driver.Exception');
xapp_import('xapp.Cache.Exception');

/**
 * Cache driver memcached class
 *
 * @package Cache
 * @subpackage Cache_Driver
 * @class Xapp_Cache_Driver_Memcached
 * @error 158
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Cache_Driver_Memcached extends Xapp_Cache_Driver
{
    /**
     * option to pass a valid and instantiated php memcached instance that will be used instead of
     * having this class instantiate the memcached instance. must contain all server and options
     * needed!
     *
     * @const INSTANCE
     */
    const INSTANCE                  = 'XAPP_CACHE_DRIVER_MEMCACHED_INSTANCE';

    /**
     * a single server object either as array with key => value pairs or a instance of
     * Xapp_Cache_Driver_Memcached_Server with includes all needed properties which are:
     * "host", "port" and optional "weight parameter. to add multiple servers either pass an
     * array with multiple Xapp_Cache_Driver_Memcached_Server instances or an array with multiple
     * arrays containing the parameters
     *
     * @const SERVER
     */
    const SERVER                    = 'XAPP_CACHE_DRIVER_MEMCACHED_SERVER';

    /**
     * option to contain php memcached options as explained here: http://php.net/manual/en/memcached.constants.php
     * must be passed as key => value array like you would do with memcached::setOptions
     *
     * @const OPTIONS
     */
    const OPTIONS                   = 'XAPP_CACHE_DRIVER_MEMCACHED_OPTIONS';

    /**
     * option to set a persistent id or key that will survive a single request. see memcached class
     * constructor for more
     *
     * @const PERSISTENT_ID
     */
    const PERSISTENT_ID             = 'XAPP_CACHE_DRIVER_MEMCACHED_PERSISTENT_ID';

    /**
     * option to define a key prefix the will be added to cache key value automatically
     *
     * @const KEY_PREFIX
     */
    const KEY_PREFIX                = 'XAPP_CACHE_DRIVER_MEMCACHED_KEY_PREFIX';

    /**
     * option to activate memcached item compression. will overwrite the OPT_COMPRESSION value if set
     * in OPTIONS parameter
     *
     * @const COMPRESS
     */
    const COMPRESS                  = 'XAPP_CACHE_DRIVER_MEMCACHED_COMPRESS';

    /**
     * option to define memcached connection timeout. will overwrite the OPT_CONNECT_TIMEOUT value if
     * set in option parameter
     *
     * @const CONNECTION_TIMEOUT
     */
    const CONNECTION_TIMEOUT        = 'XAPP_CACHE_DRIVER_MEMCACHED_CONNECTION_TIMEOUT';


    /**
     * contains the singleton instance for this class
     *
     * @var null|Xapp_Cache_Driver_Memcached
     */
    protected static $_instance = null;


    /**
     * contains the memcached instance created for this cache instance
     *
     * @var null
     */
    protected $_memcached = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::INSTANCE              => 'Memcached',
        self::SERVER                => array('Xapp_Cache_Driver_Memcached_Server', XAPP_TYPE_ARRAY),
        self::OPTIONS               => XAPP_TYPE_ARRAY,
        self::PERSISTENT_ID         => XAPP_TYPE_STRING,
        self::KEY_PREFIX            => XAPP_TYPE_STRING,
        self::COMPRESS              => XAPP_TYPE_BOOL,
        self::CONNECTION_TIMEOUT    => XAPP_TYPE_INT
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::INSTANCE              => 0,
        self::SERVER                => 1,
        self::OPTIONS               => 0,
        self::PERSISTENT_ID         => 0,
        self::KEY_PREFIX            => 1,
        self::COMPRESS              => 1,
        self::CONNECTION_TIMEOUT    => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::DEFAULT_EXPIRATION    => 60,
        self::KEY_PREFIX            => '',
        self::COMPRESS              => false,
        self::OPTIONS               => array(),
        self::CONNECTION_TIMEOUT    => 15
    );



    /**
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15701
     * @param null|mixed $options expects optional xapp option array or object
     * @return Xapp_Cache_Driver_Memcached
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
     * init instance by checking for memcached extension and validating server options
     *
     * @error 15801
     * @return void
     * @throws Xapp_Cache_Driver_Exception
     */
    protected function init()
    {
        if(!extension_loaded('memcached'))
        {
            throw new Xapp_Cache_Driver_Exception(_("memcached is not supported by this system"), 1580101);
      	}
        if(xapp_is_option(self::COMPRESS, $this))
        {
            xapp_set_option(self::OPTIONS, array(Memcached::OPT_COMPRESSION => xapp_get_option(self::COMPRESS, $this)), $this);
        }
        if(xapp_is_option(self::CONNECTION_TIMEOUT, $this))
        {
            xapp_set_option(self::OPTIONS, array(Memcached::OPT_CONNECT_TIMEOUT => xapp_get_option(self::CONNECTION_TIMEOUT, $this)), $this);
        }
        if(xapp_is_option(self::INSTANCE, $this))
        {
            $this->_memcached = xapp_get_option(self::INSTANCE, $this);
        }else{
            if(xapp_is_option(self::PERSISTENT_ID, $this))
            {
                $this->_memcached = new Memcached(xapp_get_option(self::PERSISTENT_ID, $this));
            }else{
                $this->_memcached = new Memcached();
            }
            if(xapp_is_option(self::OPTIONS, $this))
            {
                foreach(xapp_get_option(self::OPTIONS, $this) as $k => $v)
                {
                    if(!$this->_memcached->setOption((int)$k, $v))
                    {
                        throw new Xapp_Cache_Driver_Exception(xapp_sprintf(_("unable to set memcached option: %s"), $k), 1580102);
                    }
                }
            }
            $server = xapp_get_option(self::SERVER, $this);
            if(is_object($server))
            {
                xapp_set_option(self::SERVER, array($server), $this, true);
            }else if(is_array($server) && !array_key_exists(0, $server)){
                xapp_set_option(self::SERVER, array($server), $this, true);
            }
            foreach((array)xapp_get_option(self::SERVER, $this) as $server)
            {
                $server = (array)$server;
                if(!array_key_exists('host', $server))
                {
                    throw new Xapp_Cache_Driver_Exception(_("memcached parameter: host must be set"), 1580103);
                }
                if(!array_key_exists('port', $server))
                {
                    throw new Xapp_Cache_Driver_Exception(_("memcached parameter: port must be set"), 1580104);
                }
                if(!$this->_memcached->addServer($server['host'], (int)$server['port'], ((array_key_exists('weight', $server)) ? (int)$server['weight'] : 0)))
                {
                    throw new Xapp_Cache_Driver_Exception(xapp_sprintf(_("unable to add server for host: %s and port: %d"), array($server['host'], $server['port'])), 1580105);
                }
            }
        }
    }


    /**
     * get value for cache key returning default value if key does not exist or is expired
     *
     * @error 15802
     * @param string $key expects the cache key name as string
     * @param null|mixed $default expects the default return value if cache key does not exist anymore
     * @return null|mixed
     */
    public function get($key, $default = null)
    {
        if(($value = $this->_memcached->get(xapp_get_option(self::KEY_PREFIX, $this) . (string)$key)) !== false)
        {
            return $value;
        }else{
            return xapp_default($default);
        }
    }


    /**
     * set value for cache key with optional lifetime value as third parameter. if not set default lifetime
     * will be applied. returns boolean true if success else false
     *
     * @error 15803
     * @param string $key expects the cache key name as string
     * @param mixed $value expects the value to set for cache key
     * @param null|int $lifetime expects the optional lifetime value
     * @return boolean
     */
    public function set($key, $value, $lifetime = null)
    {
        if($lifetime === null)
        {
            $lifetime = xapp_get_option(self::DEFAULT_EXPIRATION, $this);
        }else{
            $lifetime = (int)$lifetime;
        }
        if($lifetime > 0)
        {
            $lifetime = $this->timestamp($lifetime);
        }
        return $this->_memcached->set(xapp_get_option(self::KEY_PREFIX, $this) . (string)$key, serialize($value), $lifetime);
    }


    /**
     * check if cache key still exists or has been purged already returning boolean value
     *
     * @error 15804
     * @param string $key expects the cache key name as string
     * @return bool
     */
    public function has($key)
    {
        return ($this->get($key) !== null) ? true : false;
    }


    /**
     * remove cache key returning boolean value
     *
     * @error 15805
     * @param string $key expects the cache key name as string
     * @return bool
     */
    public function forget($key)
    {
        if($this->has($key))
        {
            return $this->_memcached->delete(xapp_get_option(self::KEY_PREFIX, $this) . (string)$key);
        }else{
            return false;
        }
    }


    /**
     * flush all key values which are left in key store
     *
     * @error 15806
     * @param bool $expired not used for memcached implementation
     * @return bool
     */
    public function purge($expired = true)
    {
        if((bool)$expired)
        {
            return true;
        }else{
            $result = $this->_memcached->flush();
            usleep(100000);
            return $result;
        }
    }


    /**
     * method to set/get memcached instance. when setting will overwrite previously set memcached instance so
     * passed instance should be instantiated will all required options etc.
     *
     * @error 15807
     * @param Memcached $memcached expects optional memcached instance when setting
     * @return Memcached|null
     */
    public function memcached(Memcached $memcached = null)
    {
        if($memcached !== null)
        {
            return $this->_memcached = xapp_set_option(self::INSTANCE, $memcached, $this, true);
        }else{
            return $this->_memcached;
        }
    }
}