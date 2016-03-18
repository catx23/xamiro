<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');


/**
 * Cache driver base class
 *
 * @package Cache
 * @class Xapp_Cache_Driver
 * @error 154
 * @author Frank Mueller
 */
abstract class Xapp_Cache_Driver extends Xapp_Cache implements Xapp_Singleton_Interface
{
    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
    );



    /**
     * init concrete driver cache implementation
     *
     * @return void
     */
    abstract protected function init();


    /**
     * get cache key
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    abstract public function get($key, $default = null);


    /**
     * set cache key
     *
     * @param $key
     * @param $value
     * @param null $lifetime
     * @return mixed
     */
    abstract public function set($key, $value, $lifetime = null);


    /**
     * check if cache key exists
     *
     * @param $key
     * @return bool
     */
    abstract public function has($key);


    /**
     * remove key from cache
     *
     * @param $key
     * @return bool
     */
    abstract public function forget($key);


    /**
     * purge/flush single key or all keys
     *
     * @param bool $expired
     * @return void
     */
    abstract public function purge($expired = true);


    /**
     * default class constructor for all driver implementations that need no
     * special constructor. concrete driver implementations should always call
     * parent constructor
     *
     * @error 15401
     * @param null|mixed $options expects optional xapp option array or object
     */
    protected function __construct($options = null)
    {
        xapp_init_options($options, $this);
        $this->init();
    }


    /**
     * make and return timestamp for now or now + x seconds if second parameter is not null
     *
     * @error 15402
     * @param null|int $seconds expects additional value of seconds to add to timestamp now
     * @return int
     */
    protected function timestamp($seconds = null)
    {
        if($seconds !== null)
        {
            return time() + (int)$seconds;
        }else{
            return time();
        }
    }


    /**
     * overload class by setting cache key => value pair with default lifetime value
     *
     * @error 15403
     * @param string $name expects the key name
     * @param null|mixed $value expects the value to set
     * @return null|mixed
     */
    public function __set($name, $value = null)
    {
        if($value === null)
        {
            return null;
        }
        return $this->set($name, $value);
    }


    /**
     * overload class by getting value for cache key. will return null if the key does
     * not exist.
     *
     * @error 15404
     * @param string $name expects the key name
     * @return null|mixed
     */
    public function __get($name)
    {
        if($this->has($name))
        {
            return $this->get($name);
        }else{
            return null;
        }
    }


    /**
     * overload class by checking for existing cache key returning boolean value
     *
     * @error 15405
     * @param string $name expects the key name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }


    /**
     * overload class by un setting cached entry with key name in first parameter
     *
     * @error 15406
     * @param string $name expects the key name
     * @return bool
     */
    public function __unset($name)
    {
        return $this->forget($name);
    }
}