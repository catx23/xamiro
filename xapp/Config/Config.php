<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Config.Interface');
xapp_import('xapp.Config.Exception');

/**
 * Config class
 *
 * @package Config
 * @class Xapp_Config
 * @error 124
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Config implements Xapp_Config_Interface
{
    /**
     * contains all none global/default config entries
     *
     * @var array
     */
    private $_config = array();

    /**
     * contains the config cache of all non global/default entries for fast read access
     *
     * @var array
     */
    private $_cache = array();

    /**
     * contains array of created config instances
     *
     * @var array
     */
    protected static $_instances = array();

    /**
     * contains the global/default config entries
     *
     * @var array
     */
    protected static $_globalConfig = array();

    /**
     * contains the global/default config cache for fast access
     *
     * @var array
     */
    protected static $_globalCache = array();


    /**
     * class constructor loads config file values from file or buffer of file as single value or array of multiple
     * values of the same. use concrete config to instantiate this class so callee can be determined properly e.g.
     * new Xapp_Conf.Php instead of new Xapp_Conf to make sure your config files are stores in the right instance
     *
     * @see Xapp_Config::create
     * @error 12401
     * @param string|mixed|array $config value according to concrete load implementation of subclasses
     */
    public function __construct($config)
    {
        $class = get_class($this);
        if(is_array($config) && array_key_exists(0, $config))
        {
            foreach($config as $c)
            {
                $this->_config = array_merge_recursive($this->_config, (array)$class::load($c));
            }
        }else{
            $this->_config = (array)$class::load($config);
        }
    }


    /**
     * use the static create function to create a config instance of subclass implementation for
     * config handling. using the static method will ensure static access at any point of the application
     * or script. there are two ways of using config handling with namespace and public/protected access
     * 1)   use namespace "global" or leave ns blank. by doing so all loaded config structures (key => value pairs) are loaded
     *      into the same global public available namespace array. all values are shared and can be set/get by anyone.
     *      this is the default behaviour
     * 2)   use your own namespace. by doing so access to config values (set/get) is only possible if you know the namespace
     *      identifier since all config values are stored in protected array only for that namespace. access is not possible
     *      unless you know the namespace
     * If you pass the third parameter a config structure type hint is expected, e.g. json to load config with json loader
     * multiple config values can be passes in first parameter array merging multiple config files/buffers. NOTE: when using
     * multiple config values use different keys since in merging array keys will be merged!
     *
     * @error 12402
     * @param string|array|mixed $config expects config structure as buffer, file pointer or any other readable value or array of multiple of the same
     * @param string $ns expects the namespace as explained above
     * @param null|string $type expects optional config type hint
     * @return array|null
     * @throws Xapp_Config_Exception
     */
    public static function create($config, $ns = 'global', $type = null)
    {
        if($type != null)
        {
            $type = strtolower(trim($type));
        }else{
            if(is_array($config))
            {
                $type = 'php';
            }else if((bool)preg_match('/\.([a-z0-9]{2,4})$/i', $config, $m)){
                $type = strtolower(trim($m[1]));
            }else{
                $type = self::detect($config);
            }
        }
        $class = __CLASS__ . '_' . $type;
        if(class_exists($class, true))
        {
            if(strtolower($ns) === 'global')
            {
                if(array_key_exists(0, $config) && is_array($config[0]))
                {
                    foreach($config as $c)
                    {
                        self::$_globalConfig = array_merge_recursive(self::$_globalConfig, (array)$class::load($c));
                    }
                }else{
                    self::$_globalConfig = (array)$class::load($config);
                }
                return self::$_globalConfig;
            }else{
                if(!array_key_exists($ns, self::$_instances))
                {
                    self::$_instances[$ns] = new $class($config);
                }
                return self::$_instances[$ns];
            }
        }else{
            throw new Xapp_Config_Exception(xapp_sprintf(_("config class: %s does not exist"), $class), 1240201);
        }
    }


    /**
     * check if config (instance) has been created yet either with global ns or individual namespace
     *
     * @error 12418
     * @return bool
     */
    public static function created()
    {
        return (sizeof(self::$_instances) > 0 || sizeof(self::$_globalConfig) > 0) ? true : false;
    }


    /**
     * tries to detect type of passed value for loading type driver. returns
     * type driver or throws exception if unable to determine
     *
     * @error 12403
     * @param  mixed $mixed expects value to determine type
     * @return string
     * @throws Xapp_Config_Exception
     */
    protected static function detect($mixed)
    {
        if(is_array($mixed))
        {
            return 'php';
        }else if((bool)@json_decode($mixed)){
            return 'json';
        }else if((bool)@simplexml_load_string($mixed)){
            return 'xml';
        }else if((bool)@parse_ini_string($mixed)){
            return 'ini';
        }else{
            throw new Xapp_Config_Exception(_("unable to determine config type for passed value"), 1240301);
        }
    }


    /**
     * retrieve config or config value  for static created config namespaces by passing namespace and optional key. if
     * key is not set the namespace config array will be returned as a whole. the third argument returns the default
     * value in case key is not found in config. the third argument can also be a throwable exception
     *
     * @error 12404
     * @param string $ns expects the namespace
     * @param null|string $key expects the optional key to get value for
     * @param null|mixed|Exception $default expects optional default value if namespace/key is not found
     * @return null|mixed
     * @throws Exception
     */
    public static function retrieve($ns, $key = null, $default = null)
    {
        if(!array_key_exists($ns, self::$_globalCache))
        {
            $cache = self::$_globalCache[$ns] = array();
        }else{
            $cache = self::$_globalCache[$ns];
        }
        if($ns === 'global')
        {
            return self::toObject(self::out(self::$_globalConfig, $key, $default, $cache));
        }else if(isset(self::$_instances[$ns])){
            return self::toObject(self::out(self::$_instances[$ns]->_config, $key, $default, $cache));
        }else{
            return xapp_default($default);
        }
    }


    /**
     * append a key > value pair to a static create config instance and namespace passing
     * namespace and key.
     *
     * @error 12405
     * @param string $ns expects the namespace
     * @param string $key expects the key for value
     * @param null|mixed $val expects the value for key
     * @return void
     */
    public static function append($ns, $key, $val = null)
    {
        if($ns === 'global')
        {
            self::in(self::$_globalConfig, $key, $val);
        }else{
            self::in(self::$_instances[$ns]->_config, $key, $val);
        }
    }


    /**
     * sweep/reset a namespaces or the global namespace
     *
     * @error 12406
     * @param null|string $ns expects the namespace to sweep
     * @return void
     */
    public static function sweep($ns = null)
    {
        if($ns === null || $ns === 'global')
        {
            self::$_globalConfig = array();
            self::$_globalCache = array();
        }else if(isset(self::$_instances[$ns])){
            self::$_instances[$ns]->_config = array();
            self::$_instances[$ns]->_cache = array();
        }
    }


    /**
     * set a key => value pair to none static config instance
     *
     * @error 12407
     * @param string $key expects the key for value
     * @param null|mixed $val expects the value for key
     * @return Xapp_Config
     */
    public function set($key, $val = null)
    {
        self::in($this->_config, $key, $val);
        return $this;
    }


    /**
     * get config or config value if key is set. returns default value
     * if key is not found
     *
     * @error 12408
     * @param null|string $key expects the optional config key
     * @param null|mixed $default expects the default return value
     * @return array|mixed
     */
    public function get($key = null, $default = null)
    {
        if($key !== null)
        {
            return self::toObject(self::out($this->_config, $key, $default, $this->_cache));
        }else{
            return self::toObject($this->_config);
        }
    }


    /**
     * checks whether the config key for none static config instance exists
     *
     * @error 12409
     * @param string $key expects the key to check for
     * @return bool
     */
    public function has($key)
    {
        return ((bool)self::out($this->_config, $key, false, $this->_cache)) ? true : false;
    }


    /**
     * inject key => value pair into first parameter array. key => value pairs can be passed
     * as single array in second parameter $key or as key => value pair over second and third
     * parameter
     *
     * @error 12410
     * @param array $array expects the array to inject key into
     * @param string|array $key expects key as string or key => value pairs
     * @param null|mixed $val expects optional value
     * @return void
     */
    protected static function in(Array &$array, $key, $val = null)
    {
        if(is_array($key) && $val === null)
        {
            //do nothing
        }else{
            $key = array($key => $val);
        }
        foreach($key as $k => $v)
        {
            xapp_array_set($array, $k, $v);
        }
    }


    /**
     * retrieve value from array by keys with dot notation e.g. config.database.user is equivalent
     * to a multidimensional array with same keys. pass a cache array in last parameter if there
     * is already a pre compiled cache array with array keys in dot notation. if so will retrieve
     * from cache array instead from first array which is needs to iterated until finally key
     * value is found. if no key is passed will return complete array
     *
     * @error 12411
     * @param array $array expects the original array to look for key
     * @param null|string $key expects the optional key to get from array
     * @param null|mixed $default expects optional returnable default value if key is not found
     * @param null|array $cache expects optional cache array for dot notation key => value pairs
     * @return array|null
     */
    protected static function out(Array $array, $key = null, $default = null, Array &$cache = null)
    {
        if($key === null)
        {
            return $array;
        }
        if($cache !== null && array_key_exists($key, $cache))
        {
            return $cache[$key];
        }
        if($cache !== null)
        {
            return $cache[$key] = xapp_array_get($array, $key, xapp_default($default));
        }else{
            return xapp_array_get($array, $key, xapp_default($default));
        }
    }


    /**
     * convert array to stdClass object. returns first argument directly if not array
     *
     * @error 12412
     * @param null|mixed|array $data expects array to convert or any other none array value
     * @return null|mixed|object
     */
    protected static function toObject($data = null)
    {
        if(is_array($data))
        {
            if(array_keys($data) === range(0, count($data) - 1))
            {
                return $data;
            }else{
                return (object)array_map(array(__CLASS__, 'toObject'), $data);
            }
        }else{
            return $data;
        }
    }


    /**
     * convert stdClass object to array. returns first argument directly if not object
     *
     * @error 12413
     * @param null|mixed|stdClass $data expects object to convert or any none object value
     * @return null|mixed|array
     */
    public static function toArray($data = null)
    {
        if(is_object($data))
        {
            $data = get_object_vars($data);
        }
        if(is_array($data))
        {
            return array_map(array(__CLASS__, 'toArray'), $data);
      	}else{
            return $data;
      	}
    }


    /**
     * reset the class none static config array
     *
     * @error 12414
     * @return void
     */
    public function reset()
    {
        $this->_config = array();
    }


    /**
     * when going to sleep let only the instance config array
     * survive. discard the cache
     *
     * @error 12415
     * @return array
     */
    public function ___sleep()
    {
        $this->_cache = array();
        return array('_config');
    }


    /**
     * clear cache on wakeup
     *
     * @error 12416
     * @return void
     */
    public function __wakeup()
    {
        $this->_cache = array();
    }


    /**
     * dont allow cloning!
     *
     * @error 12417
     * @return void
     */
    protected function __clone(){}
}