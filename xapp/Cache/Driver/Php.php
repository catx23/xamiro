<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Cache.Driver');
xapp_import('xapp.Cache');
xapp_import('xapp.Cache.Driver.Exception');
xapp_import('xapp.Cache.Exception');

/**
 * Cache driver php class
 *
 * @package Cache
 * @subpackage Cache_Driver
 * @class Xapp_Cache_Driver_Php
 * @error 162
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Cache_Driver_Php extends Xapp_Cache_Driver
{
    /**
     * php driver option cache directory must be a valid path and passed with instance
     *
     * @const PATH
     */
    const PATH                      = 'XAPP_CACHE_DRIVER_PHP_PATH';


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
        self::PATH                  => XAPP_TYPE_DIR
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::PATH                  => 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::DEFAULT_EXPIRATION    => 60,
        self::AUTOPURGE_EXPIRED     => true
    );


    /**
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 16201
     * @param null|mixed $options expects optional xapp option array or object
     * @return Xapp_Cache_Driver_Php
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
     * @error 16202
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
                throw new Xapp_Cache_Driver_Exception(_("cache directory is not readable"), 1620201);
            }
            if(!$dir->isWritable())
            {
                throw new Xapp_Cache_Driver_Exception(_("cache directory is not writable"), 1620202);
            }
            xapp_set_option(self::PATH, rtrim($dir->getRealPath(), DS) . DS, $this);
      	}
        catch(Exception $e)
        {
            throw new Xapp_Cache_Driver_Exception(xapp_sprintf(_("cache directory file info error: %d, %s"), $e->getCode(), $e->getMessage()), 1620203);
        }
    }


    /**
     * get value for cache key returning default value if key does not exist or is expired
     *
     * @error 16203
     * @param string $key expects the cache key name as string
     * @param null|mixed|Exception $default expects the default return value or exception if cache key does not exist anymore
     * @return null|mixed
     * @throws Exception
     */
    public function get($key, $default = null)
    {
        if($this->has($key))
        {
            $php = @include xapp_get_option(self::PATH, $this) . $this->keyify($key) . '.php';
            if($php === false || time() >= (int)$php['lifetime'])
            {
                $this->forget($key);
                return xapp_default($default);
            }else{
                return $php['value'];
            }
        }else{
            return xapp_default($default);
        }
    }


    /**
     * set value for cache key with optional lifetime value as third parameter. if not set default lifetime
     * will be applied. returns boolean true if success else false
     *
     * @error 16204
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
        return (bool)file_put_contents(xapp_get_option(self::PATH, $this) . $this->keyify($key) . '.php', $this->phpize(var_export($value, true), $lifetime), LOCK_EX);
    }


    /**
     * check if cache key file still exists or has been remove already returning boolean value
     *
     * @error 16205
     * @param string $key expects the cache key name as string
     * @return bool
     */
    public function has($key)
    {
        return (file_exists(xapp_get_option(self::PATH, $this) . $this->keyify($key) . '.php'));
    }


    /**
     * remove cache key file returning boolean value from file unlink function
     *
     * @error 16206
     * @param string $key expects the cache key name as string
     * @return bool
     */
    public function forget($key)
    {
        if($this->has($key))
        {
            return unlink(xapp_get_option(self::PATH, $this) . $this->keyify($key) . '.php');
        }else{
            return false;
        }
    }


    /**
     * purge/remove only already expired cache entries if first parameter is set to true. if set
     * to false will remove all cache key files independent of expiration value!
     *
     * @error 16207
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
                        $value = @include $f;
                        if($value !== false && time() >= (int)$value['lifetime'])
                        {
                            unlink($f);
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
     * make php string to be written to php file so it can be included into variable fast
     *
     * @error 16208
     * @param mixed $value expects the cache value to write
     * @param int $lifetime expects the cache lifetime
     * @return string
     */
    protected function phpize($value, $lifetime)
    {
        $php = "";

        $php .= "<?php\n";
        $php .= "return array\n";
        $php .= "(\n";
        $php .= "'lifetime' => {$this->timestamp($lifetime)},\n";
        $php .= "'value' => $value\n";
        $php .= ");\n";
        $php .= "?>";

        return $php;
    }


    /**
     * make a valid key that will server as the filename
     *
     * @error 16209
     * @param string $key expects the cache key name as string
     * @return string
     */
    protected function keyify($key)
    {
        if(preg_match('/^[a-f0-9]{32}$/', $key))
        {
            return (string)$key;
        }else{
            return sha1(trim((string)$key));
        }
    }
}