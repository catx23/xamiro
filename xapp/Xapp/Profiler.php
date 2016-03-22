<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Profiler class
 *
 * @package Xapp
 * @class Xapp_Profiler
 * @error 152
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Profiler
{
    /**
     * constant the defines the profiler output mode with available options
     * 1) boolean true = output is flushed to screen/console after xapp shutdown
     * 2) boolean false = nothing is logged or flushed
     * 3) int 1 = see 1)
     * 4) int 2 = log to registered php console
     * 5) string console = see 4)
     * 6) instance of Xapp_Console = see 4)
     * 7) instance of Xapp_Log = will log output to log file using log instance
     * 8) valid file = expects a absolute file path to existing log file
     * 9) valid dir = expects a absolute dir path to write log file in
     *
     * @const XAPP_PROFILER_MODE
     */
    const MODE                  = 'XAPP_PROFILER_MODE';

    /**
     * defines the time factor when calculation time units default to 1000 which
     * default to display in ms. use 1 for showing in seconds
     *
     * @const XAPP_PROFILER_TIME_FACTOR
     */
    const TIME_FACTOR           = 'XAPP_PROFILER_TIME_FACTOR';

    /**
     * defines whether to profile sql query performance by default or not
     *
     * @const XAPP_PROFILER_PROFILE_SQL
     */
    const PROFILE_SQL           = 'XAPP_PROFILER_PROFILE_SQL';

    /**
     * defines whether to profile log messages/objects by default or not
     *
     * @const XAPP_PROFILER_PROFILE_ERROR
     */
    const PROFILE_ERROR         = 'XAPP_PROFILER_PROFILE_ERROR';

    /**
     * defines whether to include memory and peak memory in profiling output
     *
     * @const XAPP_PROFILER_PROFILE_MEMORY
     */
    const PROFILE_MEMORY        = 'XAPP_PROFILER_PROFILE_MEMORY';

    /**
     * defines the array value string separator when array data is compiled to string
     *
     * @const XAPP_PROFILER_STRING_SEPARATOR
     */
    const STRING_SEPARATOR      = 'XAPP_PROFILER_STRING_SEPARATOR';


    /**
     * stores all profiling entries to be flushed on shutdown
     *
     * @var array
     */
    protected $_data = array();

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::MODE              => array(XAPP_TYPE_INT, XAPP_TYPE_BOOL, XAPP_TYPE_STRING, XAPP_TYPE_FILE, XAPP_TYPE_DIR, 'Xapp_Console', 'Xapp_Log'),
        self::TIME_FACTOR       => XAPP_TYPE_INT,
        self::PROFILE_SQL       => XAPP_TYPE_BOOL,
        self::PROFILE_ERROR     => XAPP_TYPE_BOOL,
        self::PROFILE_MEMORY    => XAPP_TYPE_BOOL,
        self::STRING_SEPARATOR  => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::MODE              => 1,
        self::TIME_FACTOR       => 1,
        self::PROFILE_SQL       => 1,
        self::PROFILE_ERROR     => 1,
        self::PROFILE_MEMORY    => 1,
        self::STRING_SEPARATOR  => 1,
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::MODE              => 1,
        self::TIME_FACTOR       => 1000,
        self::PROFILE_SQL       => true,
        self::PROFILE_ERROR     => false,
        self::PROFILE_MEMORY    => true,
        self::STRING_SEPARATOR  => ', '
    );


    /**
     * constructor initializes options and registers profile listeners according
     * to the set options
     *
     * @error 15201
     * @param null|mixed $options expects optional options
     */
    public function __construct($options = null)
    {
        xapp_init_options($options, $this);
        if(xapp_is_option(self::PROFILE_SQL, $this))
        {
            Xapp_Event::listen('xapp.orm.query', function($sql, $params, $time)
            {
                foreach($params as $p)
                {
              		$sql = preg_replace('/\?/i', Xapp_Orm::quote($p), $sql, 1);
              		$sql = htmlspecialchars($sql);
                }
                xapp_profile('query', $sql, $time);
          	});
        }
        if(xapp_is_option(self::PROFILE_ERROR, $this))
        {
            Xapp_Event::listen('xapp.error', function($e)
            {
                xapp_profile('error', $e);
          	});
        }
        if(xapp_get_option(self::MODE, $this) === 1 || xapp_get_option(self::MODE, $this) === true)
        {
            Xapp_Event::listen('xapp.shutdown', function()
            {
                xapp_profile(false);
            });
        }
    }


    /**
     * time the execution of a function/closure or callback passed in second parameter
     *
     * @error 15202
     * @param string $name expects the name id of the profile time log
     * @param callback $function expects a valid php callback
     * @return void
     */
    public function time($name, $function)
   	{
   		$start = microtime(true);
        call_user_func($function);
   		$end = microtime(true);

        $this->log($this->compile(trim($name), $end - $start));
   	}


    /**
     * tick function that will profile passed time unit every time the tick function is called resulting
     * in tick counting and elapsed time for each tick.
     *
     * @error 15203
     * @param null|string $name expects an optional tick name id
     * @return void
     */
    public function tick($name = null)
    {
        if($name !== null)
        {
            $name = trim($name);
        }else{
            $name = 'tick';
        }
        if(!array_key_exists($name, $this->_data))
        {
            $this->log($this->compile($name, microtime(true) - XAPP_START, array(1, microtime(true))));
        }else{
            $this->log($this->compile($name, microtime(true) - $this->_data[$name][sizeof($this->_data[$name]) - 1][3], array(sizeof($this->_data[$name]) + 1, microtime(true))));
        }
    }


    /**
     * profile boiler function will not only feed the tick and time method according to parameter
     * values passed but also log a normal profiling entry with variable log message in second
     * parameter. calling this function with all parameters null or only first parameter not null
     * will invoke the tick method. if the second parameter is a callable will invoke the time method.
     * is the first parameter boolean false will stop profiling and dump profiling data to screen/console.
     *
     * @error 15204
     * @param null|bool|string $name expects a valid value as explained above
     * @param null|mixed|callable $message expects a valid value as explained above
     * @param null|float $time expects an optional float microsecond value
     * @return void
     */
    public function profile($name = null, $message = null, $time = null)
    {
        if($name === false)
        {
            $this->dump();
            return;
        }

        if($time === null)
        {
            $time = microtime(true) - XAPP_START;
        }else{
            $time = (float)$time;
        }

        if($name === null || ($name !== null && $message === null))
        {
            $this->tick($name);
        }else if(is_callable($message)){
            $this->time($name, $message);
        }else{
            $this->log($this->compile($name, $time, array($message)));
        }
    }


    /**
     * compile a valid profile entry expecting a entry name id and a valid time value
     *
     * @error 15205
     * @param string $name expects a valid entry name id
     * @param float $time expects a valid microsecond value
     * @param array $args expects optional data to include in entry compiling
     * @return array
     */
    protected function compile($name, $time, Array $args = array())
    {
        if(!array_key_exists($name, $this->_data))
        {
            $this->_data[$name] = array();
        }

        if(!function_exists('_xapp_compile'))
        {
            function _xapp_compile($args, $sep = ', ')
            {
                foreach($args as $k => &$v)
                {
                    if(is_array($v))
                    {
                        $v = implode((string)$sep, $v);
                    }
                }
                return $args;
            }
        }
        $args = _xapp_compile($args, xapp_get_option(self::STRING_SEPARATOR, $this));

        $log = array
        (
            $name,
            number_format((float)$time * xapp_get_option(self::TIME_FACTOR, $this), 2, '.', '')
        );
        $log = array_merge($log, $args);
        if(xapp_is_option(self::PROFILE_MEMORY, $this))
        {
            $log = array_merge($log, array
            (
                self::byteConv(memory_get_usage(true)),
                self::byteConv(memory_get_peak_usage(true)))
            );
        }
        return $this->_data[$name][] = $log;
    }


    /**
     * log any data to target specified by XAPP_PROFILER_MODE. all not string values
     * will be compiled to string separated values as defined by XAPP_PROFILER_STRING_SEPARATOR and
     * according to the profiler mode send/executed by the target value. see XAPP_PROFILER_MODE
     * option for more info.
     *
     * @error 15205
     * @return void
     */
    public function log()
    {
        $log = array();
        $mode = xapp_get_option(self::MODE, $this);
        $separator = xapp_get_option(self::STRING_SEPARATOR, $this);

        if(func_num_args() > 0)
        {
            foreach(func_get_args() as $a)
            {
                if(is_array($a))
                {
                    $log[] = implode($separator, $a);
                }else{
                    $log[] = $a;
                }
            }
            $log = implode($separator, $log);

            switch($mode)
            {
                case ($mode === 1 || $mode === true):
                    //do nothing since data will be flushed on shutdown
                    break;
                case ($mode === 2 || $mode === 'console'):
                    xapp_console($log, 'profiler');
                    break;
                case ($mode instanceof Xapp_Console):
                    $mode->log($log, 'profiler');
                    break;
                case ($mode instanceof Xapp_Log):
                    $mode->log($log);
                    break;
                case (is_file($mode)):
                    file_put_contents($mode, "$log\n", FILE_APPEND);
                    break;
                case (is_dir($mode)):
                    file_put_contents(rtrim($mode, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'profiler.log', "$log\n", FILE_APPEND);
                    break;
                default:

            }
        }

        $log = null;
    }


    /**
     * convert bytes to human readable value
     *
     * @error 15206
     * @param (int) $bytes expects the byte value to convert
     * @return string
     */
    public static function byteConv($bytes)
    {
        if($bytes > 0)
        {
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');

            if (array_key_exists($unit, $units) === true)
            {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }
        return $bytes;
    }


    /**
     * dump profiler data using Xapp_Debug dump method flushing all profiler data to empty it
     *
     * @error 15207
     * @return void
     */
    public function dump()
    {
        $separator = xapp_get_option(self::STRING_SEPARATOR, $this);

        if(!empty($this->_data))
        {
            foreach($this->_data as $key => $val)
            {
                Xapp_Debug::dump("$key:");
                foreach($val as $k => $v)
                {
                    Xapp_Debug::dump('... ' . implode($separator, array_slice($v, 1)));
                }
            }
        }
        $this->_data = array();
    }
}