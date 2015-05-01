<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
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
 * @class Xapp_Cache_Driver_Filer
 * @error 155
 * @author Frank Mueller
 */
class Xapp_Cache_Driver_File extends Xapp_Cache_Driver
{
    /**
     * file driver option cache directory must be a valid path and passed with instance
     *
     * @const PATH
     */
    const PATH                      = 'XAPP_CACHE_DRIVER_FILE_PATH';

    /**
     * file driver option default cache file extension must a valid file extension value
     *
     * @const CACHE_EXTENSION
     */
    const CACHE_EXTENSION           = 'XAPP_CACHE_DRIVER_FILE_CACHE_EXTENSION';


    /**
     * contains the singleton instance for this class
     *
     * @var null|Xapp_Cache_Driver_File
     */
    protected static $_instance = null;


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::PATH                  => XAPP_TYPE_DIR,
        self::CACHE_EXTENSION       => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::PATH                  => 1,
        self::CACHE_EXTENSION       => 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::DEFAULT_EXPIRATION    => 60,
        self::CACHE_EXTENSION       => 'cache'
    );


    /**
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return Xapp_Cache_Driver_File
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
     * init class instance by validating cache directory for being readable and writable
     *
     * @error 15502
     * @return void
     * @throws Xapp_Cache_Driver_Exception
     */
    protected function init()
    {
        try
        {
            $dir = new SplFileInfo(xapp_get_option(self::PATH, $this));
            if(!$dir->isReadable())
            {
                throw new Xapp_Cache_Driver_Exception(_("cache directory is not readable"), 1550201);
            }
            if(!$dir->isWritable())
            {
                throw new Xapp_Cache_Driver_Exception(_("cache directory is not writable"), 1550202);
            }
            xapp_set_option(self::PATH, rtrim($dir->getRealPath(), DS) . DS, $this);
      	}
        catch(Exception $e)
        {
            throw new Xapp_Cache_Driver_Exception(xapp_sprintf(_("cache directory file info error: %d, %s"), $e->getCode(), $e->getMessage()), 1550203);
        }
    }


    /**
     * get value for cache key returning default value if key does not exist or is expired
     *
     * @error 15503
     * @param string $key expects the cache key name as string
     * @param null|mixed $default expects the default return value if cache key does not exist anymore
     * @return null|mixed
     * @throws Xapp_Cache_Driver_Exception
     */
    public function get($key, $default = null)
    {
        if($this->has($key))
        {
            if(($value = file_get_contents(xapp_get_option(self::PATH, $this) . $this->filename($key))) !== false)
            {
                if(time() >= (int)substr($value, 0, 10))
                {
                    $this->forget($key);
                    return xapp_default($default);
                }else{
                    return unserialize(substr(trim($value), 10));
                }
            }else{
                throw new Xapp_Cache_Driver_Exception(_("unable to read content from cache file"), 1550301);
            }
        }else{
            return xapp_default($default);
        }
    }


    /**
     * set value for cache key with optional lifetime value as third parameter. if not set default lifetime
     * will be applied. returns value written
     *
     * @error 15504
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
        return (bool)file_put_contents(xapp_get_option(self::PATH, $this) . $this->filename($key), $this->timestamp($lifetime) . serialize($value), LOCK_EX);
    }


    /**
     * check if cache key file still exists or has been remove already returning boolean value
     *
     * @error 15505
     * @param string $key expects the cache key name as string
     * @return bool
     */
    public function has($key)
    {
        return (file_exists(xapp_get_option(self::PATH, $this) . $this->filename($key)));
    }


    /**
     * remove cache key file returning boolean value from file unlink function
     *
     * @error 15506
     * @param string $key expects the cache key name as string
     * @return bool
     */
    public function forget($key)
    {
        if($this->has($key))
        {
            return unlink(xapp_get_option(self::PATH, $this) . $this->filename($key));
        }else{
            return false;
        }
    }


    /**
     * purge/remove only already expired cache entries if first parameter is set to true. if set
     * to false will remove all cache key files independent of expiration value!
     *
     * @error 15507
     * @param bool $expired expects boolean value for delete mode like explain above
     * @return void
     */
    public function purge($expired = true)
    {
        if(($files = glob(xapp_get_option(self::PATH, $this) . '*')) !== false)
        {
            foreach($files as $f)
            {
                if(is_file($f))
                {
                    if((bool)$expired)
                    {
                        if(($value = file_get_contents($f)) !== false)
                        {
                            if(time() >= substr($value, 0, 10))
                            {
                                unlink($f);
                            }
                        }
                    }else{
                        unlink($f);
                    }
                }
            }
        }
        @clearstatcache();
    }


    /**
     * make and return valid sha1 hashed file name from cache key
     *
     * @error 15508
     * @param string $key expects the cache key name as string
     * @return string
     */
    protected function filename($key)
    {
        if(!preg_match('/^[a-f0-9]{32}$/', $key))
        {
            $key = sha1(trim((string)$key));
        }
        return (string)$key . '.' . trim(xapp_get_option(self::CACHE_EXTENSION, $this), '.');
    }
}