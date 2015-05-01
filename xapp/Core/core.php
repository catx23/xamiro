<?php

// *********************************************************************************************************************
// xapp core functions. xapp core.php must always be included regardless of what module to use. core.php sets global config
// path, ini, env values and contains all important constants core.php is the very heart of xapp containing also global
// interfaces, facades and most important all pass through functions that can be called from any class and redirects it
// logic to xapp base classes if loaded. xapp core.php is supposed to run without the Xapp base class under xapp/Xapp/Xapp.php
// *********************************************************************************************************************

// *********************************************************************************************************************
// Yes this is xapp! used as core.php include lock
// *********************************************************************************************************************
define('XAPP', true);

// *********************************************************************************************************************
// set xapp application start time
// *********************************************************************************************************************
define('XAPP_START', microtime(true));

// *********************************************************************************************************************
// global xapp interfaces
// *********************************************************************************************************************
interface Xapp_Singleton_Interface{ public static function instance(); }
interface Xapp_Dump_Interface{ public function dump($message); }

// *********************************************************************************************************************
// global facade classes
// *********************************************************************************************************************
class XO extends StdClass{}
class Xapp_Result_Exception extends Exception{}

/**
 * defines what minimum php version is required when using xapp
 * @const XAPP_PHP_VERSION
 */
define('XAPP_PHP_VERSION', '5.2.6');

/**
 * defines the default php extension
 * @const XAPP_EXT
 */
define('XAPP_EXT', '.php');

// *********************************************************************************************************************
// XAPP_ERROR
// error level constants to use system wide in all Xapp_Error and sub class exception calls
// *********************************************************************************************************************

/**
 * error level code that will not be stacked and logged just thrown
 * @const XAPP_ERROR_IGNORE
 */
define('XAPP_ERROR_IGNORE',             -1);
/**
 * error level code for error type notice
 * @const XAPP_ERROR_NOTICE
 */
define('XAPP_ERROR_NOTICE',             LOG_NOTICE);
/**
 * error level code for error type warning
 * @const XAPP_ERROR_WARNING
 */
define('XAPP_ERROR_WARNING',            LOG_WARNING);
/**
 * error level code for error type error
 * @const XAPP_ERROR_ERROR
 */
define('XAPP_ERROR_ERROR',              LOG_ERR);
/**
 * error level code for error type alert
 * @const XAPP_ERROR_ALERT
 */
define('XAPP_ERROR_ALERT',              LOG_ALERT);
/**
 * error level code for error type debug
 * @const XAPP_ERROR_DEBUG
 */
define('XAPP_ERROR_DEBUG',              LOG_DEBUG);

// *********************************************************************************************************************
// XAPP_TYPE
// data type constants to be used for xapps options handling defining the type of option data type to be allowed as option
// value
// *********************************************************************************************************************

/**
 * data type constant for type mixed
 * @const XAPP_TYPE_MIXED
 */
define('XAPP_TYPE_MIXED',               'MIXED');
/**
 * data type constant for type string
 * @const XAPP_TYPE_STRING
 */
define('XAPP_TYPE_STRING',              'STRING');
/**
 * data type constant for type array
 * @const XAPP_TYPE_ARRAY
 */
define('XAPP_TYPE_ARRAY',               'ARRAY');
/**
 * data type constant for type bool
 * @const XAPP_TYPE_BOOL
 */
define('XAPP_TYPE_BOOL',                'BOOL');
/**
 * data type constant for type double
 * @const XAPP_TYPE_DOUBLE
 */
define('XAPP_TYPE_DOUBLE',              'DOUBLE');
/**
 * data type constant for type float
 * @const XAPP_TYPE_FLOAT
 */
define('XAPP_TYPE_FLOAT',               'FLOAT');
/**
 * data type constant for type scalar
 * @const XAPP_TYPE_SCALAR
 */
define('XAPP_TYPE_SCALAR',              'SCALAR');
/**
 * data type constant for type int
 * @const XAPP_TYPE_INT
 */
define('XAPP_TYPE_INT',                 'INT');
/**
 * data type constant for type integer
 * @const XAPP_TYPE_INTEGER
 */
define('XAPP_TYPE_INTEGER',             'INTEGER');
/**
 * data type constant for type null
 * @const XAPP_TYPE_NULL
 */
define('XAPP_TYPE_NULL',                'NULL');
/**
 * data type constant for type numeric
 * @const XAPP_TYPE_NUMERIC
 */
define('XAPP_TYPE_NUMERIC',             'NUMERIC');
/**
 * data type constant for type object
 * @const XAPP_TYPE_OBJECT
 */
define('XAPP_TYPE_OBJECT',              'OBJECT');
/**
 * data type constant for type file
 * @const XAPP_TYPE_FILE
 */
define('XAPP_TYPE_FILE',                'FILE');
/**
 * data type constant for type dir
 * @const XAPP_TYPE_DIR
 */
define('XAPP_TYPE_DIR',                 'DIR');
/**
 * data type constant for type resource
 * @const XAPP_TYPE_RESOURCE
 */
define('XAPP_TYPE_RESOURCE',            'RESOURCE');
/**
 * data type constant for type callable
 * @const XAPP_TYPE_CALLABLE
 */
define('XAPP_TYPE_CALLABLE',            'CALLABLE');
/**
 * data type constant for type class
 * @const XAPP_TYPE_CLASS
 */
define('XAPP_TYPE_CLASS',               'CLASS');
/**
 * data type constant for type date
 * @const XAPP_TYPE_DATE
 */
define('XAPP_TYPE_DATE',                'DATE');
/**
 * data type constant for type date
 * @const XAPP_TYPE_DATETIME
 */
define('XAPP_TYPE_DATETIME',            'DATETIME');


// *********************************************************************************************************************
// XAPP_PATH
// all path values are set and retrieved by xapp_path function. if called without any parameters the first time xapp_path
// will init all paths and determine their default values.
// *********************************************************************************************************************

/**
 * path constant value for xapp base path
 * @const XAPP_PATH_XAPP
 */
define('XAPP_PATH_XAPP',                'XAPP');
/**
 * path constant value for document root
 * @const XAPP_PATH_ROOT
 */
define('XAPP_PATH_ROOT',                'ROOT');
/**
 * path constant value for app base path
 * @const XAPP_PATH_BASE
 */
define('XAPP_PATH_BASE',                'BASE');
/**
 * path constant value for xapp log path
 * @const XAPP_PATH_LOG
 */
define('XAPP_PATH_LOG',                 'LOG');

// *********************************************************************************************************************
// XAPP_CONF
// all the following values conf options have only affect if they are used together with xapp base class Xapp.php as
// application base class and calling xapp::run to start app. the conf values can be initialized inside Xapp.php by passing
// conf array through xapp::run or by calling xapp_conf with the same conf array
// *********************************************************************************************************************

/**
 * conf value charset, if set, expects a valid charset value to be set. e.g. default value is "utf-8" but can by any valid
 * I18N charset. the value will be set in xapp::run if xapp is used and will set the internal app encoding. if the value
 * is set to null|false nothing will be set.
 *
 * @const XAPP_CONF_CHARSET
 */
define('XAPP_CONF_CHARSET',             'CHARSET');

/**
 * conf value language, if set, expects a valid language string used in setting php environment var "language" which is
 * usually the same used for setlocale e.g. "en.UTF8". if value is null|false nothing will be set.
 *
 * @const XAPP_CONF_LANGUAGE
 */
define('XAPP_CONF_LANGUAGE',            'LANGUAGE');

/**
 * conf value locale, if set, expects a valid locate name which is used got setlocale phps native function, e.g. "en.UTF8".
 * the value can be set as single string or array containing multiple values like:
 *
 * <code>
 *      XAPP_CONF_LOCALE => array(LC_TIME => x, LC_NUMERIC => y, ...)
 * </code>
 *
 * if value is null|false nothing is set.
 *
 * @const XAPP_CONF_LOCALE
 */
define('XAPP_CONF_LOCALE',              'LOCALE');

/**
 * conf value timezone, if set, expects the default timezone value as used in php natives function date_default_timezone_set,
 * e.g. UTC. if value is null|false nothing will be set.
 *
 * @conf XAPP_CONF_TIMEZONE
 */
define('XAPP_CONF_TIMEZONE',            'TIMEZONE');

/**
 * conf value autoload, if set, does the following:
 * 1)   if set to true = initialize Xapp_Autoloader inside xapp to use internal autoloader with default options
 * 2)   if set with array = initialize Xapp_Autoloader with specific options - see Xapp_Autoloader second parameter and
 *      class options
 * 3)   if set to null|false = nothing will be set
 * 4)   instance of autoloader for compliance reasons but nothing will happen since its already running
 *
 * @const XAPP_CONF_AUTOLOAD
 */
define('XAPP_CONF_AUTOLOAD',            'AUTOLOAD');

/**
 * conf value handle_error, if set, does the following:
 * 1)   if set to true = sets error handler via php natives set_error_handler function to Xapp_Error error handling and
 *      be doing so let that class handle all the errors with default settings
 * 2)   if set to object or callable = redirects all error handling to that callable or object
 * 3)   if set to null|false = nothing will be set
 *
 * @const XAPP_CONF_HANDLE_ERROR
 */
define('XAPP_CONF_HANDLE_ERROR',        'HANDLE_ERROR');

/**
 * conf value handle_exception, if set, does the same as XAPP_CONF_HANDLE_ERROR but just taking care of handling exceptions
 * instead of errors.
 *
 * @const XAPP_CONF_HANDLE_EXCEPTION
 */
define('XAPP_CONF_HANDLE_EXCEPTION',    'HANDLE_EXCEPTION');

/**
 * conf value handle_shutdown, if set, delegates a shutdown function by either:
 * 1)   if set to true = registers internal xapp base class shutdown function to handle debug and error message as well
 *      as flush all contents
 * 2)   if set to object or callable = sets shutdown according to value passed
 * 3)   if set to null|false = nothing will be set
 * NOTE: when using xapp base module shutdown handler should not be changed and constant passed with true. This will take
 * that error handling will be done right
 *
 * @const XAPP_CONF_HANDLE_SHUTDOWN
 */
define('XAPP_CONF_HANDLE_SHUTDOWN',     'HANDLE_SHUTDOWN');

/**
 * conf value handle_buffer, if set, delegates ob_start output buffer handler by:
 * 1)   if set to true = internal xapp default output buffering will be used
 * 2)   if set to object or callable = redirects output buffer to the passed value
 * 3)   if set to false|null = nothing will be set
 * NOTE: when using xapp base module buffer handling should be left with value true since xapp will take care of settings
 * headers and output compression.
 *
 * @const XAPP_CONF_HANDLE_BUFFER
 */
define('XAPP_CONF_HANDLE_BUFFER',       'HANDLE_BUFFER');

/**
 * conf value handle_error_codes, if set, can pass php native error code bitmasked constants for internal error handling
 * and displaying. the level of error logging, if using xapp to take of error handling, is set like you would with php
 * natives error_reporting function. so:
 * 1)   if set to null|false = xapp will use the php default value or take the values set in error_reporting before if set.
 * 2)   if set to int = e.g. E_ALL will use the bitmasked value to set error_reporing inside xapp and therefore pass these
 *      value also to the error handling capacities of xapp
 * NOTE: if in XAPP_CONF_DEV_MODE all previous values will be overridden to: E_ALL
 * NOTE: if in XAPP_CONF_DEBUG_MODE all previous values will be overridden to: E_ALL ^ E_NOTICE) | E_STRICT)
 * NOTE: if no value is set at all xapp will set the default value: E_ALL & ~E_NOTICE & ~E_STRICT
 *
 * @const XAPP_CONF_HANDLE_ERROR_CODES
 */
define('XAPP_CONF_HANDLE_ERROR_CODES',  'HANDLE_ERROR_CODES');

/**
 * conf value log_error, if set, will define how to use error logging by:
 * 1)   if set to true = will try to initialize Xapp_Log_Error to be used as default error logger with default options
 * 2)   if set to Xapp_Log_Error instance = will take instance to be used as error logger with all logger options set
 * 3)   if set to object = will look for public method log($message) in passed instance because only then 3rd party logger
 *      will be accepted to work with xapp base class
 * 4)   if set as string representing a log file absolute file or dir path will log all errors using xapp error logger
 *      class or phps native error_log function to passed log file or directory
 * 5)   if set to null|false = no logging will be used
 * NOTE: if no logging will be used and in XAPP_CONF_DEV_MODE all errors will be printed to screen
 *
 * @const XAPP_CONF_LOG_ERROR
 */
define('XAPP_CONF_LOG_ERROR',           'LOG_ERROR');

/**
 * conf value display_error, if set, will define how to display errors by:
 * 1)   if set and not null = is setting the value for phps native ini value display_error inside xapp
 * 2)   if set to null = nothing will be set
 * NOTE: if in XAPP_CONF_DEV_MODE and display_errors has been set to 0 previously, xapp will force display to 1
 *
 * @const XAPP_CONF_DISPLAY_ERROR
 */
define('XAPP_CONF_DISPLAY_ERROR',       'DISPLAY_ERROR');

/**
 * conf value memory_limit, if set, will set the php native ini value memory_limit by expecting as value other then null|false
 *
 * @const XAPP_CONF_MEMORY_LIMIT
 */
define('XAPP_CONF_MEMORY_LIMIT',        'MEMORY_LIMIT');

/**
 * conf value execution_time, if set, will set the php native ini value execution time through phps native function
 * set_time_limit expecting a value other then null|false NOTE: if xapp is run under cli execution time is overwritten by
 * 0 = endless
 *
 * @const XAPP_CONF_EXECUTION_TIME
 */
define('XAPP_CONF_EXECUTION_TIME',      'EXECUTION_TIME');

/**
 * conf value dev_mode, if set to true or 1, puts xapp into dev mode doing the following:
 * 1)   override XAPP_CONF_DISPLAY_ERROR value so errors are always displayed if necessary
 * 2)   override XAPP_CONF_HANDLE_ERROR_CODES value so all errors are always displayed and/or logged
 * dev mode constant can have the following values
 * 1)   if set to true|1 = will put xapp into dev mode as explained above
 * 2)   if set to 2 = will put xapp into dev strict mode
 * 3)   if set to null|false xapp does not run in dev mode
 * NOTE: when in strict dev mode = 2 the following happens on top of default dev mode 1:
 * 1)   all argument missing php warning will throw errors making sure that all missing argument warning, which are errors!,
 *      are forced to correct!
 *
 * @const XAPP_CONF_DEV_MODE
 */
define('XAPP_CONF_DEV_MODE',            'DEV_MODE');

/**
 * conf value debug mode, if set, determines xapps debug message handling like:
 * 1)   if set to true|1 = will print all debug messages to screen in shutdown
 * 2)   if set to 2 = will log debug messages to default log location overwriting previous log
 * 3)   if set to 3 = will log debug messages to default log location appending to log file
 * 4)   if set to 4 = will be routed directly to php console if Xapp_Console is used or driver has been specified for
 *      XAPP_CONF_CONSOLE
 * 5)   if set to "console" see 4)
 * 6)   if set to null|false = ignore debug messages and discard them
 * NOTE: since all debug messages are first send to xapp_debug function all debug methods can be intercepted by overwriting
 * xapp_debug
 *
 * @const XAPP_CONF_DEBUG_MODE
 */
define('XAPP_CONF_DEBUG_MODE',          'DEBUG_MODE');

/**
 * conf value profiler mode, if set and using xapp base class Xapp.php will auto profile xapp native functionality like
 * xapp start, shutdown and sql queries and also any custom profiling. the following modes are supported:
 * 1)   if set to true|1 = will print profiling result to screen/console on shutdown
 * 2)   if set to 2 = will send profiling data to xapp php browser console
 * 3)   if set to "console" = see 2)
 * 4)   if set to instance of Xapp_Console = see 2)
 * 5)   if set to instance of Xapp_Log = will write profiling data to log file according to log instance options
 * 6)   if set to absolute file location = will log to that file if that file exists
 * 7)   if set to absolute path directory = will create a new profiling log file in this directory
 * 8)   if set to null|false|0 = profiling is omitted
 * 9)   if set to instance of Xapp_Profiler = will use that instance for profiling instead of default instance
 * NOTE: since all profiling messages are first send to xapp_profile function all profiling data can be intercepted by
 * overwriting xapp_profile
 *
 * @const XAPP_CONF_PROFILER_MODE
 */
define('XAPP_CONF_PROFILER_MODE',       'PROFILER_MODE');

/**
 * conf value system_email, if set, is used in conjunction with xapp internal error logging to dispatch severe errors to
 * the passed e-mail address expecting the mail system to work if set to null|false remains unused
 *
 * @const XAPP_CONF_SYSTEM_EMAIL
 */
define('XAPP_CONF_SYSTEM_EMAIL',        'SYSTEM_MAIL');

/**
 * conf value http_gzip, if set, is used in xapp internal output buffer handling to compress als HTTP response output with
 * gzip. if left to null|false nothing will be compressed
 *
 * @const XAPP_CONF_HTTP_GZIP
 */
define('XAPP_CONF_HTTP_GZIP',           'HTTP_GZIP');

/**
 * the date format used within the application as defined on php.net/manual/en/function.strftime.php acts as a default
 * value through out the application
 *
 * @const XAPP_CONF_DATETIME_FORMAT
 */
define('XAPP_CONF_DATE_FORMAT',         'DATE_FORMAT');

/**
 * the datetime format used within the application as defined on php.net/manual/en/function.strftime.php acts as a default
 * value through out the application
 *
 * @const XAPP_CONF_DATETIME_FORMAT
 */
define('XAPP_CONF_DATETIME_FORMAT',     'DATETIME_FORMAT');

/**
 * the xapp console driver if used will route all calls from xapp_console to driver instance for inline browser console
 * logging. can only be used when xapp base class is used. use value null or false if you dont want to use console at all.
 * supported drivers are "firephp" and "chromephp".
 *
 * @const XAPP_CONF_CONSOLE
 */
define('XAPP_CONF_CONSOLE',             'CONSOLE');

/**
 * xapp is capable of java style import through dot and wildcard notation e.g. xapp_import('foo.Base') or xapp_import('foo.Base.*')
 * to load packages. the default import path is the xapp base path. you can define user import paths by passing an string
 * or array of absolute paths (!) to this conf constant when settings xapp config values in your app. when set to null or
 * false will be ignored. when xapp´s internal autoloader is activated will pass the the import paths to the autoloader
 * so using xapp_import in this case would be obsolete. NOTE that import dirs must be readable and exists no errors are
 * thrown!
 *
 * @const XAPP_CONF_IMPORT_PATH
 */
define('XAPP_CONF_IMPORT_PATH',         'IMPORT_PATH');

/**
 * define runtime mode for xapp. currently not used
 *
 * @const XAPP_CONF_RUNTIME
 */
define('XAPP_CONF_RUNTIME',             'RUNTIME');

// *********************************************************************************************************************
// init xapp core by auto setting paths and default conf values to be stored in global scope for latter use. require core
// files
// *********************************************************************************************************************

xapp_init();
xapp_path();
xapp_conf();

require_once xapp_path(XAPP_PATH_XAPP) . 'Core' . DS . 'helper.php';
require_once xapp_path(XAPP_PATH_XAPP) . 'Core' . DS . 'legacy.php';

// *********************************************************************************************************************
// include xapp base files if xapp is used with xapp base functionality required if xapp is used as app builder rather
// then single modules
// *********************************************************************************************************************
if(defined('XAPPED') && (bool)constant('XAPPED') && is_dir(__DIR__ . DS . '..' . DS . 'Xapp'))
{
	require_once xapp_path(XAPP_PATH_XAPP) . 'Xapp' . DS . SOURCE_SEPARATOR . DS . 'Autoloader.php';
	require_once xapp_path(XAPP_PATH_XAPP) . 'Xapp' . DS . SOURCE_SEPARATOR . DS . 'Error.php';
	require_once xapp_path(XAPP_PATH_XAPP) . 'Xapp' . DS . SOURCE_SEPARATOR . DS . 'Reflection.php';
	require_once xapp_path(XAPP_PATH_XAPP) . 'Xapp' . DS . SOURCE_SEPARATOR . DS . 'Debug.php';
	require_once xapp_path(XAPP_PATH_XAPP) . 'Xapp' . DS . SOURCE_SEPARATOR . DS . 'Event.php';
	require_once xapp_path(XAPP_PATH_XAPP) . 'Xapp' . DS . SOURCE_SEPARATOR . DS . 'Xapp.php';
	require_once xapp_path(XAPP_PATH_XAPP) . 'Xapp' . DS . SOURCE_SEPARATOR . DS . 'Console.php';
	require_once xapp_path(XAPP_PATH_XAPP) . 'Xapp' . DS . SOURCE_SEPARATOR . DS . 'Profiler.php';
}

// *********************************************************************************************************************
// xapp core functions used system wide in the xapp framework
// *********************************************************************************************************************


/**
 * xapp init sets global path/separator values and reacts to magic_quotes_gpc value
 *
 * @return void
 */
function xapp_init()
{
	if(!defined('DIRECTORY_SEPARATOR'))
	{
		define('DIRECTORY_SEPARATOR', ((isset($_ENV['OS']) && strpos('win', $_ENV["OS"]) !== false) ? '\\' : '/'));
	}
	if(!defined('DS'))
	{
		define('DS', DIRECTORY_SEPARATOR);
	}
	if(!defined('PATH_SEPARATOR'))
	{
		define('PATH_SEPARATOR', ':');
	}
	if(!defined('NAMESPACE_SEPARATOR'))
	{
		define('NAMESPACE_SEPARATOR', '\\');
	}
	if(is_dir(rtrim(dirname(__FILE__), DS) . DS . 'branches'))
	{
		define('SOURCE_SEPARATOR', 'src');
	}else{
		define('SOURCE_SEPARATOR', '');
	}
	if(get_magic_quotes_gpc() === 1)
	{
		if(function_exists('json_encode'))
		{
			$_GET       = json_decode(stripslashes(json_encode($_GET, JSON_HEX_APOS)), true);
			$_POST      = json_decode(stripslashes(json_encode($_POST, JSON_HEX_APOS)), true);
			$_COOKIE    = json_decode(stripslashes(json_encode($_COOKIE, JSON_HEX_APOS)), true);
			$_REQUEST   = json_decode(stripslashes(json_encode($_REQUEST, JSON_HEX_APOS)), true);
		}else{
			die('xapp needs json extension installed and enabled to run');
		}
	}
}

/**
 * global conf setter/getter function for xapp that will used inside xapp base class if used or elsewhere with custom
 * implementation. to initialize the first time the function must be called either with an array of conf option to override
 * defaults or call with out parameter to initialize default values. after init the function acts as getter/setter getting
 * an option if first parameter is only present. set values if both parameter are set. NOTE: if you want to make use of
 * xapp as application base user xapp base class and pass conf array to xapp::run
 *
 * @param null|array|string $mixed expects value according to explanation above
 * @param mixed $value expects the conf value if in setter mode
 * @return array the full conf array
 */
function xapp_conf($mixed = null, $value = 'NIL')
{
	$conf = array
	(
		XAPP_CONF_CHARSET               => 'utf-8',
		XAPP_CONF_LANGUAGE              => null,
		XAPP_CONF_LOCALE                => null,
		XAPP_CONF_TIMEZONE              => null,
		XAPP_CONF_AUTOLOAD              => true,
		XAPP_CONF_HANDLE_ERROR          => true,
		XAPP_CONF_HANDLE_EXCEPTION      => true,
		XAPP_CONF_HANDLE_SHUTDOWN       => true,
		XAPP_CONF_HANDLE_BUFFER         => false,
		XAPP_CONF_HANDLE_ERROR_CODES    => null,
		XAPP_CONF_LOG_ERROR             => null,
		XAPP_CONF_DISPLAY_ERROR         => null,
		XAPP_CONF_MEMORY_LIMIT          => '512M',
		XAPP_CONF_EXECUTION_TIME        => 60,
		XAPP_CONF_DEV_MODE              => false,
		XAPP_CONF_DEBUG_MODE            => false,
		XAPP_CONF_SYSTEM_EMAIL          => null,
		XAPP_CONF_HTTP_GZIP             => false,
		XAPP_CONF_DATE_FORMAT           => '%Y-%m-%d',
		XAPP_CONF_DATETIME_FORMAT       => '%Y-%m-%d %H:%M:%S',
		XAPP_CONF_CONSOLE               => null,
		XAPP_CONF_IMPORT_PATH           => null,
		XAPP_CONF_RUNTIME               => 'release',
		XAPP_CONF_PROFILER_MODE         => false
	);

	if(!isset($GLOBALS['XAPP_CONF']))
	{
		$GLOBALS['XAPP_CONF'] = $conf;
	}
	if($mixed !== null)
	{
		if(is_string($mixed) && $value !== 'NIL')
		{
			$mixed = array($mixed => $value);
		}
		if(is_array($mixed))
		{
			foreach($mixed as $k => $v)
			{
				$k = strtoupper(trim($k));
				if(array_key_exists($k, $conf))
				{
					$GLOBALS['XAPP_CONF'][$k] = $v;
				}
			}
			return $GLOBALS['XAPP_CONF'];
		}
		if(is_string($mixed) && $value === 'NIL')
		{
			return ((array_key_exists($mixed, $GLOBALS['XAPP_CONF'])) ? $GLOBALS['XAPP_CONF'][$mixed] : null);
		}
	}
	return $GLOBALS['XAPP_CONF'];
}

/**
 * global path getter/setter function to set all system wide path and store any other path into the global path array.
 * to auto initialize all default xapp paths the function should be called without any parameters. use the function then
 * as setter/getter to get xapp path and set new paths if required. if no parameter is passed will return the whole path
 * array. NOTE: be careful and dont overwrite xapp default paths!
 *
 * @param null|string $key if set will get path value or set the path value for $key if $value is set
 * @param string|null $value if set is the path value in setter mode
 * @return string|null|array according to passed parameter
 */
function xapp_path($key = null, $value = 'NIL')
{
	$path = array
	(
		XAPP_PATH_ROOT => ((isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['SCRIPT_FILENAME'])) ? rtrim(str_ireplace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']) , DS) . DS : rtrim($_SERVER['DOCUMENT_ROOT'], DS) . DS),
		XAPP_PATH_BASE => rtrim(realpath(__DIR__ . DS . '..' . DS . '..'), DS) . DS,
		XAPP_PATH_XAPP => rtrim(realpath(__DIR__ . DS . '..'), DS) . DS
	);
	$dir = rtrim(realpath(__DIR__ . DS . '..' . DS . '..' . DS . '..' . DS . '..'), DS) . DS . 'log' . DS;
	if(is_dir($dir))
	{
		$path[XAPP_PATH_LOG] = $dir;
	}else{
		$path[XAPP_PATH_LOG] = null;
	}
	if(!isset($GLOBALS['XAPP_PATH']))
	{
		$GLOBALS['XAPP_PATH'] = $path;
	}
	if($key !== null && $value === 'NIL')
	{
		return ((array_key_exists($key, $GLOBALS['XAPP_PATH'])) ? $GLOBALS['XAPP_PATH'][$key] : null);
	}else if($key !== null && $value !== ''){
		return $GLOBALS['XAPP_PATH'][$key] = $value;
	}else{
		return $GLOBALS['XAPP_PATH'];
	}
}

/**
 * setter/getter function to set/get system wide environment vars be using phps build in env function putenv/getenv.
 * returns boolean when setting to test if setting was successful and the env kex return value when getting
 *
 * @param null|string $key expects the env key
 * @param null|mixed $value if set and not null for setter mode expects the key value
 * @return bool|string according to passed parameter
 */
function xapp_env($key = null, $value = null)
{
	if($key !== null)
	{
		$key = strtoupper(trim($key));
		if($value !== null)
		{
			putenv("$key=$value");
			return assert(getenv($key) == $value);
		}else{
			return getenv($key);
		}
	}
	return false;
}

/**
 * setter/getter function to set/get system wide php ini values returning the ini value, false if unable to set or an array
 * with all ini values
 *
 * @param null|string $key expects the php ini option to get/set
 * @param null|mixed $value expects the php ini option value if in setter mode
 * @return array|string|boolean according to set parameter
 */
function xapp_ini($key = null, $value = null)
{
	if($key !== null)
	{
		if($value !== null)
		{
			return ini_set($key, $value);
		}else{
			return ini_get($key);
		}
	}else{
		return ini_get_all();
	}
}

/**
 * function to check if xapp base class is used/loaded or if any other classmis a xapp class belonging to the xapp framework.
 *
 * @param   null|object|string $class expects instance of a any object, string name of class or null to just check if xapp
 *          base class is loaded
 * @param   bool $autoload expects a boolean value whether to auto load class if not loaded already
 * @return  bool
 */
function xapped($class = null, $autoload = false)
{
	if($class !== null)
	{
		if(is_object($class))
		{
			$class = get_class($class);
		}
		return (strtolower(substr(trim($class), 0, 4)) === 'xapp' && class_exists($class, (bool)$autoload));
	}else{
		return class_exists('Xapp', (bool)$autoload);
	}
}

/**
 * shortcut function to validate values if of expected type looking for phps native functions and xapps internal data type
 * validation functions
 *
 * @param null|string $type expects the data type to check value for
 * @param null|mixed $value expects the value to check
 * @return bool
 */
function xapp_is($type = null, $value = null)
{
	if($type !== null)
	{
		$type = strtolower(trim($type));
		if(function_exists("is_$type"))
		{
			return (bool)call_user_func("is_$type", $value);
		}
		if(function_exists("xapp_is_$type"))
		{
			return (bool)call_user_func("xapp_is_$type", $value);
		}
	}
	return false;
}

/**
 * xapp java style dot and wildcard class/package loader imports/requires class or packages from the default xapp base
 * path or paths passed as conf value defined by XAPP_CONF_IMPORT_PATH. the import $parameter can be:
 *
 * 1) absolute file like /var/www/app/foo.php - includes file directly
 * 2) relative file like /app/foo.php - tries to include file from known xapp paths
 * 3) java style class xapp.Xapp.Event - imports class
 * 4) java style package xapp.Xapp.* - imports packages
 *
 * this function can not import:
 * 3) absolute dirs like /var/www/app - not supported!
 * 4) relative dirs like /app - not supported!
 *
 * the function store all imported java style classes and packages into a global package cache to check if package has been
 * already imported and therefore does not execute any import code. the function does not throw any error but simply tries
 * to require the $import param with triggering php error when $import value require fails.
 *
 * The import function does have hidden additional parameters when using java style dotmnotation wildcard loading. the
 * hidden function arguments/parameters are:
 * 1 = either a custom base path from where to load the package or an array with regex exclude rules/patterns
 * 2 = an array with regex exclude rules if 1 is a custom base path. call like:
 *
 * <code>
 *      xapp_import('xapp.Package.*, '/custom/path/');
 *      xapp_import('xapp.Package.*, array('/^regex1$/', '/regex2/i'));
 *      xapp_import('xapp.Package.*, '/custom/path/', array('/^regex1$/', '/regex2/i'));
 * </code>
 *
 * NOTE: the regex syntax for the the path/name exclusion argument must be a valid and complete regex pattern! Also the
 * exclude argument MUST be an array!
 *
 * @param string $import expects a a valid import value as defined above
 * @return boolean
 */
function xapp_import($import)
{
	$path = null;

	//if is php includable file with relative or absolute path with DS separator or . dot separator include directly trying to resolve absolute path
	if(preg_match('/(.*)\.(php|php5|phps|phtml|inc)$/i', $import, $m))
	{
		if(stripos($import, DS) === false)
		{
			$import = implode(DS, explode('.', trim($m[1], '.* '))) . '.' . trim($m[2], '.* ');
		}
		if(in_array($import, get_required_files())){ return; }
		if(is_file($import))
		{
			require_once $import;
			return true;
		}
		$class = xapp_path(XAPP_PATH_XAPP) . trim($import, DS);
		if(in_array($class, get_required_files())){ return; }
		if(is_file($class))
		{
			require_once $class;
			return true;
		}
		$class = xapp_path(XAPP_PATH_BASE) . trim($import, DS);
		if(in_array($class, get_required_files())){ return; }
		if(is_file($class))
		{
			require_once $class;
			return true;
		}
		$class = xapp_path(XAPP_PATH_ROOT) . trim($import, DS);
		if(in_array($class, get_required_files())){ return; }
		if(is_file($class))
		{
			require_once $class;
			return true;
		}
		require_once $import;
		//is java style import . dot notation with wildcard
	}else{
		$ns = substr($import, 0, strpos($import, '.'));
		$pk = substr(substr($import, strlen($ns) + 1), 0, strpos(substr($import, strlen($ns) + 1), '.'));
		//if ns is not xapp check if composer autoloader and package to import exists
		if(stripos($ns, 'xapp') === false)
		{

			$ns = substr($import, 0, strpos($import, '.'));
			$pk = substr(substr($import, strlen($ns) + 1), 0, strpos(substr($import, strlen($ns) + 1), '.'));
			//if ns is not xapp check if composer autoloader and package to import exists
			if(stripos($ns, 'xapp') === false) {
				$searchPath = xapp_path(XAPP_PATH_BASE) . 'xapp' . DIRECTORY_SEPARATOR;
				if (!in_array(xapp_path(XAPP_PATH_BASE) . 'autoload.php', get_required_files())) {
					/**
					 * @Core-Hack
					 */

					if (is_file($searchPath . 'autoload.php')) {
						require_once $searchPath . 'autoload.php';
					} else {
						trigger_error(
							"unable to include composer vendor autoloader - please make sure composer is installed",
							E_USER_ERROR
						);
					}
					/*
					if(is_file(xapp_path(XAPP_PATH_BASE) . 'autoload.php'))
					{
						require_once xapp_path(XAPP_PATH_BASE) . 'autoload.php';
					}else{
						trigger_error("unable to include composer vendor autoloader - please make sure composer is installed", E_USER_ERROR);
					}
					*/
				}
				if (!is_dir(
					$searchPath . str_replace(
						'.',
						DS,
						substr($import, 0, strpos($import, '.', (strpos($import, '.') + 1)))
					)
				)
				) {
					trigger_error(
						"composer vendor package: $import does not exist - please make sure package is installed",
						E_USER_ERROR
					);
				}
				return true;
			}
			/*
			if(!in_array(xapp_path(XAPP_PATH_BASE) . 'autoload.php', get_required_files()))
			{
				if(is_file(xapp_path(XAPP_PATH_BASE) . 'autoload.php'))
				{
					require_once xapp_path(XAPP_PATH_BASE) . 'autoload.php';
				}else{
					trigger_error("unable to include composer vendor autoloader - please make sure composer is installed", E_USER_ERROR);
				}
			}
			if(!is_dir(xapp_path(XAPP_PATH_BASE) . str_replace('.', DS, substr($import, 0, strpos($import, '.', (strpos($import, '.') + 1))))))
			{
				trigger_error("composer vendor package: $import does not exist - please make sure package is installed", E_USER_ERROR);
			}
			return true;
			*/
		}
		//if ns is xapp and xapp autoloader is loaded do nothing but only if package is not Ext
		if(stripos($ns, 'xapp') !== false && xapped('Xapp_Autoloader') && Xapp_Autoloader::hasInstance())
		{
			if(stripos($pk, 'Ext') === false)
			{
				return true;
			}
		}
		//init global import array
		if(!isset($GLOBALS['XAPP_IMPORTS']))
		{
			$GLOBALS['XAPP_IMPORTS'] = array();
		}
		//if called class or package is already imported do nothing
		if(in_array($import, $GLOBALS['XAPP_IMPORTS']))
		{
			return true;
		}
		//import single class
		if(substr($import, -1) !== '*')
		{
			$base = explode('.', trim($import, '. '));
			$class = array_pop($base);
			if($base[0] === 'xapp' && SOURCE_SEPARATOR !== '')
			{
				if(!array_key_exists(1, $base))
				{
					array_push($base, $class, SOURCE_SEPARATOR);
				}else{
					$base = array_merge(array_slice($base, 0, 2), array(SOURCE_SEPARATOR), array_slice($base, 2));
				}
			}
			$base = implode(DS, $base);
			$path = xapp_path(XAPP_PATH_BASE);
			$path = array_diff(array_merge((array)$path, (array)xapp_conf(XAPP_CONF_IMPORT_PATH)), array(''), array(1));
			foreach($path as $p)
			{
				$p = rtrim($p, DS) . DS;
				if(is_file($p . $base . DS . $class . XAPP_EXT))
				{
					array_push($GLOBALS['XAPP_IMPORTS'], $import);
					require_once $p . $base . DS . $class . XAPP_EXT;
					return true;
				}
				if(is_file($p . $base . DS . $class . DS . $class . XAPP_EXT))
				{
					array_push($GLOBALS['XAPP_IMPORTS'], $import);
					require_once $p . $base . DS . $class . DS . $class . XAPP_EXT;
					return true;
				}
			}
			trigger_error("unable to import: $import - class not found", E_USER_ERROR);
			//import package
		}else{
			$regex = null;
			if(func_num_args() >= 2)
			{
				$args = func_get_args();
				if(array_key_exists(1, $args) && array_key_exists(2, $args) && !empty($args[2]))
				{
					$path = (array)$args[1];
					$regex = (array)$args[2];
				}else{
					if(is_array($args[1]))
					{
						$regex = $args[1];
					}else if(strpos($args[1], DS) !== false){
						$path = (array)$args[1];
					}
				}
			}
			if($path === null)
			{
				$path = xapp_path(XAPP_PATH_BASE);
				$path = array_diff(array_merge((array)$path, (array)xapp_conf(XAPP_CONF_IMPORT_PATH)), array(''), array(1));
			}
			foreach($path as $p)
			{
				$b = rtrim($p, DS) . DS;
				$p = $b . implode(DS, explode('.', trim($import, '.*'))) . DS;
				$found = false;
				if(($dir = @opendir($p)) !== false)
				{
					while(($file = readdir($dir)) !== false)
					{
						if($file === '.' || $file === '..' || stripos($file, '.svn') !== false)
						{
							continue;
						}else if($regex !== null){
							foreach($regex as $r)
							{
								if((bool)preg_match(trim($r), $file))
								{
									continue;
								}
							}
						}else if(is_dir($p . $file)){
							xapp_import(trim($import, '.*') . '.' . $file . '.*', $b, $regex);
						}else if(strpos($file, XAPP_EXT) !== false){
							$i = trim($import, '.*') . '.' . substr($file, 0, strrpos($file, XAPP_EXT));
							if(!in_array($i, $GLOBALS['XAPP_IMPORTS']))
							{
								require_once $p . $file;
								array_push($GLOBALS['XAPP_IMPORTS'], $i);
								$found = true;
							}
						}
					}
					if($found) break;
				}
			}
			if(!in_array($import, $GLOBALS['XAPP_IMPORTS']))
			{
				array_push($GLOBALS['XAPP_IMPORTS'], $import);
			}
			return true;
		}
	}
	return false;
}


// *********************************************************************************************************************
// xapp overwritable/hookable functions for system wide functionality. each of the following function can be overwritten
// by defining the function before the xapp core.php file is included. see function description on how to overwrite
// *********************************************************************************************************************

if(!function_exists('xapp_event'))
{
	/**
	 * xapp event trigger/listen shortcut function to (re)delegate re(direct) xapp events. the events are triggered from
	 * inside the xapp framework and can be listened to from everywhere if Xapp_Event class is loaded. the event syntax
	 * is a following (action):(namespace).(event) to trigger events: "trigger:xapp.shutdown", "trigger:myevent", to listen
	 * to events: "listen:xapp.shutdown", "listen:xapp.myevent". the xapp event functionality can be used outside of xapp
	 * framework and new events easily defined and triggered. best to use own events with own namespace like "trigger:my.event".
	 * if this function is overwritten events can be handled with custom logic and redirected.
	 *
	 * overwrite like:
	 * <code>
	 *      function xapp_event($event = null, $mixed = null, $bool = false)
	 *      {
	 *          //your custom code here
	 *      }
	 * </code>
	 *
	 * @param   null|string $event expects event command as explained above
	 * @param   null|callable|array $mixed expects according to event action a callable for listeners or a params array
	 *          to be passed to the listener from the trigger
	 * @param   boolean $bool expects a boolean value which is used in xapp listener mode to reset listeners for event.
	 *          in trigger mode to stop bubbling events.
	 * @return  array|mixed|null according to event command
	 */
	function xapp_event($event = null, $mixed = null, $bool = false)
	{
		if($event !== null)
		{
			if(xapped() && xapped('Xapp_Event'))
			{
				if(($pos = strpos($event, ':')) !== false)
				{
					$type = strtolower(substr($event, 0, $pos));
					$event = substr($event, $pos + 1, strlen($event));
					if($type === 'listen' && is_callable($mixed)){
						return Xapp_Event::listen($event, $mixed, $bool);
					}else{
						return Xapp_Event::trigger($event, $mixed, $bool);
					}
				}else{
					return Xapp_Event::trigger($event, $mixed, $bool);
				}
			}
		}
		return null;
	}
}


if(!function_exists('xapp_console'))
{
	/**
	 * xapp console shortcut function will tunnel all debug/console messages to Xapp_Console wrapper class which will handle
	 * redirection to loaded php console class located in /ext directory such as FirePhp for firefox or ChromePhp for chrome
	 * browser. Console logging is be default disabled. enable by overwritting global conf constant XAPP_CONF_CONSOLE with
	 * either:
	 * 1)   true = load default driver for FirePhp
	 * 2)   instance of Xapp_Console = already instantiated wrapper class
	 * 3)   string = defining the driver to load (e.g. firephp, chromephp)
	 * 4)   null|false = to disable console logging
	 * to route xapps default debug messages to php console use xapp conf constant XAPP_CONF_DEBUG_MODE = 4 or "console"
	 * to rout all error messages into php console
	 *
	 * overwrite like:
	 * <code>
	 *      xapp_console($m = null, $l = null, $t = 'info', Array $o = array())
	 *      {
	 *          //your custom code here
	 *      }
	 * </code>
	 *
	 * @param null|mixed $m message - expects any value that can be send to console via the loaded driver
	 * @param null|string $l label - expects an optional label to be display to explain log value
	 * @param string $t type - expects the log type. see Xapp_Console for all log types
	 * @param array $o options - expects further options. see loaded console driver for more
	 * @return void
	 */
	function xapp_console($m = null, $l = null, $t = 'info', Array $o = array())
	{
		if(xapped('Xapp_Console') && (bool)xapp_conf(XAPP_CONF_CONSOLE))
		{
			if(is_object(xapp_conf(XAPP_CONF_CONSOLE)) && xapp_conf(XAPP_CONF_CONSOLE) instanceof Xapp_Console)
			{
				xapp_conf(XAPP_CONF_CONSOLE)->log($m, $l, $t, $o);
			}else if(xapp_conf(XAPP_CONF_CONSOLE) === true){
				Xapp_Console::instance('firephp')->log($m, $l, $t, $o);
			}else{
				Xapp_Console::instance((string)xapp_conf(XAPP_CONF_CONSOLE))->log($m, $l, $t, $o);
			}
		}else{
			xapp_debug($m);
		}
	}
}


if(!function_exists('xapp_profile'))
{
	/**
	 * xapp profiler shortcut function is used system wide to redirect profile data/action back to concrete implementation
	 * which defaults to Xapp_Profile class if xapp is used. this function can be overwritten to implement custom profiling
	 * logic. calling this function with all parameters null or only first parameter not null should be used to emulate a
	 * tick function. if the second parameter is a callable can emulate a tick function with callback or a time the execution
	 * of the call back. the third optional parameter can be used to pass a microtime value representing a start value or
	 * a already timed value. the first parameter could also be a boolean false value expecting the profiler to stop working
	 *
	 * overwrite like:
	 * <code>
	 *      function xapp_profile($name = null, $message = null, $time = null)
	 *      {
	 *          //your custom code here
	 *      }
	 * </code>
	 *
	 * @param null|bool|string $name expects a valid value as explained above
	 * @param null|mixed|callable $message expects a valid value as explained above
	 * @param null|float $time expects an optional float microsecond value
	 * @return void
	 */
	function xapp_profile($name = null, $message = null, $time = null)
	{
		if(xapped() && xapped('Xapp_Profiler', true))
		{
			if(!isset($GLOBALS['XAPP_PROFILER']))
			{
				$GLOBALS['XAPP_PROFILER'] = new Xapp_Profiler();
			}
			$GLOBALS['XAPP_PROFILER']->profile($name, $message, $time);
		}
	}
}


if(!function_exists('xapp_cache'))
{
	/**
	 * xapp cache short cut function for using the caching capabilities of xapp in a intuitive way. this function is
	 * expecting at least one cache instance create with xapp cache class in order to work. if xapp cache class is
	 * not xapped and has no instance registered will return null or false depending on get or set way of calling.
	 * this function will combines the three stages of caching has, get, set. check if a cache entry exists, get the
	 * entry and if not set it. the shortcut function will work with or without namespace identifier. if you want to
	 * use the current instance use the function like:
	 * <code>
	 *      //set
	 *      xapp_cache($key, $value);
	 *      //get
	 *      xapp_cache($key);
	 * </code>
	 *
	 * if you want to refer to your instances create with namespace identifiers do it like:
	 * <code>
	 *      //set
	 *      xapp_cache('ns1', $key, $value);
	 *      //get
	 *      xapp_cache('ns1', $key);
	 * </code>
	 *
	 * the third parameter is the crucial one. if you use anything other then null as value will set cache values
	 * regardless of your intention! the best way to use the cache short cut function is like:
	 * <code>
	 *      //check and get
	 *      if(($result = xapp_cache('ns1', 'test')) === false)
	 *      {
	 *          //set since not cached yet
	 *          $result = xapp_cache('ns1', 'test', array("foo" => "bar"), 60);
	 *      }
	 * </code>
	 *
	 * you can also use the shortcut function to purge/remove all expired cache items calling the function like:
	 * <code>
	 *      xapp_cache();
	 * </code>
	 *
	 * overwrite like:
	 * <code>
	 *      function xapp_cache($with, $key, $value = null, $lifetime = null)
	 *      {
	 *          //your custom code here
	 *      }
	 * </code>
	 *
	 * @param null|string $with expects a namespace identifier or null for targeting current instance
	 * @param string $key expects the cache key string
	 * @param null|mixed $value expects the to be cached value when setting
	 * @param null|int $lifetime expects the optional lifetime value
	 * @return null|mixed
	 */
	function xapp_cache($with, $key, $value = null, $lifetime = null)
	{
		if(xapped() && xapped('Xapp_Cache', false))
		{
			if(Xapp_Cache::hasInstance())
			{
				if(func_num_args() > 0)
				{
					if(func_num_args() >= 3 && $value !== null)
					{
						Xapp_Cache::set($with, $key, $value, $lifetime);
						return $value;
					}else{
						return Xapp_Cache::get($with, $key, ((func_num_args() === 2) ? false : null));
					}
				}else{
					return Xapp_Cache::purge();
				}
			}
		}
		return ((func_num_args() === 2) ? false : null);
	}
}


if(!function_exists('xapp_debug'))
{
	/**
	 * xapp debug shortcut function used system wide in xapp framework and can be overwritten. if xapp base class is used
	 * will redirect debug messages back to xapp::d debug function unless xapp conf constant XAPP_CONF_DEBUG_MODE is set
	 * to 4 or "console" redirecting all debug messages directly to php browser console via the Xapp_Console wrapper class.
	 *
	 * overwrite like:
	 * <code>
	 *      function xapp_debug($m, $l = null, Array $p = array())
	 *      {
	 *          //your custom code here
	 *      }
	 * </code>
	 *
	 * @param mixed $m expects the debug message as string or any other value
	 * @param null|string $l expects optional label
	 * @param array $p expects optional parameter array (currently unused)
	 * @return void
	 */
	function xapp_debug($m, $l = null, Array $p = array())
	{
		if(xapped())
		{
			if(xapped('Xapp_Console') && (bool)xapp_conf(XAPP_CONF_CONSOLE) && ((int)xapp_conf(XAPP_CONF_DEBUG_MODE) === 4 || strtolower((string)xapp_conf(XAPP_CONF_DEBUG_MODE)) === 'console'))
			{
				xapp_console($m, null, 'info', $p);
			}else{
				Xapp::d($m, $p);
			}
		}
	}
}

if(!function_exists('xapp_error'))
{
	/**
	 * xapp error shortcut function used system wide to redirect errors back to xapp base class if xapp base class is used.
	 * this function can be easily overwritten to handle errors thrown by exceptions and internal php errors channeled
	 * through xapps internal error handler defined in XAPP_CONF_HANDLE_ERROR. use the the this function in all derived
	 * exception classes inheriting from phps native ErrorException class in constructor - see Xapp_Error for example. when
	 * implementing your own error handler you can bounce back your errors to this function or overwrite it. if xapp base
	 * class is not used with direct error to default error handling defined either via phps set_error_handler function
	 * or error_log function and triggered by phps trigger_error function. this functions expects exception as well as
	 * errors passed as it there where an exception but only for logging purpose or even act as set_error_handler if needed.
	 * when redirecting all errors to console you must load and enable Xapp_Console by defining an external console driver
	 * like "firephp" and define XAPP_CONF_HANDLE_ERROR with value "console".
	 *
	 * overwrite like:
	 * <code>
	 *      function xapp_error($e, $c = 0, $s = 0, $f = null, $l = null)
	 *      {
	 *          //your custom code here
	 *      }
	 * </code>
	 *
	 * @param   string|Exception|Xapp_Error|array $e expects either string error message, instance of Exception or array
	 *          return from phps native function error_get_last
	 * @param   int $c expects optional error code
	 * @param   int $s expects optional severity
	 * @param   null|string $f expects the file where error occurred
	 * @param   null|int $l expects the line where error occurred
	 * @return  boolean|Xapp_Error returns Xapp_Error instance or false as deault
	 */
	function xapp_error($e, $c = 0, $s = XAPP_ERROR_ERROR, $f = null, $l = null)
	{
		if(xapped())
		{
			xapp_event('xapp.error', array($e));
			//redirect all errors to console
			if(xapped('Xapp_Console') && (bool)xapp_conf(XAPP_CONF_CONSOLE) && strtolower((string)xapp_conf(XAPP_CONF_HANDLE_ERROR) === 'console'))
			{
				if($e instanceof Exception)
				{
					xapp_console($e, $e->getMessage(), 'error');
				}else if(is_array($e) && isset($e['message'])){
					xapp_console($e, $e['message'], 'error');
				}else{
					$std = new stdClass();
					$std->message = (string)$e;
					$std->code = (int)$c;
					$std->severity = (int)$s;
					$std->file = (string)$f;
					$std->line = (int)$l;
					xapp_console($std, $std->message, 'error');
				}
				//redirect all error to xapp error handling
			}else{
				if($e instanceof Xapp_Error)
				{
					//do nothing because all has been done already since instance of Xapp_Error can only exists after constructor has
					//been called and with calling parent constructor and stacking the error all has been done!
				}else if($e instanceof Exception){
					return Xapp::e($e, (int)$c, (int)$s, $f, $l);
				}else if(is_array($e) && isset($e['message'])){
					return Xapp::e($e['message'], (int)$e['type'], XAPP_ERROR_ERROR, $e['file'], $e['line']);
				}else{
					return Xapp::e((string)$e, (int)$c, (int)$s, $f, $l);
				}
			}
		}else{
			xapp_profile('error', $e);
			trigger_error((string)$e, E_USER_ERROR);
		}
		return false;
	}
}

if(!function_exists('xapp_dump'))
{
	/**
	 * xapp generic dump function will try to dump any input in first parameter using the passed value in second parameter
	 * or if not set by default echo and print_r in cli or none cli mode. the first parameter can by anything that can be
	 * printed to screen via print_r function. the second parameter can by a php function, an object or class name the
	 * implements the dump method as public static or none static method. the dump method of object must have its own logic
	 * for dumping objects since this function will do nothing else but calling the method returning void. you can also
	 * use a php function like json_encode in second parameter to encode and dump your input. overwrite like:
	 *
	 * <code>
	 *      function xapp_dump($what, $with = null)
	 *      {
	 *          //your custom code here
	 *      }
	 * </code>
	 *
	 * @param mixed $what expects any type of variable
	 * @param null|string|callable|object $with expects optional value with what to output first parameter
	 * @return void
	 */
	function xapp_dump($what, $with = null)
	{
		ob_start();

		if($with !== null)
		{
			if(is_callable($with))
			{
				echo call_user_func($with, $what);
			}else if(is_object($with) && method_exists($with, 'dump')){
				echo $with->dump($what);
			}else if(is_string($with)){
				if(stripos($with, 'xapp') !== false)
				{
					if(!xapped($with))
					{
						xapp_import(lcfirst(str_replace('_', '.', $with)));
					}
				}
				if(method_exists($with, 'dump'))
				{
					echo call_user_func(array($with, 'dump'), $what);
				}
			}
		}
		if(strlen($o = (string)ob_get_contents()) > 0)
		{
			ob_end_clean();
			echo $o;
		}else{
			@ob_end_clean();
			echo ((strtolower(php_sapi_name()) === 'cli') ? print_r($what, true) : "<pre>".print_r($what, true)."</pre>");
		}
	}
}


if(!function_exists('xapp_config'))
{
	/**
	 * shortcut method for getting config values set by xapp´s built in Xapp_Config class. this shortcut function
	 * behaves like Xapp_Config::retrieve. a config (instance) must be loaded and initialized before using this shortcut
	 * function and if not will return/throw default value.
	 *
	 * @see Xapp_Config::retrieve
	 * @param string $ns expects the namespace
	 * @param null|string $key expects the optional key to get value for
	 * @param null|mixed|Exception $default expects optional default value if namespace/key is not found
	 * @return null|mixed
	 * @throws Exception
	 */
	function xapp_config($ns, $key = null, $default = null)
	{
		if(xapped('Xapp_Config') && Xapp_Config::created())
		{
			return Xapp_Config::retrieve($ns, $key, $default);
		}else{
			return xapp_default($default);
		}
	}
}



// *********************************************************************************************************************
// xapp option handling is a unique feature that can be used in any xapp build in or custom class. xapp option handling
// will ensure that a) always the required options are set, b) only the options are passed which exists, c) the options
// value data type is valid as defined in the options dictionary. to use this feature see a reference class e.g. Xapp_Log_Error
// on how it works. to initialize instance options use either xapp_set_options, or xapp_init_options to also validate if
// required options are set. the xapp options handling can be used out of the box to build generic options arrays with
// setter/getter to use for array pooling. the option handling will work with any class also that  as a minimum implements
// public static/non-static property $options.
//
// the option handling can be fully overwritten to implement custom option handling.
// *********************************************************************************************************************

if(!function_exists('xapp_can_options'))
{
	/**
	 * xapp option shortcut xapp_can_options
	 * checks whether a class has the options property and therefore
	 * can be used for option handling
	 *
	 * @param null|string|object $class expects the class name or object instance
	 * @return bool
	 */
	function xapp_can_options($class = null)
	{
		if($class !== null)
		{
			if(is_object($class))
			{
				$class = get_class($class);
			}
			return (bool)property_exists($class, 'options');
		}
		return false;
	}
}


if(!function_exists('xapp_init_options'))
{
	/**
	 * xapp option shortcut xapp_init_options initializes the passed options in first parameter according to second parameter
	 * which can be of the following type:
	 * 1) array = to use xapp option handling for array building
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 * since the second parameter is passed as reference.
	 *
	 * @param array|Xapp_Option $options expects options to set
	 * @param null|object|string $mixed expects value according to explanation above
	 * @return null
	 */
	function xapp_init_options($options = null, &$mixed = null)
	{
		$options = (array)$options;
		if(is_array($mixed)){
			$mixed = array_merge($mixed, $options);
		}else if(xapped()){
			Xapp::initOptions($options, $mixed);
		}else if(is_object($mixed) && xapp_can_options($mixed)){
			$mixed->options = array_merge($mixed->options, $options);
		}else if(is_string($mixed) && xapp_can_options($mixed)){
			$mixed::$options = array_merge($mixed::$options, $options);
		}
		return null;
	}
}

if(!function_exists('xapp_set_options'))
{
	/**
	 * xapp option shortcut xapp_set_options sets options from first parameter to second parameter merging with existing
	 * options where the second parameter can be:
	 * 1) array = merging both together
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 * unlike xapp_init_options only data type will checked if used with xapp base class
	 *
	 * @param null|array|Xapp_Option $options expects options to set
	 * @param null|object|string $mixed expects value according to explanation above
	 * @return null|array the options array
	 */
	function xapp_set_options($options = null, &$mixed = null)
	{
		if($options !== null)
		{
			$options = (array)$options;
			if(is_array($mixed)){
				return $mixed = array_merge($mixed, $options);
			}else if(xapped()){
				return Xapp::setOptions($options, $mixed);
			}else if(is_object($mixed) && xapp_can_options($mixed)){
				return $mixed->options = array_merge($mixed->options, $options);
			}else if(is_string($mixed) && xapp_can_options($mixed)){
				return $mixed::$options = array_merge($mixed::$options, $options);
			}
		}
		return null;
	}
}

if(!function_exists('xapp_set_option'))
{
	/**
	 * xapp option shortcut xapp_set_option single option setter sets option from first parameter to third parameter as
	 * part of the xapp option functionality. the third parameter can be:
	 * 1) array = setting $key to $mixed
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 *
	 * use the third parameter to reset the array before setting option. this will only have affect on class options
	 * managed by xapp option handling
	 *
	 * @param null|string $key expects the option key name
	 * @param null|mixed $value expects the option key value
	 * @param null|object|string $mixed expects value according to explanation above
	 * @param boolean $reset whether to reset before setting or not
	 * @return null|mixed the just set option value
	 */
	function xapp_set_option($key = null, $value = null, &$mixed = null, $reset = false)
	{
		if($key !== null)
		{
			if(is_array($mixed)){
				return $mixed[$key] = $value;
			}else if(xapped()){
				return Xapp::setOption($key, $value, $mixed, $reset);
			}else if(is_object($mixed) && xapp_can_options($mixed)){
				return $mixed->options[$key] = $value;
			}else if(is_string($mixed) && xapp_can_options($mixed)){
				return $mixed::$options[$key] = $value;
			}
		}
		return null;
	}
}

if(!function_exists('xapp_get_options'))
{
	/**
	 * xapp option shortcut xapp_get_options full option getter gets all options as part of the xapp option functionality.
	 * the first parameter can be:
	 * 1) array = returning $mixed simply
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 *
	 * @param null|object|string|array $mixed expects value according to explanation above
	 * @param array|mixed $default expects default if options array is not found
	 * @return array|mixed|null returns options array
	 */
	function xapp_get_options(&$mixed = null, $default = array())
	{
		if(is_array($mixed)){
			return $mixed;
		}else if(xapped()){
			return (array)Xapp::getOptions($mixed);
		}else if(is_object($mixed) && xapp_can_options($mixed)){
			return (array)$mixed->options;
		}else if(is_string($mixed) && xapp_can_options($mixed)){
			return (array)$mixed::$options;
		}else{
			return xapp_default($default);
		}
	}
}

if(!function_exists('xapp_get_option'))
{
	/**
	 * xapp option shortcut xapp_get_option single option getter to get option $key, the first parameter from $mixed the
	 * second parameter returning third parameter $default if not found. the second parameter can be:
	 * 1) array = returning options $key value if option exists in $mixed
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 *
	 * @param null|string $key expects the options key name to get
	 * @param null|object|string|array $mixed expects optional value according to explanation above
	 * @param null|mixed $default expects the default return value
	 * @return null|mixed the option key value
	 */
	function xapp_get_option($key = null, &$mixed = null, $default = null)
	{
		if($key !== null)
		{
			if(is_array($mixed)){
				return (array_key_exists($key, $mixed)) ? $mixed[$key] : $default;
			}else if(xapped()){
				return Xapp::getOption($key, $mixed, $default);
			}else if(is_object($mixed) && xapp_can_options($mixed)){
				return (array_key_exists($key, $mixed->options)) ? $mixed->options[$key] : $default;
			}else if(is_string($mixed) && xapp_can_options($mixed)){
				return (array_key_exists($key, $mixed::$options)) ? $mixed::$options[$key] : $default;
			}
		}
		return xapp_default($default);
	}
}

if(!function_exists('xapp_has_option'))
{
	/**
	 * xapp option shortcut xapp_has_option check whether first parameter the option $key exists in second parameter which
	 * can be:
	 * 1) array = array to check for key
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 * if the third parameter is true will not only set for existence but also for a value which is not null and not false
	 * and is not empty
	 *
	 * @param null|string $key expects the options key name to check
	 * @param null|object|string|array $mixed expects value according to explanation above
	 * @param bool $strict expects strict value
	 * @return bool
	 */
	function xapp_has_option($key = null, &$mixed = null, $strict = false)
	{
		if($key !== null)
		{
			$strict = (bool)$strict;
			if(is_array($mixed)){
				return (($strict) ? (array_key_exists($key, $mixed) && xapp_is('value', $mixed[$key])) : array_key_exists($key, $mixed));
			}else if(xapped()){
				return Xapp::hasOption($key, $mixed, $strict);
			}else if(is_object($mixed) && xapp_can_options($mixed)){
				return (($strict) ? (array_key_exists($key, $mixed->options) && xapp_is('value', $mixed->options[$key])) : array_key_exists($key, $mixed->options));
			}else if(is_string($mixed) && xapp_can_options($mixed)){
				return (($strict) ? (array_key_exists($key, $mixed::$options) && xapp_is('value', $mixed::$options[$key])) : array_key_exists($key, $mixed::$options));
			}
		}
		return false;
	}
}

if(!function_exists('xapp_is_option'))
{
	/**
	 * xapp option shortcut function for xapp_has_option this will use xapp_has_option with third parameter strict = true
	 * so option value has to be a valid value other then null, false, empty array. see xapp_has_option
	 *
	 * @see xapp_has_option
	 * @param null|string $key expects the options key name to check
	 * @param null|object|string|array $mixed expects value according to explanation above
	 * @return bool
	 */
	function xapp_is_option($key = null, &$mixed = null)
	{
		if($key !== null)
		{
			if(is_array($mixed)){
				return xapp_is('value', $mixed[$key]);
			}else if(xapped()){
				return Xapp::hasOption($key, $mixed, true);
			}else if(is_object($mixed) && xapp_can_options($mixed)){
				return ((array_key_exists($key, $mixed->options) && xapp_is('value', $mixed->options[$key])) ? true : false);
			}else if(is_string($mixed) && xapp_can_options($mixed)){
				return ((array_key_exists($key, $mixed::$options) && xapp_is('value', $mixed::$options[$key])) ? true : false);
			}
		}
		return false;
	}
}

if(!function_exists('xapp_unset_option'))
{
	/**
	 * xapp option shortcut xapp_unset_option will unset first parameter option $key from second parameter which can be:
	 * 1) array = unset key from array
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 *
	 * @param null|string $key expects the options key name to unset
	 * @param null|object|string|array $mixed expects value according to explanation above
	 * @return void
	 */
	function xapp_unset_option($key = null, &$mixed = null)
	{
		if($key !== null)
		{
			if(is_array($mixed)){
				if(xapp_has_option($key, $mixed)){ unset($mixed[$key]); }
			}else if(xapped()){
				if(xapp_has_option($key, $mixed)){ Xapp::unsetOption($key, $mixed); }
			}else if(is_object($mixed) && xapp_can_options($mixed)){
				if(xapp_has_option($key, $mixed)){ unset($mixed->options[$key]); }
			}else if(is_string($mixed) && xapp_can_options($mixed)){
				if(xapp_has_option($key, $mixed)){ unset($mixed::$options[$key]); }
			}
		}
	}
}

if(!function_exists('xapp_reset_option'))
{
	/**
	 * xapp option shortcut xapp_reset_option reset an option value making sure that in case when xapp option handling is
	 * used arrays are fully resetet and not merged when setting options the xapp option functionality. the third parameter
	 * can be:
	 * 1) array = setting $key to $mixed
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 *
	 * @param null|string $key expects the option key name
	 * @param null|mixed $value expects the option key value
	 * @param null|object|string $mixed expects value according to explanation above
	 * @return void
	 */
	function xapp_reset_option($key = null, $value = null, &$mixed = null)
	{
		if(is_array($mixed)){
			if(xapp_has_option($key, $mixed)){ $mixed[$key] = $value; }
		}else if(xapped()){
			Xapp::resetOption($key, $value, $mixed);
		}else if(is_object($mixed) && xapp_can_options($mixed)){
			if(xapp_has_option($key, $mixed)){ $mixed->options[$key] = $value; }
		}else if(is_string($mixed) && xapp_can_options($mixed)){
			if(xapp_has_option($key, $mixed)){ $mixed::$options[$key] = $value; }
		}
	}
}

if(!function_exists('xapp_reset_options'))
{
	/**
	 * xapp option shortcut xapp_reset_options will reset first params options which can be:
	 * 1) array = reset array
	 * 2) object = object instance implementing xapp option handling or not
	 * 3) string = object class name as string to handle static classes/options
	 * 4) null = null when called inside class for xapp option handling for auto caller determination
	 *
	 * @param null|object|string|array $mixed expects value according to explanation above
	 * @return void
	 */
	function xapp_reset_options(&$mixed = null)
	{
		if(is_array($mixed)){
			$mixed = array();
		}else if(xapped()){
			Xapp::resetOptions($mixed);
		}else if(is_object($mixed) && xapp_can_options($mixed)){
			$mixed->options = array();
		}else if(is_string($mixed) && xapp_can_options($mixed)){
			$mixed::$options = array();
		}
	}
}

if(!function_exists('xapp_default'))
{
	/**
	 * xapp default function that is supposed to act where class methods use default return value arguments which can have
	 * different functions. the normal behaviour is return the default value once method logic fails. if you pass a callback
	 * or exception as default value returning these value does not make sense - instead exception is thrown and callback
	 * is called
	 *
	 * @param mixed $value expects the value to return or action to perform
	 * @return mixed
	 * @throws Exception
	 */
	function xapp_default($value)
	{
		if(is_callable($value) || (is_string($value) && function_exists($value)))
		{
			return call_user_func($value);
		}else if($value instanceof Exception) {
			throw $value;
		}else if($value === 'exit'){
			exit(0);
		}
		return $value;
	}
}