<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Log.Interface');
xapp_import('xapp.Log.Exception');

/**
 * Log abstract class
 *
 * @package Log
 * @class Xapp_Log
 * @error 115
 * @author Frank Mueller
 */
abstract class Xapp_Log implements Xapp_Log_Interface, Xapp_Singleton_Interface
{
    /**
     * option email can contain email recipient(s) or mail class instance to write log message to email and send
     *
     * @const EMAIL
     */
    const EMAIL                     = 'LOG_EMAIL';

    /**
     * option path expects the path to log location or complete log file pointer
     *
     * @const PATH
     */
    const PATH                      = 'LOG_PATH';

    /**
     * option name expects optional log name
     *
     * @const NAME
     */
    const NAME                      = 'LOG_NAME';

    /**
     * option extension contains the log file extension value
     *
     * @const EXTENSION
     */
    const EXTENSION                 = 'LOG_EXTENSION';

    /**
     * option line delimiter contains line feed or eol value
     *
     * @const LINE_DELIMITER
     */
    const LINE_DELIMITER            = 'LOG_LINE_DIVIDER';

    /**
     * option field delimiter contains value to separate fields
     *
     * @const FIELD_DELIMITER
     */
    const FIELD_DELIMITER           = 'LOG_FIELD_DIVIDER';

    /**
     * option entry delimiter contains optional log entry delimiter
     *
     * @const ENTRY_DELIMITER
     */
    const ENTRY_DELIMITER           = 'LOG_ENTRY_DIVIDER';

    /**
     * option value enclosure contains value to enclose each field value
     *
     * @const VALUE_ENCLOSURE
     */
    const VALUE_ENCLOSURE           = 'LOG_VALUE_ENCLOSURE';

    /**
     * option time format contains strftime var string
     *
     * @const TIME_FORMAT
     */
    const TIME_FORMAT               = 'LOG_TIME_FORMAT';

    /**
     * option file format contains default strftime var string
     *
     * @const FILE_FORMAT
     */
    const FILE_FORMAT               = 'LOG_FILE_FORMAR';

    /**
     * option rotation can contain boolean value, empty string and rotation flag: d = daily, w = weekly, m = monthly.
     * when set to false or empty value no rotation is used
     *
     * @const ROTATION
     */
    const ROTATION                  = 'LOG_ROTATION';

    /**
     * option writer can contain log writer instances when using concrete log implementation
     *
     * @const WRITER
     */
    const WRITER                    = 'LOG_WRITER';


    /**
     * contains the log entry stack of all received entries
     *
     * @var array
     */
    public $stack = array();

    /**
     * pause/resume logging
     *
     * @var bool
     */
    public $pause = false;

    /**
     * contains all registered Xapp_Log_Writer instances
     *
     * @var array
     */
    protected $_writer = array();

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::PATH                  => XAPP_TYPE_STRING,
        self::NAME                  => XAPP_TYPE_STRING,
        self::EXTENSION             => XAPP_TYPE_STRING,
        self::EMAIL                 => array(XAPP_TYPE_STRING, XAPP_TYPE_ARRAY, XAPP_TYPE_CLASS),
        self::LINE_DELIMITER        => XAPP_TYPE_STRING,
        self::FIELD_DELIMITER       => XAPP_TYPE_STRING,
        self::ENTRY_DELIMITER       => XAPP_TYPE_STRING,
        self::VALUE_ENCLOSURE       => XAPP_TYPE_STRING,
        self::TIME_FORMAT           => XAPP_TYPE_STRING,
        self::FILE_FORMAT           => XAPP_TYPE_STRING,
        self::ROTATION              => array(XAPP_TYPE_BOOL, XAPP_TYPE_STRING),
        self::WRITER                => array(XAPP_TYPE_ARRAY)
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::PATH                  => 1,
        self::NAME                  => 0,
        self::EXTENSION             => 0,
        self::EMAIL                 => 0,
        self::LINE_DELIMITER        => 1,
        self::FIELD_DELIMITER       => 1,
        self::ENTRY_DELIMITER       => 1,
        self::VALUE_ENCLOSURE       => 1,
        self::TIME_FORMAT           => 1,
        self::TIME_FORMAT           => 1,
        self::ROTATION              => 1,
        self::WRITER                => 0
    );


    /**
     * class factory function to create Xapp_Log concrete class instance
     *
     * @error 11501
     * @param string $driver expects the driver string of Xapp_Log implementation
     * @param null|mixed $options expects options object to be passed to instance of driver
     * @return mixed instance of Xapp_Log
     * @throws Xapp_Log_Exception
     */
    public static function factory($driver, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(trim((string)$driver));
        if(class_exists($class))
        {
            return new $class($options);
        }else{
            throw new Xapp_Log_Exception(xapp_sprintf(_("unable to create class for driver: %s"), $driver), 1150101);
        }
    }


    /**
     * class singleton factory function to create Xapp_Log concrete class instance
     *
     * @error 11502
     * @param string $driver expects the driver string of Xapp_Log implementation
     * @param null|mixed $options expects options object to be passed to instance of driver
     * @return mixed instance of Xapp_Log
     * @throws Xapp_Log_Exception
     */
    public static function singletonFactory($driver, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(trim((string)$driver));
        if(class_exists($class))
        {
            return $class::instance($options);
        }else{
            throw new Xapp_Log_Exception(xapp_sprintf(_("unable to create singleton instance for driver: %s"), $driver), 1150201);
        }
    }


    /**
     * register instance of Xapp_Log_Writer for routing log messages to any of the writers. with second parameter an alias
     * can be defined to get a specify Xapp_Log_Writer instance from the register container array. NOTE: if no alias is
     * passed the instance unique spl hash id is used.
     *
     * @error 11503
     * @param Xapp_Log_Writer $writer expects instance of log writer implementation
     * @param null|string $alias expects optional alias value
     * @return Xapp_Log
     * @throws Xapp_Log_Exception
     */
    public function register(Xapp_Log_Writer $writer, $alias = null)
    {
        if($alias !== null)
        {
            $alias = strtolower(trim((string)$alias));
            if(!array_key_exists($alias, $this->_writer))
            {
                $this->_writer[$alias] = $writer;
            }else{
                throw new Xapp_Log_Exception(xapp_sprintf(_("log writer under the same alias: %s already exists"), $alias), 1150301);
            }
        }else{
            $this->_writer[(string)$writer] = $writer;
        }
        return $this;
    }


    /**
     * unregister log writer instance. if the alias is not passed all registered log writers will be swept. if alias only
     * the log writer with this alias
     *
     * @error 11504
     * @param null|string $alias expects an optional alias value
     * @return void
     */
    public function unregister($alias = null)
    {
        if($alias !== null)
        {
            $alias = strtolower(trim((string)$alias));
            if(array_key_exists($alias, $this->_writer))
            {
                unset($this->_writer[$alias]);
            }
        }else{
            $this->_writer = array();
        }
    }


    /**
     * returns array of registered log writers if first argument is not set or null or returns log writer for alias if
     * first argument is set and alias string. NOTE: returns reference to log writer instance(s) in order to manipulate
     * log writer properties in run time
     *
     * @error 11514
     * @param null|string $alias expects optional alias
     * @return array|Xapp_Log
     * @throws Xapp_Log_Exception
     */
    public function writer($alias = null)
    {
        if($alias !== null)
        {
            $alias = strtolower(trim((string)$alias));
            if(array_key_exists($alias, $this->_writer))
            {
                return $this->_writer[$alias];
            }else{
                throw new Xapp_Log_Exception(xapp_sprintf(_("log writer under the same alias: %s does not exist"), $alias), 1151401);
            }
        }else{
            return $this->_writer;
        }
    }


    /**
     * reset a active logger instance by sweeping the log messages stack this function sould be called always by an instance,
     * e.g. $log->reset() or if used static by the log class used, e.g. Xapp_Log_Error::reset
     *
     * @error 11505
     * @return void
     * @throws Xapp_Log_Exception
     */
    public static function reset()
    {
        $class = self::callee(null, 2);
        if(is_object($class) && $class instanceof Xapp_Log){
            $class->stack = array();
        }else if(is_subclass_of($class, __CLASS__)){
            $class::instance()->stack = array();
        }else{
            throw new Xapp_Log_Exception(_("unable to reset logger since caller is obscured"), 1150501);
        }
    }


    /**
     * pause a active logger instance. this function should be called always by an instance, e.g. $log->reset() or if used
     * static by the log class used, e.g. Xapp_Log_Error::reset
     *
     * @error 11506
     * @return void
     * @throws Xapp_Log_Exception
     */
    public static function pause()
    {
        $class = self::callee(null, 2);
        if(is_object($class) && $class instanceof Xapp_Log){
            $class->pause = true;
        }else if(is_subclass_of($class, __CLASS__)){
            $class::instance()->pause = true;
        }else{
            throw new Xapp_Log_Exception(_("unable to pause logger since caller is obscured"), 1150601);
        }
    }


    /**
     * resume a active logger instance. this function should be called always by an instance, e.g. $log->reset() or if
     * used static by the log class used, e.g. Xapp_Log_Error::reset
     *
     * @error 11507
     * @return void
     * @throws Xapp_Log_Exception
     */
    public static function resume()
    {
        $class = self::callee(null, 2);
        if(is_object($class) && $class instanceof Xapp_Log){
            $class->pause = false;
        }else if(is_subclass_of($class, __CLASS__)){
            $class::instance()->pause = false;
        }else{
            throw new Xapp_Log_Exception(_("unable to resume logger since caller is obscured"), 1150701);
        }
    }


    /**
     * tries to determine which class is calling this function either by passing object or calling it statically.
     *
     * @error 11508
     * @param null|object $object expects optional object instance
     * @param int $depth expects the debug depth
     * @return object returns the caller object
     */
    protected static function callee($object = null, $depth = 1)
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
     * make a string md5 identification hash out of a log object/message to avoid duplicate log messages. first parameter
     * can either be a single string, array std object, or instance of exception.
     *
     * @error 11509
     * @param string|array|object|Exception $mixed expects a log object/message
     * @return string the md5 hash
     */
    protected static function hashify($mixed)
    {
        $tmp = array();

        if($mixed instanceof Exception)
        {
            $tmp[] = (string)$mixed->getMessage();
            $tmp[] = (string)$mixed->getFile();
            $tmp[] = (string)$mixed->getLine();
        }else{
            foreach((array)$mixed as $m)
            {
                $tmp[] = (string)$m;
            }
        }
        return md5(mb_strtolower(trim(implode('', $tmp))));
    }


    /**
     * write a message to all registered log writers or a specific log writer in pool by passing an alias. pass optional
     * parameters to log writer instance write method. see log writer implementation for more info. the $message parameter
     * can be string or array/object (std) with key => values pairs. the log writer will try to construct a valid loggable
     * message formatting the incoming message. the function will return whatever the log writer in stack return - either
     * a single value or array if calling all registered writers.
     *
     * @error 11510
     * @param string|array|object $message expects message object see explanation above
     * @param null|string $alias expects optional alias to just write to specific writer
     * @param null|mixed $params expects optional options object
     * @return array|mixed the result of the log writers write function
     * @throws Xapp_Log_Exception
     */
    public function write($message, $alias = null, $params = null)
    {
        $return = array();

        if($alias !== null)
        {
            $alias = strtolower(trim((string)$alias));
            if(array_key_exists($alias, $this->_writer))
            {
                return $this->_writer[$alias]->write($message, $params);
            }else{
                throw new Xapp_Log_Exception(xapp_sprintf(_("log writer for alias: %s does not exist"), $alias), 1151001);
            }
        }else{
            foreach($this->_writer as $k => $writer)
            {
                $return[$k] = $writer->write($message, $params);
            }
        }
        return $return;
    }


    /**
     * get value from global vars array. if second parameter is not set the default $_SERVER array is taken. first parameter
     * key must contain the key to get value for.
     *
     * @error 11511
     * @param string $key expects the global env key
     * @param null|string $env expects the globals array key
     * @return bool|mixed returns the key value or false if not found
     */
    public static function getEnv($key, $env = null)
    {
        $key = trim($key);
        if($env !== null)
        {
            if(is_string($env))
            {
                $env = $GLOBALS[strtoupper(trim($env))];
            }
        }else{
            $env = $_SERVER;
        }
        return ((isset($env[$key])) ? $env[$key] : false);
    }


    /**
     * get current user pid
     *
     * @error 11512
     * @return int
     */
    public static function getPid()
    {
        return (int)getmypid();
    }


    /**
     * get current user ip address or false if not determinable
     *
     * @error 11513
     * @return bool|string
     */
    public static function getIp()
    {
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
        }else if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')){
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')){
            $ip = getenv('REMOTE_ADDR');
        }else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')){
            $ip = $_SERVER['REMOTE_ADDR'];
        }else{
            $ip = false;
        }
        return (($ip !== false && (bool)preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/i', $ip)) ? trim($ip) : false);
    }
}