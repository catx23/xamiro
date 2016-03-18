<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Xapp.Error');
xapp_import('xapp.Xapp.Reflection');

/**
 * Autoloader class
 *
 * @package Xapp
 * @class Xapp_Autoloader
 * @error 107
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Autoloader implements Xapp_Singleton_Interface
{
    /**
     * expects a string value or empty string for no value if classes use namespaces
     * like Xapp_ or Zend_. this default value defines the default path divider
     * which can only be a string or an empty value. the value can be also passed in first constructor
     * parameter $dir as array key lil array('path to dir', 'ns', 'divider') overwriting
     * default value. With this value you can match class name namespaces to file/dir namespace e.g.
     * a file in /app/foo/class.php will be stored as app_foo_class in cache map. use the ns divider
     * to overwrite the default value "_" to anything else
     *
     * @const PATH_SEPARATOR
     */
    const PATH_SEPARATOR            = 'AUTOLOADER_PATH_SEPARATOR';

    /**
     * expects the default php real namespace separator so it can be replaced with PATH_DIVIDER value
     * when looking for class name. e.g. a class Namespace\Foo will be replaced to Namespace_Foo with
     * default path separator making sure that namespaces match folder/file separator syntax.
     *
     * @const NS_SEPARATOR
     */
    const NS_SEPARATOR              = 'AUTOLOADER_NS_SEPARATOR';


    /**
     * expects a boolean value whether to load php include paths defined in get_include_path or not.
     * NOTE that loading path defined for include_path php ini setting can lead to performance drop
     * since autoloader will iterate through all folders. use this option only when include path are
     * set on purpose.
     *
     * @const INCLUDE_PATHS
     */
    const INCLUDE_PATHS             = 'AUTOLOAD_INCLUDE_PATHS';

    /**
     * expects an array with class names that will be excluded from loading
     *
     * @const EXCLUDE_CLASSES
     */
    const EXCLUDE_CLASSES           = 'AUTOLOADER_EXCLUDE_CLASSES';

    /**
     * expects an array with valid file extensions telling the autoloader
     * which files to preload and which not
     *
     * @const INCLUDE_EXTENSIONS
     */
    const INCLUDE_EXTENSIONS        = 'AUTOLOADER_INCLUDE_EXTENSION';

    /**
     * only use autoloader to autoload classes inside the xapp namespace and
     * omit the rest
     *
     * @const XAPP_ONLY
     */
    const XAPP_ONLY                 = 'AUTOLOADER_XAPP_ONLY';

    /**
     * apart from passing autoload dirs in constructor pass directories as
     * array in this option
     *
     * @const AUTOLOADER__DIRECTORIES
     */
    const DIRECTORIES               = 'AUTOLOADER__DIRECTORIES';


    /**
     * array containing all preloaded classes with class name and full path
     *
     * @var array
     */
    protected $_classes = array();

    /**
     * array containing all directories to include in preloading. see class constructor
     * for more explanation
     *
     * @var array
     */
    protected $_dirs = array();

    /**
     * contains the static singleton instance
     *
     * @var null|Xapp_Autoloader
     */
    protected static $_instance = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::PATH_SEPARATOR        => XAPP_TYPE_STRING,
        self::NS_SEPARATOR          => XAPP_TYPE_STRING,
        self::EXCLUDE_CLASSES       => XAPP_TYPE_ARRAY,
        self::INCLUDE_EXTENSIONS    => XAPP_TYPE_ARRAY,
        self::INCLUDE_PATHS         => XAPP_TYPE_BOOL,
        self::XAPP_ONLY             => XAPP_TYPE_BOOL,
        self::DIRECTORIES           => XAPP_TYPE_ARRAY
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::PATH_SEPARATOR        => 1,
        self::NS_SEPARATOR          => 1,
        self::EXCLUDE_CLASSES       => 0,
        self::INCLUDE_EXTENSIONS    => 1,
        self::INCLUDE_PATHS         => 1,
        self::XAPP_ONLY             => 1,
        self::DIRECTORIES           => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::PATH_SEPARATOR        => '_',
        self::NS_SEPARATOR          => '\\',
        self::INCLUDE_EXTENSIONS    => array('php', 'php5', 'php4', 'inc'),
        self::INCLUDE_PATHS         => false,
        self::XAPP_ONLY             => false
    );


    /**
     * class constructor initialize autloader but can only be called via singleton. constructor checks for
     * autoloading capacities, sets xapp default autoload directory and processes any other directories passed
     * in first parameter which can be:
     * 1)   string = containing a relative or absolute path
     * 2)   array = containing an array of relative or absolute paths
     * 3)   array = containing an array with at least 2 values per key e.g. $dir = array(array('path', 'ns', 'divider'),..)
     * when defining a namespace in variant 3) it will add this namespace and/or divider in front of file name so a class
     * named Xapp_Registry residing in /Registry/Registry.php can be mapped because file name does not have the namespace
     * in the file name.
     *
     * directories can be passed relative or absolute. when passed relative autoloader will try to complete the relative
     * path looking inside xapps root/base path for a valid directory. if nothing is found will throw an error.
     *
     * if you are using multiple autoloaders beware. phps native __autoload function can only be set once and if set
     * prior to xapp could lead to Xapp_Autoloader being useless.
     *
     * @error 10701
     * @param null|string|array $dirs expects a directory path or multiple as array
     * @param null|mixed $options expects the class instance options
     * @throws Xapp_Error
     */
    protected function __construct($dirs = null, $options = null)
    {
        xapp_init_options($options, $this);
        if(strnatcmp(phpversion(), '5.3.0') < 0 || !function_exists('spl_autoload_register'))
        {
            if(!function_exists('__autoload'))
            {
                function __autoload($class)
                {
                    self::instance()->load($class);
                }
            }else{
                throw new Xapp_Error(_("xapp autoloader can not overwrite autoloading since __autoload is already set"), 1070101);
            }
        }else{
            @spl_autoload_register(array(get_class($this), 'load'), true);
        }
        $this->init($dirs);
    }


    /**
     * expects the directories passed via class constructor to be stored as autoloadable directories for preloading
     * see Xapp_Autoloader for explanations.
     *
     * @see Xapp_Autoloader
     * @error 10709
     * @param null|string|array $dirs expects a directory path or multiple as array
     * @return void
     * @throws Xapp_Error
     */
    protected function init($dirs = null)
    {
        $this->_dirs[self::hash(xapp_path(XAPP_PATH_XAPP))] = array(xapp_path(XAPP_PATH_XAPP), 'Xapp');
        if(xapp_is_option(self::DIRECTORIES, $this))
        {
            $dirs = array_merge((array)$dirs, xapp_get_option(self::DIRECTORIES, $this));
        }
        if($dirs !== null && (is_array($dirs) || is_string($dirs)))
        {
            if(is_string($dirs))
            {
                $dirs = array(array($dirs));
            }else{
                $dirs = (array)$dirs;
                foreach($dirs as &$dir)
                {
                    if(!is_array($dir)) $dir = array($dir);
                }
            }
            unset($dir);
            foreach($dirs as $key => $dir)
            {
                $tmp = null;
                $dir = (array)$dir;
                $hash = self::hash($dir[0]);
                if(array_key_exists(1, $dir)){
                    $ns = array_slice($dir, 1);
                }
                else if(!is_int($key) && is_string($key)){
                    $ns = array($key);
                }else{
                    $ns = array();
                }
                if(is_dir($dir[0]))
                {
                    if(!array_key_exists($hash, $this->_dirs))
                    {
                        $this->_dirs[$hash] = array_merge(array(rtrim($dir[0], DS) . DS), $ns);
                        continue;
                    }
                }else{
                    if(stripos($dir[0], xapp_path(XAPP_PATH_ROOT)) === false)
                    {
                        $tmp = (string)realpath(xapp_path(XAPP_PATH_ROOT) . $dir[0]);
                    }
                    if(!is_dir($tmp)) { $tmp = null; }
                    if($tmp === null && stripos($dir[0], xapp_path(XAPP_PATH_BASE)) === false)
                    {
                        $tmp = (string)realpath(xapp_path(XAPP_PATH_BASE) . $dir[0]);
                    }
                    if(!is_dir($tmp)) { $tmp = null; }
                    if($tmp === null && stripos($dir[0], xapp_path(XAPP_PATH_XAPP)) === false)
                    {
                        $tmp = (string)realpath(xapp_path(XAPP_PATH_XAPP) . $dir[0]);
                    }
                    if(!is_dir($tmp)) { $tmp = null; }
                    if($tmp !== null)
                    {
                        if(!array_key_exists($hash, $this->_dirs))
                        {
                            $this->_dirs[$hash] = array_merge(array(rtrim($tmp, DS) . DS), $ns);
                        }
                    }else{
                        throw new Xapp_Error(xapp_sprintf(_("relative dir: %s is not a valid dir"), $dir[0]), 1070901);
                    }
                }
            }
        }
        if(xapp_is_option(self::INCLUDE_PATHS, $this))
        {
            $paths = explode(PATH_SEPARATOR, get_include_path());
            foreach($paths as $path)
            {
                if(strpos($path, DS) !== false && is_readable($path))
                {
                    $this->_dirs[self::hash($path)] = (array)$path;
                }
            }
        }
    }


    /**
     * creates and receives singleton instance of this class passing parameters to the constructor
     *
     * @error 01702
     * @see Xapp_Autoloader::__construct
     * @param null|string|array $dirs expects a valid value as explained in Xapp_Autoloader::__construct
     * @param null|mixed $options expects instance options
     * @return null|Xapp_Autoloader
     */
    public static function instance($dirs = null, $options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($dirs, $options);
        }
        return self::$_instance;
    }


    /**
     * checks whether singleton instance has been set or not
     *
     * @error 01703
     * @return bool
     */
    public static function hasInstance()
    {
        return (self::$_instance !== null) ? true : false;
    }


    /**
     * shortcut method to create singleton instance
     *
     * @error 01704
     * @see Xapp_Autoloader::__construct
     * @param null|string|array $dirs expects a valid value as explained in Xapp_Autoloader::__construct
     * @param null|mixed $options expects instance options
     * @return null|Xapp_Autoloader
     */
    public static function run($dirs = null, $options = null)
    {
        return self::instance($dirs, $options);
    }


    /**
     * makes a unique hash value for a directory name/path by using php md5 function
     *
     * @error 01708
     * @param string $dir directory/path as string
     * @return null|string
     */
    protected static function hash($dir)
    {
        return md5(str_replace(array('/', '\\', ':', '.', '_', '-', ' ', '(', ')', '#'), '', trim((string)$dir)));
    }


    /**
     * return all preloaded classes as class => path map array
     *
     * @error 01707
     * @return array
     */
    public function getClasses()
    {
        return $this->_classes;
    }


    /**
     * will preload passed directories to map class name (with optional namespace) to full path to class file
     * so autoload function will include class by looking into cached map called by this function instead of
     * iterating though all directories anew. see further explanation in class constructor. function will
     * return the class map as key => value array.
     *
     * @error 01705
     * @param null|array|string $dir expects valid dir
     * @return array
     * @throws Xapp_Error
     */
    public function preload($dir = null)
    {
        $separator = xapp_get_option(self::PATH_SEPARATOR, $this);
        $extensions = xapp_get_option(self::INCLUDE_EXTENSIONS, $this);
        $classes =  xapp_get_option(self::EXCLUDE_CLASSES, $this);

        if($dir === null)
        {
            $dir = $this->_dirs;
        }
        foreach((array)$dir as $k => $v)
        {
            $v = (array)$v;
            if(is_dir($v[0]))
            {
                try
                {
                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($v[0]), RecursiveIteratorIterator::CHILD_FIRST);
                    foreach($iterator as $i)
                    {
                        if(stripos($i->__toString(), '.svn') === false && stripos($i->__toString(), 'branches') === false)
                        {
                            if(!$i->isDir())
                            {
                                //for xapp ext package limit iteration to first level only
                                if(stripos($i->__toString(), 'Xapp' . DS . 'Ext' . DS  .'src') !== false && substr($i->__toString(), strrpos($i->__toString(), DS) - 3, 3) !== 'src')
                                {
                                    continue;
                                }

                                $class  = '';
                                $file   = trim($i->__toString());
                                $path   = trim((string)substr(substr($file, 0, strrpos($file, DS)), strlen($v[0])), DS);
                                $paths  = explode(DS, str_replace(DS . 'src','', $path));
                                $name   = substr(basename($file), 0, strpos(basename($file), '.'));
                                $ext    = strtolower(substr($file, strrpos($file, '.') + 1));
                                $div    = ((isset($v[2])) ? strtolower(trim($v[2])) : $separator);
                                $ns     = ((isset($v[1]) && !empty($v[1])) ? trim(trim($v[1]), $div) : '');
                                if(empty($path))
                                {
                                    $class = ((!empty($ns) && $name !== 'xapp') ? $ns . $div : '') . $name;
                                }else{
                                    $class .= ((!empty($ns) && $ns !== array_shift($paths)) ? $ns . $div : '');
                                    $paths = explode(DS, str_replace(DS . 'src','', $path));
                                    if(end($paths) === $name)
                                    {
                                        $class .= implode($div, $paths);
                                    }else{
                                        $class .= implode($div, array_merge($paths, array($name)));
                                    }
                                }
                                $class = strtolower($class);
                                if(in_array($ext, $extensions) && !in_array($class, (array)$classes))
                                {
                                    if(!isset($this->_classes[$k]))
                                    {
                                        $this->_classes[$k] = array();
                                    }
                                    if(!array_key_exists($class, $this->_classes[$k]))
                                    {
                                        $this->_classes[$k][$class] = $file;
                                    }
                                }
                            }
                        }
                    }
                }
                catch(Exception $e)
                {
                    throw new Xapp_Error(xapp_sprintf(_("iterator exception: %d, %s"), $e->getCode(), $e->getMessage()), 0170501);
                }
            }else{
                throw new Xapp_Error(xapp_sprintf(_("unable to preload dir: %s since dir is not valid"), $v[0]), 0170501);
            }
        }
        return $this->_classes;
    }


    /**
     * autoload function registered with autoload handler loads class by looking for class in preloaded class map loaded
     * by Xapp_Autoloader::preload.
     *
     * @error 01706
     * @param null|string $class expects class to load
     * @return bool
     */
    public function load($class = null)
    {
        $file = null;

        if($class !== null && !empty($class))
        {
            $class = strtolower(trim((string)$class));
            if(strpos($class, xapp_get_option(self::NS_SEPARATOR, $this)) !== false)
            {
                $class = trim($class, xapp_get_option(self::NS_SEPARATOR, $this));
                $class = str_replace(xapp_get_option(self::NS_SEPARATOR, $this), xapp_get_option(self::PATH_SEPARATOR, $this), $class);
            }
            if((bool)xapp_get_option(self::XAPP_ONLY))
            {
                if(strpos($class, 'Xapp_') === false)
                {
                    return true;
                }
            }
            if(empty($this->_classes))
            {
                $this->preload();
            }
            foreach($this->_dirs as $k => $v)
            {
                if(isset($this->_classes[$k]) && array_key_exists($class, $this->_classes[$k]))
                {
                    $file = $this->_classes[$k][$class];
                    break;
                }
                if(isset($this->_classes[$k]) && array_key_exists("xapp_ext_$class", $this->_classes[$k]))
                {
                    $file = $this->_classes[$k]["xapp_ext_$class"];
                    break;
                }
            }
            if($file !== null && is_file($file))
            {
                require_once $file;
                @clearstatcache();
                return true;
            }
        }
        return false;
    }
}