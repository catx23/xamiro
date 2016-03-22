<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Log');
xapp_import('xapp.Log.Interface');
xapp_import('xapp.Log.Exception');
xapp_import('xapp.Log.Writer.File');
xapp_import('xapp.Log.Writer.Mail');

/**
 * Log Error class
 *
 * @package Log
 * @class Xapp_Log_Error
 * @error 116
 * @author Frank Mueller
 */
class Xapp_Log_Error extends Xapp_Log
{
    /**
     * ignore level = nothing will be logged
     *
     * @const IGNORE
     */
    const IGNORE                    = 0;

    /**
     * dump level constant for bitwise combination
     *
     * @const DUMP
     */
    const DUMP                      = 1;

    /**
     * log level constant for bitwise combination
     *
     * @const LOG
     */
    const LOG                       = 2;

    /**
     * mail level constant for bitwise combination
     *
     * @const MAIL
     */
    const MAIL                      = 4;

    /**
     * option stack trace defines whether to also log the stack trace
     * of error or not
     *
     * @const STACK_TRACE
     */
    const STACK_TRACE               = 'LOG_ERROR_STACK_TRACE';

    /**
     * option action map defines a map for mapping any error code to
     * the errors action constants, e.g. array(64 => 2) maps error 64
     * to logger action 2 = log message
     *
     * @const ACTION_MAP
     */
    const ACTION_MAP                = 'LOG_ERROR_ACTION_MAP';

    /**
     * option extended will log extended statistics like: ip, referer,..
     *
     * @const EXTENDED
     */
    const EXTENDED                  = 'LOG_ERROR_EXTENDED';

    /**
     * option email subject contains default email subject
     *
     * @const EMAIL_SUBJECT
     */
    const EMAIL_SUBJECT             = 'LOG_ERROR_MAIL_SUBJECT';


    /**
     * contains the static singleton instance of this class
     *
     * @var null|Xapp_Log_Error
     */
    protected static $_instance = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::STACK_TRACE           => XAPP_TYPE_BOOL,
        self::ACTION_MAP            => XAPP_TYPE_ARRAY,
        self::EXTENDED              => XAPP_TYPE_BOOL,
        self::EMAIL_SUBJECT         => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::STACK_TRACE           => 0,
        self::ACTION_MAP            => 0,
        self::EXTENDED              => 1,
        self::EMAIL_SUBJECT         => 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::EXTENSION             => 'log',
        self::STACK_TRACE           => false,
        self::FIELD_DELIMITER       => ", ",
        self::LINE_DELIMITER        => "",
        self::ENTRY_DELIMITER       => PHP_EOL,
        self::VALUE_ENCLOSURE       => "",
        self::EXTENDED              => true,
        self::TIME_FORMAT           => "%Y-%m-%d %H:%M:%S",
        self::FILE_FORMAT           => "%Y%m%d-error",
        self::EMAIL_SUBJECT         => 'Xapp error alert',
        self::ROTATION              => false
    );

    /**
     * php to xapp error mapping. all php errors are mapped to xapp error levels
     * so passing an error with php error code will be mapped to xapp error
     *
     * @var array
     */
    protected static $_errorMap = array
    (
        E_NOTICE                    => XAPP_ERROR_NOTICE,
        E_USER_NOTICE               => XAPP_ERROR_NOTICE,
        E_WARNING                   => XAPP_ERROR_WARNING,
        E_CORE_WARNING              => XAPP_ERROR_WARNING,
        E_USER_WARNING              => XAPP_ERROR_WARNING,
        E_ERROR                     => XAPP_ERROR_ALERT,
        E_USER_ERROR                => XAPP_ERROR_ERROR,
        E_CORE_ERROR                => XAPP_ERROR_ALERT,
        E_RECOVERABLE_ERROR         => XAPP_ERROR_ERROR,
        E_STRICT                    => XAPP_ERROR_DEBUG
    );


    /**
     * class constructor receives instance options and set error action
     * map if not already set.
     *
     * @error 11601
     * @param null|mixed $options expects options object
     */
    public function __construct($options = null)
    {
        if(!isset($options[self::ACTION_MAP]))
        {
            $options[self::ACTION_MAP] = array
            (
                XAPP_ERROR_IGNORE   => null,
                XAPP_ERROR_NOTICE   => self::LOG,
                XAPP_ERROR_WARNING  => self::LOG,
                XAPP_ERROR_ERROR    => self::LOG,
                XAPP_ERROR_ALERT    => self::LOG | self::MAIL,
                XAPP_ERROR_DEBUG    => self::DUMP
            );
        }
        xapp_init_options($options, $this);
        $this->init();
    }


    /**
     * init instance by completing all passed options. e.g. if a path is not a absolute
     * log file pointer the name will be automatically created by the class. the init
     * function will check if any log writer instances have been passed in instance options
     * and register those to be executed at a later stage. if not default log writers are
     * registered
     *
     * @error 11602
     * @return void
     */
    protected function init()
    {
        $this->stack = array();
        $path = xapp_get_option(self::PATH, $this);
        if(!preg_match('/\.([a-z0-9]{2,4})$/i', $path))
        {
            if(!xapp_is_option(self::NAME, $this))
            {
                xapp_set_option(self::NAME, strftime(xapp_get_option(self::FILE_FORMAT, $this), time()), $this);
            }
            if(strpos(xapp_get_option(self::NAME, $this), '.') === false)
            {
                xapp_set_option(self::NAME, trim(xapp_get_option(self::NAME, $this), '.') . '.' . trim(xapp_get_option(self::EXTENSION, $this), '.'), $this);
            }
            if(!empty($path))
            {
                $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . xapp_get_option(self::NAME, $this);
            }else{
                $path = debug_backtrace();
                $path = array_pop($path);
                $path = rtrim(dirname($path['file']), DS) . DS;
                $path = $path . xapp_get_option(self::NAME, $this);
            }
            xapp_set_option(self::PATH, $path, $this);
        }
        if(xapp_is_option(self::WRITER, $this))
        {
            foreach(xapp_get_option(self::WRITER, $this) as $writer)
            {
                if(is_array($writer))
                {
                    $this->register(array_shift($writer), array_shift($writer));
                }else{
                    $this->register($writer);
                }
            }
        }else{
            $this->register(
                new Xapp_Log_Writer_File(
                    xapp_get_option(self::PATH, $this),
                    xapp_get_option(self::ROTATION, $this)
                )
            , 'file');
            if(xapp_is_option(self::EMAIL, $this))
            {
                $this->register(
                    new Xapp_Log_Writer_Mail(
                        xapp_get_option(self::EMAIL, $this),
                        xapp_get_option(self::EMAIL_SUBJECT, $this)
                    )
                , 'mail');
            }
        }
    }


    /**
     * method to create singleton instance for this class
     *
     * @error 11603
     * @param null|mixed $options expects options object
     * @return null|Xapp_Log_Error
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
     * function to compile passed error message/object that has been received via the log
     * function. the function will return an array of compiled error messages ready to be logged
     * the default way. the function but will also return for every entry an object containing the
     * following data:
     * 1)   severity = contains the severity level
     * 2)   code = contains the error code
     * 3)   message = contains the already formatted log messages
     * 4)   object = contains an object of all log details as key => val pair
     *
     * @error 11604
     * @param null|string|Exception $e expects either a log message as string or exception object
     * @param null|int $c expects optional error code when passing log messages as string
     * @return null|object returns the compiled error object
     */
    protected function compile($e = null, $c = null)
    {
        if($e !== null)
        {
            $message = "";
            $object = new stdClass();
            $led = xapp_get_option(self::ENTRY_DELIMITER, $this);
            $lld = xapp_get_option(self::LINE_DELIMITER, $this);
            $lfd = xapp_get_option(self::FIELD_DELIMITER, $this);
            $lve = xapp_get_option(self::VALUE_ENCLOSURE, $this);
            $hash = parent::hashify($e);

            if($e instanceof Exception)
            {
                $severity = ((xapp_property_exists($e, 'severity')) ? (int)$e->getSeverity() : self::LOG);
                $code = (int)$e->getCode();
                $message .= $led;
                $message .= $lve . ($object->timestamp = strftime(xapp_get_option(self::TIME_FORMAT, $this), time())) . $lve . $lfd;
                $message .= $lve . ($object->message = $e->getMessage()) . $lve . $lfd;
                $message .= $lve . ($object->file = $e->getFile()) . $lve . $lfd;
                $message .= $lve . ($object->line = $e->getLine()) . $lve . $lfd;
                $message .= $lve . ($object->code = $e->getCode()) . $lve . $lfd;
            }else{
                $severity = (($c !== null && array_key_exists((int)$c, self::$_errorMap)) ? self::$_errorMap[(int)$c] : XAPP_ERROR_ERROR);
                $code = (($c !== null) ? (int)$c : 0);
                $message .= $led;
                $message .= $lve . ($object->timestamp = strftime(xapp_get_option(self::TIME_FORMAT, $this), time())) . $lve . $lfd;
                $message .= $lve . ($object->message = trim($e)) . $lve . $lfd;
            }
            if((bool)xapp_get_option(self::EXTENDED, $this))
            {
                $message .= $lve . ($object->ip = (string)parent::getIp()) . $lve . $lfd;
                $message .= $lve . ($object->script_name = parent::getEnv('SCRIPT_NAME')) . $lve . $lfd;
                $message .= $lve . ($object->request_uri = parent::getEnv('REQUEST_URI')) . $lve . $lfd;
                $message .= $lve . ($object->http_user_agent = parent::getEnv('HTTP_USER_AGENT')) . $lve.  $lfd;
                $message .= $lve . ($object->http_referer = parent::getEnv('HTTP_REFERER')) . $lve . $lfd;
            }
            if((bool)xapp_get_option(self::STACK_TRACE, $this))
            {
                if($e instanceof Exception)
                {
                    $trace = $e->getTraceAsString();
                }else{
                    ob_start();
                    debug_print_backtrace();
                    $trace = ob_get_contents();
                    ob_end_clean();
                }
                $message .= $lve . ($object->trace = str_replace($lfd, " ", (string)$trace)) . $lve . $lfd;
            }
            if(!(bool)$this->pause)
            {
                return $this->stack[$hash] = (object)array('severity' => $severity, 'code' => $code, "message" =>  $message, "object" => $object);
            }else{
                return (object)array('severity' => $severity, 'code' => $code, "message" =>  $message, "object" => $object);
            }
        }
        return null;
    }


    /**
     * log function receives the log message/object to be processed by class by passing
     * the value to the compile method which in turn stores the error object in class
     * stack to be processed in destructor. NOTE: this error log implementation can receive
     * an array of exceptions instances or a single exception as first parameter or a error
     * message as firs parameter. the second parameter has only affect if the first parameter
     * is a single error message string
     *
     * @error 11605
     * @param null|string|array|Exception $message expects an object as explained above
     * @param null|int $code optional error code
     * @return Xapp_Log_Error
     */
    public function log($message = null, $code = null)
    {
        if(!is_array($message))
        {
            $this->compile($message, $code);
        }else{
            foreach((array)$message as $m)
            {
                $this->compile($m);
            }
        }
        return $this;
    }


    /**
     * class destructor iterates through error stack an sorts all errors according to intended action
     * or target. since error have different severity level some errors need to be routed to specific
     * log writers only. therefore: the default implementation will route all errors to the designated
     * log writer. if the default log writers are overwritten than all errors are written to all registered
     * log writers at the same time - regardless of error log severity and mapped error action, e.g. only
     * mailing alert errors via email.
     *
     * @error 11605
     * @return void
     */
    public function __destruct()
    {
        $all = array();
        $mail = array();
        $file = array();
        $map = xapp_get_option(self::ACTION_MAP, $this);

        foreach($this->stack as $k => $v)
        {
            if(!array_key_exists((int)$v->severity, $map))
            {
                $v->severity = self::LOG;
            }
            if(array_key_exists((int)$v->severity, $map))
            {
                $v->action = $map[(int)$v->severity];
            }
            if(isset($v->action) && !is_null($v->action))
            {
                if(in_array($v->action, array(self::DUMP)))
                {
                    xapp_console($v->object, null, 'error');
                }
                if(in_array($v->action, array(self::LOG, self::LOG | self::MAIL)))
                {
                    $file[] = $v->message;
                }
                if(in_array($v->action, array(self::MAIL, self::LOG | self::MAIL)))
                {
                    $mail[] = $v->object;
                }
                $all[] = $v->object;
            }
        }
        if(xapp_is_option(self::WRITER, $this))
        {
            $this->write($all);
        }else{
            if(sizeof($file) > 0)
            {
                $this->write($file, 'file');
            }
            if(sizeof($mail) > 0)
            {
                if(xapp_is_option(self::EMAIL, $this))
                {
                    $this->write($mail, 'mail');
                }
            }
        }
    }
}