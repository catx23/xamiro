<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Xapp.Error');

if(!function_exists('_')){
	function _($arg){
		return $arg;
	}

}

/**
 * Xapp base class
 *
 * @package Xapp
 * @class Xapp
 * @error 101
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp
{
    /**
     * contains all debug messages
     *
     * @var array
     */
    protected static $_debug = array();

    /**
     * true if xapp base class has been initialized with xapp::run, false if not
     *
     * @var bool
     */
    protected static $_isRunning = false;


    /**
     * run xapp to init your application and make use of all in build features such as
     * debuging, autoloading, error logging. xapp can only be initialized with this method
     * expecting the optional xapp conf array which can also be set outside of xapp with
     * the generic function xapp_conf.
     *
     * @error 10101
     * @param array $conf
     * @return void
     */
    public static function run(Array $conf = null)
    {
        if(!isset($GLOBALS['XAPP_PATH']) || empty($GLOBALS['XAPP_PATH']))
        {
            require_once rtrim(__DIR__, '/\\') . "/../../Core/core.php";
        }

        xapp_conf($conf);
        xapp_debug('xapp is running', 'xapp');
        xapp_event('xapp.run');

        if((bool)xapp_conf(XAPP_CONF_PROFILER_MODE))
        {
            if(xapp_conf(XAPP_CONF_PROFILER_MODE) instanceof Xapp_Profiler)
            {
                $GLOBALS['XAPP_PROFILER'] = xapp_conf(XAPP_CONF_PROFILER_MODE);
            }
            if(!isset($GLOBALS['XAPP_PROFILER']))
            {
                $GLOBALS['XAPP_PROFILER'] = new Xapp_Profiler(array
                (
                    Xapp_Profiler::MODE => xapp_conf(XAPP_CONF_PROFILER_MODE)
                ));
            }
            xapp_profile('xapp', 'run', microtime(true) - XAPP_START);
        }

        self::_init();

        if((bool)xapp_conf(XAPP_CONF_HANDLE_BUFFER) && strtolower(php_sapi_name()) !== 'cli')
        {
            if(xapp_conf(XAPP_CONF_HANDLE_BUFFER) === true)
            {
                ob_start('Xapp::bufferHandler');
            }else if(is_callable(xapp_conf(XAPP_CONF_HANDLE_BUFFER))){
                ob_start(xapp_conf(XAPP_CONF_HANDLE_BUFFER));
            }else{
                ob_start('mb_output_handler');
            }
        }

        if(xapp_conf(XAPP_CONF_AUTOLOAD) !== null && xapp_conf(XAPP_CONF_AUTOLOAD) !== false)
        {
            if(!(xapp_conf(XAPP_CONF_AUTOLOAD) instanceof Xapp_Autoloader))
            {
                self::import('xapp.Xapp.Autoloader');
                if(!Xapp_Autoloader::hasInstance())
                {
                    if(xapp_conf(XAPP_CONF_IMPORT_PATH) !== null && xapp_conf(XAPP_CONF_IMPORT_PATH) !== false)
                    {
                        $dirs = (array)xapp_conf(XAPP_CONF_IMPORT_PATH);
                    }else{
                        $dirs = null;
                    }
                    Xapp_Autoloader::run($dirs, ((is_array(xapp_conf(XAPP_CONF_AUTOLOAD)) || is_string(xapp_conf(XAPP_CONF_AUTOLOAD))) ? array(Xapp_Autoloader::DIRECTORIES => (array)xapp_conf(XAPP_CONF_AUTOLOAD)) : null));
                }
            }
        }

        if((bool)xapp_conf(XAPP_CONF_HANDLE_ERROR))
        {
            if(xapp_conf(XAPP_CONF_HANDLE_ERROR) === true)
            {
                set_error_handler(array('Xapp_Error', 'errorHandler'), (int)xapp_conf(XAPP_CONF_HANDLE_ERROR_CODES));
            }else if(is_callable(xapp_conf(XAPP_CONF_HANDLE_ERROR))){
                set_error_handler(xapp_conf(XAPP_CONF_HANDLE_ERROR), (int)xapp_conf(XAPP_CONF_HANDLE_ERROR_CODES));
            }else if(strtolower(xapp_conf(XAPP_CONF_HANDLE_ERROR)) === 'console'){
                //do nothing because errors are send to console
            }else{
                die("unable to delegate error handler due to invalid error handler value" . PHP_EOL);
            }
        }

        if((bool)xapp_conf(XAPP_CONF_HANDLE_EXCEPTION))
        {
            if(xapp_conf(XAPP_CONF_HANDLE_EXCEPTION) === true)
            {
                set_exception_handler(array('Xapp_Error', 'exceptionHandler'));
            }else if(is_callable(xapp_conf(XAPP_CONF_HANDLE_EXCEPTION))){
                set_exception_handler(xapp_conf(XAPP_CONF_HANDLE_EXCEPTION));
            }else{
                die("unable to delegate exception handler due to invalid exception handler value" . PHP_EOL);
            }
        }

        if((bool)xapp_conf(XAPP_CONF_HANDLE_SHUTDOWN))
        {
            if(xapp_conf(XAPP_CONF_HANDLE_SHUTDOWN) === true)
            {
                register_shutdown_function(array('Xapp', 'shutdownHandler'));
            }else if(is_callable(xapp_conf(XAPP_CONF_HANDLE_SHUTDOWN))){
                register_shutdown_function(xapp_conf(XAPP_CONF_HANDLE_SHUTDOWN));
            }else{
                die("unable to delegate shutdown handler due to invalid shutdown handler value" . PHP_EOL);
            }
        }

        if((bool)xapp_conf(XAPP_CONF_LOG_ERROR))
        {
            if(xapp_conf(XAPP_CONF_LOG_ERROR) === true || is_string(xapp_conf(XAPP_CONF_LOG_ERROR)))
            {
                if(xapped('Xapp_Log_Error', true))
                {
                    $log = true;
                }else{
                    $log = xapp_import('xapp.Log.Error');
                }
                if($log)
                {
                    if(is_string(xapp_conf(XAPP_CONF_LOG_ERROR)))
                    {
                        if(is_file(xapp_conf(XAPP_CONF_LOG_ERROR)) || is_dir(xapp_conf(XAPP_CONF_LOG_ERROR)))
                        {
                            $path = rtrim(xapp_conf(XAPP_CONF_LOG_ERROR), DS) . DS;
                        }else{
                            die("user error log path for error logger is not a valid directory" . PHP_EOL);
                        }
                    }else{
                        $path = xapp_path(XAPP_PATH_LOG);
                        if(empty($path))
                        {
                            die("xapp log path value is not set - please define by setting a new log path value with xapp_path function" . PHP_EOL);
                        }
                    }
                    if(is_null(xapp_conf(XAPP_CONF_SYSTEM_EMAIL)) || is_bool(xapp_conf(XAPP_CONF_SYSTEM_EMAIL)))
                    {
                        $options = array
                        (
                            Xapp_Log_Error::PATH => $path,
                            Xapp_Log_Error::STACK_TRACE => false
                        );
                    }else{
                        $options = array
                        (
                            Xapp_Log_Error::PATH => $path,
                            Xapp_Log_Error::EMAIL => xapp_conf(XAPP_CONF_SYSTEM_EMAIL),
                            Xapp_Log_Error::STACK_TRACE => false
                        );
                    }
                    xapp_conf(XAPP_CONF_LOG_ERROR, Xapp_Log_Error::instance($options));
                }else{
                    if(is_file(xapp_conf(XAPP_CONF_LOG_ERROR)) || is_dir(xapp_conf(XAPP_CONF_LOG_ERROR)))
                    {
                        //do nothing since error log file or dir are found
                    }else{
                        die("user error log path for error logger must be a valid directory" . PHP_EOL);
                    }
                }
            }
            if(is_object(xapp_conf(XAPP_CONF_LOG_ERROR)) && !Xapp_Reflection::hasMethod(xapp_conf(XAPP_CONF_LOG_ERROR), 'log', true))
            {
                die("unable to delegate error logger since passed instance is not compatible and needs to implement at least log() as public function" . PHP_EOL);
            }
        }

        if((bool)xapp_conf(XAPP_CONF_CONSOLE))
        {
            if(xapp_conf(XAPP_CONF_CONSOLE) === true){
                xapp_conf(XAPP_CONF_CONSOLE, Xapp_Console::instance('firephp'));
            }else if(is_string(xapp_conf(XAPP_CONF_CONSOLE))){
                xapp_conf(XAPP_CONF_CONSOLE, Xapp_Console::instance(xapp_conf(XAPP_CONF_CONSOLE)));
            }else if(is_object(xapp_conf(XAPP_CONF_CONSOLE)) && !(xapp_conf(XAPP_CONF_CONSOLE) instanceof Xapp_Console)){
                die("unable to use object for console logging since object is not instance of Xapp_Console" . PHP_EOL);
            }
        }
    }


    /**
     * init shortcut function initializes the functions:
     * @see Xapp::_initEnv
     * @see Xapp::_initSys
     *
     * @error 10102
     * @return void
     */
    final protected static function _init()
    {
        if(!self::$_isRunning)
        {
            self::_initSys();
            self::_initEnv();
            self::$_isRunning = true;
        }
    }


    /**
     * init environment with local settings defined in xapp conf.
     * see xapp conf constants for more details on available values
     *
     * @error 10103
     * @return void
     */
    final protected static function _initEnv()
    {
        $locale = null;
        xapp_debug('xapp init environment', 'xapp');

        if(xapp_conf(XAPP_CONF_CHARSET))
        {
            mb_http_output(strtoupper(trim(xapp_conf(XAPP_CONF_CHARSET))));
            mb_internal_encoding(strtoupper(trim(xapp_conf(XAPP_CONF_CHARSET))));
        }
        if(xapp_conf(XAPP_CONF_LANGUAGE))
        {
            putenv('LANGUAGE='.xapp_conf(XAPP_CONF_LANGUAGE).'');
            assert(getenv('LANGUAGE') == xapp_conf(XAPP_CONF_LANGUAGE));
        }
        if(xapp_conf(XAPP_CONF_LOCALE))
        {
            $locale = xapp_conf(XAPP_CONF_LOCALE);
            if(!is_array($locale))
            {
                setlocale(LC_ALL, xapp_conf(XAPP_CONF_LOCALE));
            }else{
                foreach(xapp_conf(XAPP_CONF_LOCALE) as $k => $v){ setlocale($k, $v); }
            }
        }
        if(xapp_conf(XAPP_CONF_TIMEZONE))
        {
            date_default_timezone_set(xapp_conf(XAPP_CONF_TIMEZONE));
        }
    }


    /**
     * init system by setting error codes, error display value, performance values, etc.
     * dies if xapp is unable to run on the host.
     *
     * @error 10104
     * @return void
     */
    final protected static function _initSys()
    {
        if(strtolower(trim(php_sapi_name())) === 'cli' && ((int)xapp_conf(XAPP_CONF_DEBUG_MODE) === 4 || xapp_conf(XAPP_CONF_DEBUG_MODE) === 'console'))
        {
            die("unable to debug with interactive console in php cli" . PHP_EOL);
        }

        xapp_debug('xapp init system', 'xapp');

        if(!extension_loaded('mbstring'))
        {
            die("mbstring extension not found - please include mbstring shim manually from https://github.com/tchwork/utf8/blob/master/class/Patchwork/PHP/Shim/Mbstring.php" . PHP_EOL);
        }
        if(xapp_conf(XAPP_CONF_HANDLE_ERROR_CODES) === null || xapp_conf(XAPP_CONF_HANDLE_ERROR_CODES) === false)
        {
            if((bool)xapp_conf(XAPP_CONF_DEV_MODE)){
                xapp_conf(XAPP_CONF_HANDLE_ERROR_CODES, E_ALL);
            }else if((bool)(bool)xapp_conf(XAPP_CONF_DEBUG_MODE)){
                xapp_conf(XAPP_CONF_HANDLE_ERROR_CODES, (E_ALL ^ E_NOTICE) | E_STRICT);
            }else{
                xapp_conf(XAPP_CONF_HANDLE_ERROR_CODES, E_ALL & ~E_NOTICE & ~E_STRICT);
            }
        }
        error_reporting((int)xapp_conf(XAPP_CONF_HANDLE_ERROR_CODES));
        if(xapp_conf(XAPP_CONF_DISPLAY_ERROR) !== null)
        {
            xapp_ini('display_errors', (int)xapp_conf(XAPP_CONF_DISPLAY_ERROR));
        }else if((bool)xapp_conf(XAPP_CONF_DEV_MODE) && (int)xapp_ini('display_errors') === 0){
            xapp_ini('display_errors', 1);
        }
        if(xapp_conf(XAPP_CONF_MEMORY_LIMIT))
        {
            xapp_ini('memory_limit', strtolower(trim(xapp_conf(XAPP_CONF_MEMORY_LIMIT))));
        }
        if(strtolower(trim(php_sapi_name())) === 'cli'){
            set_time_limit(0);
            xapp_ini('max_execution_time', 0);
        }else if(xapp_conf(XAPP_CONF_EXECUTION_TIME) !== null && xapp_conf(XAPP_CONF_EXECUTION_TIME) !== false){
            set_time_limit((int)xapp_conf(XAPP_CONF_EXECUTION_TIME));
            xapp_ini('max_execution_time', (int)xapp_conf(XAPP_CONF_EXECUTION_TIME));
        }

        xapp_ini('short_open_tag', 1);
        xapp_ini('magic_quotes_gpc', 0);
        xapp_ini('magic_quotes_runtime', 0);
        xapp_ini('pcre.recursion-limit', 100000);

        if(defined('STDIN') && isset($_SERVER['argv']))
        {
            for($i = 0; $i < sizeof($_SERVER['argv']); $i++)
            {
                $_SERVER['CLI'][] = $_SERVER['argv'][$i];
            }
        }

        if(version_compare(PHP_VERSION, XAPP_PHP_VERSION, '<'))
        {
            die("xapp core needs php version > ".XAPP_PHP_VERSION." to run - your version is: " .PHP_VERSION . PHP_EOL);
        }
    }


    /**
     * include function for xapp will included absolute or relative php includable files as well
     * as java style dot and wildcard notation classes and packages. see xapp_import for more
     *
     * @see xapp_import
     * @error 10105
     * @param mixed $mixed expects file/dir/class/package as single string value or array with multiple values
     * @return void
     */
    public static function import($mixed)
    {
        self::_init();
        if(is_string($mixed))
        {
            xapp_import($mixed);
        }else{
            foreach((array)$mixed as $m)
            {
                xapp_import($m);
            }
        }
    }


    /**
     * checks whether a class is already included/loaded and loads the same if required.
     * the class has to be defined by its class name e.g. Xapp_Log_Error or in dot "." notation
     * e.g. xapp.log.error or passed as php 5.3 namespace string
     *
     * @error 10106
     * @param string $class expects the class name
     * @param bool $autoload defines whether to load the class or not
     * @return bool
     */
    public static function imported($class, $autoload = false)
    {
        $class = trim($class);
        $spl = spl_autoload_functions();
        if($spl !== false && sizeof($spl) > 0)
        {
            if(in_array(str_replace('.', '_', trim($class, '.')), get_declared_classes()) || in_array(str_replace('.', '\\', trim($class, '.')), get_declared_classes()))
            {
                if((bool)$autoload)
                {
                    self::import($class);
                }
                return true;
            }
            return false;
        }else if(class_exists(str_replace('.', '_', trim($class, '.')), (bool)$autoload)){
            return true;
        }else if(class_exists(str_replace('.', '\\', '\\' . trim($class, '.\\')), (bool)$autoload)){
            return true;
        }else{
            return false;
        }
    }


    /**
     * xapp option helper assert option.
     * this function will validate any xapp class for its options and passed values which are
     * defined in their respective options option requirements which are the data type map: $optionsDict and
     * the mandatory map: $optionsRule. this function will assert the instance option values on if set or not
     * and throws an error if a option value is required but missing.
     *
     * @error 10107
     * @param null|array|object $options expects the options array (instance option values)
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @return void
     * @throws Xapp_Error
     */
    public static function assertOptions($options = null, $object = null)
    {
        try
        {
            $options = (array)$options;
            if($object === null)
            {
                $object = self::optionCallee();
            }
            if(!is_object($object) && !Xapp_Reflection::isStaticProperty($object, 'options'))
            {
                throw new Xapp_Error(xapp_sprintf(_("access to none static property \$options is not allowed for static class: %s"), $object), 1010703);
            }
            $options = array_merge((array)Xapp_Reflection::propertyFactory($object, 'options', 'NIL', true), $options);
            if(is_object($object))
            {
                $rule = Xapp_Reflection::mergeProperty($object, 'optionsRule', array());
            }else{
                $rule = Xapp_Reflection::propertyFactory($object, 'optionsRule');
            }
            foreach($rule as $k => $v)
            {
                if((int)$v === 1 && !array_key_exists($k, $options))
                {
                    throw new Xapp_Error(xapp_sprintf(_("unable to initialize options since option: %s must be set"), $k), 1010701);
                }
            }
        }
        catch(ReflectionException $e)
        {
            throw new Xapp_Error(xapp_sprintf(_("reflection error: %d, %s"), $e->getCode(), $e->getMessage()), 1010702);
        }
    }


    /**
     * xapp option helper init option receive options and delegate first to xapp::assertOptions and then to xapp::setOptions
     *
     * @see xapp::assertOptions
     * @see xapp::setOptions
     * @error 10108
     * @param null|array|object $options expects the options array (instance option values)
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @return void
     */
    public static function initOptions($options = null, $object = null)
    {
        self::assertOptions($options, $object);
        self::setOptions($options, $object);
    }


    /**
     * xapp option helper set options sets multiple options using xapps setOption helper
     *
     * @see xapp::setOption
     * @error 10109
     * @param array|object $options object
     * @param null|object $object the calling object
     * @return array the objects options
     */
    public static function setOptions($options, $object = null)
    {
        foreach((array)$options as $k => $v)
        {
            self::setOption($k, $v, $object);
        }
        return self::getOptions($object);
    }


    /**
     * xapp option helper set option sets single option to options array of passed object checking if options dictionary
     * $optionsDict is set to validate passed option value for data type matching and will throw error if data type does
     * not match
     *
     * @error 10110
     * @param string $key expects the option key name
     * @param null|mixed $value expects the option value
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @param boolean $reset pass boolean true to reset value in case of array values
     * @return array|null the altered options array
     * @throws Xapp_Error
     */
    public static function setOption($key, $value = null, $object = null, $reset = false)
    {
        $options = array();
        $dict = null;
        $err = array();
        $ok = 0;

        if($object === null)
        {
            $object = self::optionCallee();
        }
        if(xapped($object))
        {
            $key = strtoupper(trim($key));
        }
        if(Xapp_Reflection::hasProperty($object, 'options'))
        {
            if(!is_object($object) && !Xapp_Reflection::isStaticProperty($object, 'options'))
            {
                throw new Xapp_Error(xapp_sprintf(_("access to none static property \$options is not allowed for static class: %s"), $object), 1011003);
            }else{
                $options = (array)Xapp_Reflection::propertyFactory($object, 'options', 'NIL', true);
            }
        }
        $dict = Xapp_Reflection::mergeProperty($object, 'optionsDict', array());
        if(!empty($dict))
        {
            if(array_key_exists($key, $dict))
            {
                if(!is_array($dict[$key]))
                {
                    $dict[$key] = array($dict[$key]);
                }
                foreach($dict[$key] as $k => $v)
                {
                    try
                    {
                        if(defined('XAPP_TYPE_' . strtoupper($v)))
                        {
                            if(strtoupper($v) !== XAPP_TYPE_MIXED)
                            {
                                if(!xapp_is($v, $value))
                                {
                                    throw new Xapp_Error(xapp_sprintf(_("unable to set option: %s due to missing or invalid dataype"), $key), 1011001, XAPP_ERROR_IGNORE);
                                }else{
                                    $ok++;
                                }
                            }
                        }else{
                            if(!class_exists($v) || !($value instanceof $v))
                            {
                                throw new Xapp_Error(xapp_sprintf(_("unable to set option: %s since value must be instance of: %s"), $key, $v), 1011002, XAPP_ERROR_IGNORE);
                            }else{
                                $ok++;
                            }
                        }
                    }
                    catch(Exception $e)
                    {
                        array_push($err, $e);
                    }
                }
                if($ok === 0)
                {
                    $err = array_shift($err);
                    throw new Xapp_Error($err->getMessage(), $err->getCode(), XAPP_ERROR_ERROR);
                }
            }
        }

        if(!(bool)$reset && isset($options[$key]) && is_array($options[$key]) && is_array($value))
        {
            if(array_values($value) === $value)
            {
                $options[$key] = array_unique(array_merge($options[$key], $value), SORT_REGULAR);
            }else{
                $options[$key] = xapp_array_merge($options[$key], $value);
            }
        }else{
            $options[$key] = $value;
        }
        Xapp_Reflection::propertyFactory($object, 'options', $options, true);
        return $options;
    }


    /**
     * xapp option helper unset option
     * will unset an option from the options array of passed object
     *
     * @error 10111
     * @param null|string $key expects the option key name
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @return void
     * @throws Xapp_Error
     */
    public static function unsetOption($key, $object = null)
    {
        if($object === null)
        {
            $object = self::optionCallee();
        }
        if(xapped($object))
        {
            $key = strtoupper(trim($key));
        }
        if(!is_object($object) && !Xapp_Reflection::isStaticProperty($object, 'options'))
        {
            throw new Xapp_Error(xapp_sprintf(_("access to none static property \$options is not allowed for static class: %s"), $object), 1011101);
        }else{
            $options = Xapp_Reflection::propertyFactory($object, 'options', 'NIL', true);
            if(array_key_exists($key, $options))
            {
                unset($options[$key]);
                Xapp_Reflection::propertyFactory($object, 'options', $options, true);
            }
        }
    }


    /**
     * xapp option helper has option checks if option key does exists in options array of passed object using either strict
     * checking or not where strict = true $key value must have a value other then null|false|empty
     *
     * @error 10112
     * @param null|string $key expects the option key name
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @param bool $strict expects the strict value
     * @return bool
     * @throws Xapp_Error
     */
    public static function hasOption($key = null, $object = null, $strict = false)
    {
        if($key !== null)
        {
            if($object === null)
            {
                $object = self::optionCallee();
            }
            if(xapped($object))
            {
                $key = strtoupper(trim($key));
            }
            if(!is_object($object) && !Xapp_Reflection::isStaticProperty($object, 'options'))
            {
                throw new Xapp_Error(xapp_sprintf(_("access to none static property \$options is not allowed for static class: %s"), $object), 1011201);
            }else{
                try
                {
                    $options = Xapp_Reflection::propertyFactory($object, 'options', 'NIL', true);
                    return ((bool)$strict) ? (array_key_exists($key, $options) && xapp_is('value', $options[$key])) : (array_key_exists($key, $options));
                }
                catch(Exception $e){}
            }
        }
        return false;
    }


    /**
     * xapp option helper get options returns options array from object or default return value if option property can not
     * be returned
     *
     * @error 10113
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @param null|mixed $default $default expects the default return value
     * @return null|array options array
     * @throws Xapp_Error
     */
    public static function getOptions($object = null, $default = null)
    {
        if($object === null)
        {
            $object = self::optionCallee();
        }
        if(!is_object($object) && !Xapp_Reflection::isStaticProperty($object, 'options'))
        {
            throw new Xapp_Error(xapp_sprintf(_("access to none static property \$options is not allowed for static class: %s"), $object), 1011301);
        }else{
            try
            {
                return Xapp_Reflection::propertyFactory($object, 'options', 'NIL', true);
            }
            catch(Exception $e){}
        }
        return xapp_default($default);
    }


    /**
     * xapp option helper get option get single option value from option array of passed object returning default value
     * if option key is not found in option array
     *
     * @error 10114
     * @param string $key expects the option key name
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @param null|mixed $default expects the default return value
     * @return mixed
     * @throws Xapp_Error
     */
    public static function getOption($key, $object = null, $default = null)
    {
        if($object === null)
        {
            $object = self::optionCallee();
        }
        if(xapped($object))
        {
            $key = strtoupper(trim($key));
        }
        if(!is_object($object) && !Xapp_Reflection::isStaticProperty($object, 'options'))
        {
            throw new Xapp_Error(xapp_sprintf(_("access to none static property \$options is not allowed for static class: %s"), $object), 1011401);
        }else{
            try
            {
                $options = Xapp_Reflection::propertyFactory($object, 'options', 'NIL', true);
                if(array_key_exists($key, $options))
                {
                    return $options[$key];
                }
            }
            catch(Exception $e){}
        }
        return xapp_default($default);
    }


    /**
     * xapp option helper reset options reset objects option array by reseting it to empty array
     *
     * @error 10115
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @return void
     * @throws Xapp_Error
     */
    public static function resetOptions($object = null)
    {
        if($object === null)
        {
            $object = self::optionCallee();
        }
        if(!is_object($object) && !Xapp_Reflection::isStaticProperty($object, 'options'))
        {
            throw new Xapp_Error(xapp_sprintf(_("access to none static property \$options is not allowed for static class: %s"), $object), 1011501);
        }
        Xapp_Reflection::propertyFactory($object, 'options', array(), true);
    }


    /**
     * xapp option helper reset option. reset an option key by value making sure that value can not be appended for array
     * values. this function uses the setOption function and the reset flag set to true
     *
     * @error 10129
     * @param string $key expects the option key name
     * @param null|mixed $value expects the option value
     * @param null|string|object $object expects the object to assert option values for as string or object reference
     * @return array|null the altered options array
     */
    public static function resetOption($key, $value = null, $object = null)
    {
        if(self::hasOption($key, $object))
        {
            return self::setOption($key, $value, $object, true);
        }else{
            return null;
        }
    }


    /**
     * callee for options since options can only be called static or from within subclass
     * of this class or via xapp function error handling the object must be either in callee
     * depth 3 or 4
     *
     * @error 10128
     * @return object|string
     */
    protected static function optionCallee()
    {
        $caller = self::callee(null, 4);
        if(!is_object($caller))
        {
            return self::callee(null, 3);
        }else{
            return $caller;
        }
    }


    /**
     * overloading via magic method __call is only allowed to set and get public properties
     * of call by preceding get|set as method name. e.g. to get the class variable foo call
     * $instance->getFoo(), or $instance->setFoo('value'). all other method overloading calls
     * are not permitted.
     *
     * @error 10116
     * @param string $method expects the method name
     * @param array $args expects possible arguments
     * @return mixed property set/get value
     * @throws Xapp_Error
     */
    public function __call($method, Array $args = null)
    {
        $property = null;

        if(substr(strtolower($method), 0, 3) === 'get' || substr(strtolower($method), 0, 3) === 'set')
        {
            $property = lcfirst(substr($method, 3, strlen($method)));
            if(Xapp_Reflection::hasProperty($this, '_' . $property))
            {
                $property = '_' . $property;
                if(substr(strtolower($method), 0, 3) === "get")
                {
                    return $this->$property;
                }else{
                    return $this->$property = (sizeof($args) === 1) ? $args[0] : $args;
                }
            }else if(Xapp_Reflection::hasProperty($this, $property)){
                if(substr(strtolower($method), 0, 3) === "get")
                {
                    return $this->$property;
                }else{
                    return $this->$property = (sizeof($args) === 1) ? $args[0] : $args;
                }
            }else{
                throw new Xapp_Error(xapp_sprintf(_("property: %s does not exist an can not be called via overloading"), $property), 1011502);
            }
        }else{
            throw new Xapp_Error(xapp_sprintf(_("method: %s does not exist and can not be called via overloading"), $method), 1011501);
        }
    }


    /**
     * tries to determine which class is calling this function either by passing
     * object or calling it statically.
     *
     * @error 10117
     * @param null|object $object expects optional object instance
     * @param int $depth expects the debug depth
     * @return object returns the caller object
     */
    public static function callee($object = null, $depth = 1)
    {
        $class = get_called_class();
        if($object !== null && is_object($object))
        {
            return $object;
        }else{
            $d = debug_backtrace();
            if(isset($d[(int)$depth]) && isset($d[(int)$depth]['type']) && $d[(int)$depth]['type'] == '->'){
                return $d[(int)$depth]['object'];
            }else if(isset($d[(int)$depth]) && isset($d[(int)$depth]['type']) && $d[(int)$depth]['type'] == '::'){
                return $d[(int)$depth]['class'];
            }
        }
        return $class;
    }


    /**
     * generic class factory will load and instantiate classes and even call static
     * method directly if first paramter is defined to call method directly. values can be:
     * 1)   class = full class name or inherited class name of callee (if you call Xapp_Log::factory('error')
     *      the according subclass Xapp_Log_Error will be loaded
     * 2    class::method = will execute a static public class method directly
     * the method can be overload with further arguments to be passed to to class constructor
     * or static method. classes can be notated also with punctation e.g. Xapp::factory('xapp.log.error')
     *
     * @error 10118
     * @param string $class expects the class or class method name according to explanation above
     * @return mixed the value according to explanation above
     * @throws Xapp_Error
     */
    public static function factory($class)
    {
        $method = null;

        try
        {
            $class = trim($class);
            $callee = self::callee();

            if(strpos($class, '::') !== false){
                $method = substr($class, strpos($class, '::') + 2);
                $class = substr($class, 0, strpos($class, '::'));
            }else{
                $class = str_replace(array('.', '/'), '_', $class);
            }
            if(strtolower($callee) === strtolower(__CLASS__))
            {
                if(strtolower(array_shift(explode('_', $class))) !== strtolower(__CLASS__))
                {
                    $class = __CLASS__ . '_' . $class;
                }
            }else{
                if(strpos($callee, '_') !== false)
                {
                    $callee = implode('_', array_slice(explode('_', $callee), 0, -1));
                }
                $class = $callee . '_' . $class;
            }
            if(!class_exists($class, false))
            {
                self::import($class);
            }
            if($method !== null)
            {
                return Xapp_Reflection::factory('method', $class, $method, array_slice(func_get_args(), 1));
            }else{
                return Xapp_Reflection::factory('class', $class, array_slice(func_get_args(), 1));
            }
        }
        catch(ReflectionException $e)
        {
            throw new Xapp_Error($e->getMessage(), 1011801);
        }
    }


    /**
     * get class name from passed object or string
     *
     * @error 10119
     * @param string|object $mixed expects class name or class object instance
     * @param bool $lower expects whether to return class name in lower chars or not
     * @return string the class name
     * @throws Xapp_Error
     */
    protected static function getClassName($mixed, $lower = false)
    {
        if(is_string($mixed))
        {
            return ((bool)$lower) ? strtolower($mixed) : $mixed;
        }else if(is_object($mixed)){
            return ((bool)$lower) ? strtolower(get_class($mixed)) : get_class($mixed);
        }else{
            throw new Xapp_Error(xapp_sprintf(_("unable to determine class name from passed value: %s"), $mixed), 1011901);
        }
    }


    /**
     * tries to determine encoding from passed string or internal used encoding if not
     * value is passed in first parameter. if unable to determine encoding will return
     * default value in second parameter
     *
     * @error 10120
     * @param null|string $str expects string to get encoding from
     * @param string $default the default value
     * @return string encoding value
     */
    public static function getEncoding($str = null, $default = 'utf-8')
    {
        if($str !== null)
        {
            return strtolower(mb_detect_encoding((string)$str));
        }else{
            if(xapp_conf(XAPP_CONF_CHARSET) !== null){
                return strtolower(xapp_conf(XAPP_CONF_CHARSET));
            }else if(($e = mb_internal_encoding()) !== false){
                return $e;
            }else if(($e = getenv('LANGUAGE')) !== false){
                if(($pos = strrpos($e, '.')) !== false)
                {
                    return strtolower(substr($e, $pos + 1, strlen($e)));
                }else{
                    return strtolower($e);
                }
            }
        }
        return (string)$default;
    }


    /**
     * tries to get data type for passed variable
     *
     * @error 10121
     * @param mixed $var expects variable to test
     * @return null|string the datatype as string or null if null
     */
    public static function getDataTypeFor($var = null)
    {
        if(is_object($var)){
            return strtolower(get_class($var));
        }
        if(is_array($var)){
            return 'array';
        }
        if(is_callable($var)){
            return 'callable';
        }
        if(is_resource($var)){
            return 'resource';
        }
        if(is_file($var)){
            return 'file';
        }
        if(is_int($var)){
            return 'integer';
        }
        if(is_float($var)){
            return 'float';
        }
        if(is_bool($var)){
            return 'boolean';
        }
        if(is_string($var)){
            return 'string';
        }
        return null;
    }


    /**
     * internal error bounce back method will load error class if not loaded already
     * and redirect error arguments to Xapp_Error class
     *
     * @error 10122
     * @param string|Exception|Xapp_Error $e expects either string error message or instance of Exception
     * @param int $c expects optional error code
     * @param int $s expects optional serverity
     * @param null|string $f expects the file where error occurred
     * @param null|int $l expects the line where error occurred
     * @return Xapp_Error
     */
    public static function e($e, $c = 0, $s = 0, $f = null, $l = null)
    {
        if(!self::imported('Xapp_Error'))
        {
            self::import('xapp.Xapp.Error');
        }
        return Xapp_Error::e($e, $c, $s, $f, $l);
    }


    /**
     * xapp debug reviever message called by xapp_debug outside in core.php will store
     * debug messages in array to be flushed to screen in shutdown
     *
     * @error 10123
     * @param mixed $m expects the debug message as string or any other value
     * @param array $a expects optional arguments
     * @return void
     */
    public static function d($m, Array $a = null)
    {
        if(sizeof(self::$_debug) === 0)
        {
            array_push(self::$_debug, array("start xapp debug modus", microtime(true), memory_get_usage(true)));
        }else{
            $t = array();
            foreach(new RecursiveIteratorIterator(new RecursiveArrayIterator(array_filter((array)$m)), RecursiveIteratorIterator::LEAVES_ONLY) as $v)
            {
                $t[] = $v;
            }
            $m = implode(', ', $t);
            if($a !== null)
            {
                $m = @vsprintf($m, $a);
            }
            array_push(self::$_debug, array($m, microtime(true), memory_get_usage(true)));
        }
    }


    /**
     * xapp shutdown function handles errors and debug messages if xapps internal shutdown
     * functionality is used. if xapp shutdown is not used user must implement custom error
     * debug handling
     *
     * @error 10124
     * @return void
     */
    final protected static function shutdown()
    {
        xapp_debug('xapp is shutting down', 'xapp');

        if((bool)xapp_conf(XAPP_CONF_PROFILER_MODE))
        {
            xapp_profile('xapp', 'shutdown', microtime(true) - XAPP_START);
        }

        $e = error_get_last();

        if(self::imported('Xapp_Error'))
        {
            if($e !== null && Xapp_Error::hasStack())
            {
                $s = Xapp_Error::getStack();
                $s = array_pop($s);
                if(strtolower($e['message']) !== strtolower($s->getMessage()))
                {
                    xapp_error($e);
                }
            }else if($e != null && !Xapp_Error::hasStack()){
                xapp_error($e);
            }
            if(Xapp_Error::hasStack())
            {
                $log = xapp_conf(XAPP_CONF_LOG_ERROR);
                if(is_object(xapp_conf(XAPP_CONF_LOG_ERROR)))
                {
                    if($log instanceof Xapp_Log)
                    {
                        $log->log(Xapp_Error::getStack());
                    }else{
                        foreach(Xapp_Error::getStack() as $k => $v)
                        {
                            $log->log($v->getMessage(), $v->getCode());
                        }
                    }
                }else if(is_string($log) && is_writable($log)){
                    if(!is_file($log))
                    {
                        $log = rtrim($log, DS) . DS . strftime("%Y%m%d", time()) . '-error.log';
                    }
                    foreach(Xapp_Error::getStack() as $k => $v)
                    {
                        error_log($v->getMessage(), 3, $log);
                    }
                }else{
                    if((bool)xapp_conf(XAPP_CONF_DEV_MODE) && !(bool)xapp_ini('display_errors'))
                    {
                        foreach(Xapp_Error::getStack() as $k => $v)
                        {
                            echo ((strtolower(php_sapi_name()) === 'cli') ? print_r($v, true) : "<pre>".print_r($v, true)."</pre>");
                        }
                    }
                }
            }
        }

        xapp_event('xapp.shutdown');

        $debug = xapp_conf(XAPP_CONF_DEBUG_MODE);
        if($debug !== null && $debug !== false)
        {
            $i = 0; $s = 0; $t = "";
            foreach(self::$_debug as $k => &$v)
            {
                if($i === 0)
                {
                    $s = $v[1]; $v[1] = 0;
                }else{
                    $v[1] = round($v[1] - $s, 4);
                }
                $i++;
            }
            if((int)$debug === 1)
            {
                Xapp_Debug::dump(self::$_debug, null, true);
            }else if((int)$debug === 2 && !is_null(xapp_path(XAPP_PATH_LOG))){
                @file_put_contents(xapp_path(XAPP_PATH_LOG) . 'debug.log', Xapp_Debug::dump(self::$_debug, 'cli', false));
            }else if((int)$debug === 3 && !is_null(xapp_path(XAPP_PATH_LOG))){
                @file_put_contents(xapp_path(XAPP_PATH_LOG) . 'debug.log', "[" . strftime('%D %T', time()) . "]\n" . Xapp_Debug::dump(self::$_debug, 'cli', false), FILE_APPEND);
            }else{
                self::$_debug = array();
            }
        }

        if(ob_get_level() > 0)
        {
            while(ob_get_level() > 0){ @ob_end_flush(); }
        }
        @flush();

        if($e !== null && in_array((int)$e['type'], array(E_ERROR, E_CORE_ERROR)) && !(bool)xapp_ini('display_errors'))
        {
            xapp_ini('display_errors', 1);
            die("xapp aborted due to fatal error: " . $e['message'] . " on line: " . $e['line'] . " in file: " . $e['file'] . PHP_EOL);
        }
    }


    /**
     * xapp internal output buffer handler if used will compress outour to gzip if option is set to compress
     *
     * @error 10125
     * @param string $buffer expects the output buffer string
     * @return string the buffer
     */
    public static function bufferHandler($buffer = '')
    {
        xapp_event("xapp.buffer", array(&$buffer));

	    if((bool)xapp_conf(XAPP_CONF_HTTP_GZIP) && !headers_sent() && isset($_SERVER["HTTP_ACCEPT_ENCODING"]))
        {
            if(stripos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== false || stripos($_SERVER["HTTP_ACCEPT_ENCODING"], 'x-gzip') !== false)
            {
	            if(stripos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== false)
                {
                    $encoding = 'gzip';
                }else{
                    $encoding = 'x-gzip';
                }
                $buffer = "\x1f\x8b\x08\x00\x00\x00\x00\x00"
                    . substr(gzcompress($buffer, 2), 0, -4)
                    . pack('V', crc32($buffer))
                    . pack('V', mb_strlen($buffer, "latin1"));
                header("Connection: close\r\n");
                header("Content-Encoding: " . $encoding . "\r\n");
                header("Content-Length: " . mb_strlen($buffer, "latin1"));
                header("X-Powered-By: " . __CLASS__);
            }
        }
        return $buffer;
    }


    /**
     * xapp internal shutdown handler redirect function
     *
     * @error 10126
     * @return bool false
     */
    public static function shutdownHandler()
    {
        self::shutdown();
        return false;
    }


    /**
     * returns class name if class is stringified
     *
     * @error 10127
     * @return string
     */
    public function __toString()
    {
        return strtolower(get_called_class());
    }
}