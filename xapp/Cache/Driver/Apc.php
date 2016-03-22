<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Cache.Driver');
xapp_import('xapp.Cache');
xapp_import('xapp.Cache.Driver.Exception');
xapp_import('xapp.Cache.Exception');

/**
 * Cache driver file class
 *
 * @package Cache
 * @subpackage Cache_Driver
 * @class Xapp_Cache_Driver_Apc
 * @error 160
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Cache_Driver_Apc extends Xapp_Cache_Driver
{
    const KEY_PREFIX                = 'XAPP_CACHE_DRIVER_APC_KEY_PREFIX';

    /**
     * contains the singleton instance for this class
     *
     * @var null|Xapp_Cache_Driver_Apc
     */
    protected static $_instance = null;


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::KEY_PREFIX            => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::KEY_PREFIX            => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::KEY_PREFIX            => '',
        self::DEFAULT_EXPIRATION    => 60
    );



    /**
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 16001
     * @param null|mixed $options expects optional xapp option array or object
     * @return Xapp_Cache_Driver_Apc
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
     * init apc instance by checking for apc extension
     *
     * @error 16101
     * @throws Xapp_Cache_Driver_Exception
     * @returns void
     */
    protected function init()
    {
        if(!extension_loaded('apc'))
        {
            throw new Xapp_Cache_Driver_Exception(_("apc extension is not supported by this system"), 1610101);
        }
    }


    /**
     * get value for cache key returning default value if key does not exist or is expired
     *
     * @error 16202
     * @param string $key expects the cache key name as string
     * @param null|mixed $default expects the default return value if cache key does not exist anymore
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if(($value = apc_fetch(xapp_get_option(self::KEY_PREFIX, $this) . $key)) !== false)
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
     * @error 16203
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
        }
        return (bool)apc_store(xapp_get_option(self::KEY_PREFIX, $this) . $key, $value, $lifetime);
    }


    /**
     * check if cache key still exists or has been purged already returning boolean value
     *
     * @error 16204
     * @param string $key expects the cache key name as string
     * @return bool
     */
    public function has($key)
    {
        return (bool)apc_exists(xapp_get_option(self::KEY_PREFIX, $this) . $key);
    }


    /**
     * remove cache key returning boolean value
     *
     * @error 16205
     * @param string $key expects the cache key name as string
     * @return bool
     */
    public function forget($key)
    {
        return (bool)apc_delete(xapp_get_option(self::KEY_PREFIX, $this) . $key);
    }


    /**
     * flush all key values which are left in key store
     *
     * @error 16206
     * @param bool $expired not used for apc implementation
     * @return bool
     */
    public function purge($expired = true)
    {
        return apc_clear_cache();
    }
}