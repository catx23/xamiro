<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Cache.Exception');

/**
 * Cache base class
 *
 * @package Cache
 * @class Xapp_Cache
 * @error 153
 * @author Frank Mueller
 */
abstract class Xapp_Cache
{
    /**
     * cache option value default expiration when cache key expires after
     *
     * @const DEFAULT_EXPIRATION
     */
    const DEFAULT_EXPIRATION            = 'XAPP_CACHE_DEFAULT_EXPIRATION';


    /**
     * contains the current active last created instance either with instance or factory
     * method
     *
     * @var null|Xapp_Cache
     */
    protected static $_instance = null;

    /**
     * contains all singleton instances defined by a names space string identifier create
     * by instance method
     *
     * @var array
     */
    protected static $_instances = array();

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::DEFAULT_EXPIRATION        => XAPP_TYPE_INT
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::DEFAULT_EXPIRATION        => 1
    );



    /**
     * get and create static driver instance. if the first parameter is null will try to get
     * the current active instance created regardless of the driver. will throw error if no instance has
     * been created yet. if the second parameter is a driver string like "file" or "apc" will check
     * if instance has already been created and if not will do so with the passed options in second
     * parameter. this method is the preferred way to create cache instances when using multiple instances
     * with different namespaces and/or driver since the factory method will create an instance but not get
     * it at a later stage. the first parameter can be null meaning no namespace identifier which equals to
     * current instance - should only be used when using one instance!
     *
     * @error 15301
     * @param null|string $ns expects optional instance namespace identifier
     * @param null|string $driver expects the cache driver string
     * @param null|mixed $options expects xapp option array or object
     * @return Xapp_Cache_Driver concrete xapp cache driver implementation instance
     * @throws Xapp_Cache_Exception
     */
    public static function instance($ns = null, $driver = null, $options = null)
    {
        if(func_num_args() > 0)
        {
            //setting
            if($driver !== null)
            {
                self::factory($driver, $options, $ns);
            }
            //getting
            if($ns !== null)
            {
                if(array_key_exists($ns, self::$_instances))
                {
                    return self::$_instances[trim((string)$ns)];
                }else{
                    throw new Xapp_Cache_Exception(xapp_sprintf(_("no cache instance under ns: %s registered"), $ns), 1530102);
                }
            }else{
                return self::$_instance;
            }
        }else{
            //getting
            if(self::$_instance !== null)
            {
                return self::$_instance;
            }else{
                throw new Xapp_Cache_Exception(_("can not get current cache class instance since no instance has been set yet"), 1530101);
            }
        }
    }


    /**
     * factory method for creating cache driver instances. this method is the only way, besides the instance method,
     * to create cache driver instances since concrete driver classes dont permit instantiation directly.
     * first parameter expects the driver string like "file" or "apc" and the second parameter
     * an driver options xapp array or option object. the third parameter can contain a namespace
     * identifier when if set will allow for unlimited instance creation. will throw error if
     * the driver does not exist
     *
     * @error 15302
     * @param string $driver expects the cache driver string
     * @param null|mixed $options expects xapp option array or object
     * @param null|string $ns expects optional ns string identifier
     * @return Xapp_Cache_Driver concrete xapp cache driver implementation instance
     * @throws Xapp_Cache_Exception
     */
    public static function factory($driver, $options = null, $ns = null)
    {
        $class = __CLASS__ . '_Driver_' . ucfirst(trim((string)$driver));
        if(class_exists($class, true))
        {
            if($ns !== null)
            {
                return self::$_instance = self::$_instances[trim((string)$ns)] = new $class($options);
            }else{
                return self::$_instance = new $class($options);
            }
        }else{
            throw new Xapp_Cache_Exception("cache driver: $driver does not exist", 1530201);
        }
    }


    /**
     * check if the cache class has > 0 instance registered or not returning boolean false if not
     * and boolean true if so
     *
     * @error 15303
     * @return bool
     */
    public static function hasInstance()
    {
        return ((self::$_instance !== null) ? true : false);
    }


    /**
     * create a hash from a string using the algorithm passed in second parameter
     *
     * @error 15304
     * @param string $string expects the string to hash
     * @param string $algo expects a valid hash algorithm
     * @return string
     */
    public static function hash($string, $algo = 'sha1')
    {
        return hash(strtolower(trim((string)$algo)), trim((string)$string));
    }


    /**
     * call static abstract driver functions like "get", "set" with the valid namespace, driver or current
     * driver instance. if the first parameter is a namespace identifier and ns value is found in array will
     * get the instance stored under the ns. if the first parameter is a driver name string will try to get
     * the instance for that. if no ns or driver name used will use last current instance. NOTE: keys should
     * never be a driver or ns name!
     *
     * @error 15305
     * @param string $method expects the function name
     * @param array $parameters expects the overloading parameters
     * @return mixed
     * @throws Xapp_Cache_Exception
     */
    public static function __callStatic($method, $parameters)
   	{
        $instance = null;

        if(sizeof($parameters >= 2) && array_key_exists((string)$parameters[0], self::$_instances))
        {
            $instance = self::$_instances[(string)$parameters[0]];
            $parameters = array_slice($parameters, 1);
        }else{
            $instance = self::$_instance;
        }
        if($instance !== null)
        {
            if(method_exists($instance, $method))
            {
                return call_user_func_array(array($instance, $method), $parameters);
            }else{
                throw new Xapp_Cache_Exception("method: $method can not be called statically", 1530501);
            }
        }else{
            throw new Xapp_Cache_Exception("no instance found for static cache class overloading", 1530502);
        }
   	}


    /**
     * dont allow cloning!
     *
     * @error 15305
     * @return void
     */
    protected function __clone(){}
}