<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Error class
 *
 * @package Xapp
 * @class Xapp_Error
 * @error 105
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Error extends ErrorException
{
    /**
     * overwrites message var of exception class so its is public
     *
     * @var string
     */
    public $message = "";

    /**
     * storage array for all errors piped through xapp error handling
     *
     * @var array
     */
    public static $stack = array();


    /**
     * constructor method which always needs to be called to stack errors for error
     * output dumping/logging and call the parent constructor to set all error arguments
     * because only parent constructor of phps native Exception class can set instance
     * properties. the class constructor expects the same arguments as its parent or instead
     * of passing a message string in first parameter can be called with an instance of
     * exception. in this case the arguments of passed instances are compared with the
     * constructor arguments to determine which will be passed to parent constructor.
     * the instance will be passed
     *
     * @error 10501
     * @param string|object $mixed expects any of the above described values (string or instance of Exception)
     * @param int $code expects optional error code
     * @param int $severity expects optional severity level
     * @param null|string $file expects the file name where exception was created
     * @param null|int $line expects the line where exception was created
     */
    public function __construct($mixed = "", $code = 0, $severity = XAPP_ERROR_ERROR, $file = null, $line = null)
    {
        if(is_object($mixed))
        {
            if($mixed instanceof ErrorException)
            {
                $severity = ((int)$severity > (int)$mixed->getSeverity()) ? (int)$severity : (int)$mixed->getSeverity();
            }
            parent::__construct
            (
                $mixed->getMessage(),
                (((int)$code > (int)$mixed->getCode()) ? (int)$code : (int)$mixed->getCode()),
                (int)$severity,
                (string)$mixed->getFile(),
                (int)$mixed->getLine()
            );
        }else{
            if($file !== null && $line !== null)
            {
                parent::__construct
                (
                    (string)$mixed,
                    (int)$code,
                    (int)$severity,
                    (string)$file,
                    (int)$line
                );
            }else{
                parent::__construct
                (
                    (string)$mixed,
                    (int)$code,
                    (int)$severity
                );
            }
        }
        if($severity !== -1)
        {
            self::stack($this);
        }
    }


    /**
     * set error message after error object construction
     *
     * @error 10502
     * @param string $message expects error message to set
     * @return Xapp_Error
     */
    public function setMessage($message)
    {
        $this->message = (string)$message;
        return $this;
    }


    /**
     * set error message to emtpy value
     *
     * @error 10503
     * @return void
     */
    public function unsetMessage()
    {
        $this->message = '';
    }


    /**
     * check if a message is set or not
     *
     * @error 10504
     * @return bool
     */
    public function hasMessage()
    {
        return (!empty($this->message)) ? true : false;
    }


    /**
     * shortcut function to create new instance of Xapp_Error as static alternative
     * to creating a class with new operator. the return instance can also be thrown
     *
     * @see Xapp_Error::__construct
     * @static
     * @error 10505
     * @param string|Exception $mixed expects any of the in the class constructor described values
     * @param int $code expects optional error code
     * @param int $severity expects optional severity level
     * @param null|string $file expects file name where error occurred
     * @param null|int $line expects line number where error occurred
     * @return Xapp_Error
     */
    public static function e($mixed, $code = 0, $severity = XAPP_ERROR_ERROR, $file = null, $line = null)
    {
        return new self($mixed, $code, $severity, $file, $line);
    }


    /**
     * throw an exception and if stack = true stack error for error handling
     *
     * @static
     * @error 10506
     * @param Exception $e expects instance or sub class of phps native Exception
     * @param bool $stack expects boolean value for stack error
     * @return void
     * @throws Exception
     */
    public static function t(Exception $e, $stack = false)
    {
        if((bool)$stack)
        {
            xapp_error($e);
        }
        throw $e;
    }


    /**
     * xapps php error handling function called be phps native set_error_handler function passes
     * all parameters to class constructor to be send constructed as Xapp_Error instance to be send
     * to error logging or console.
     *
     * @static
     * @error 10507
     * @param int $code expects error code
     * @param string $message expects the error message
     * @param null|string $file expects file name where error occured
     * @param null|int $line expects line number where error occured
     * @return bool false
     * @throws Xapp_Error
     */
    public static function errorHandler($code = 0, $message = "", $file = null, $line = null)
    {
        if((int)xapp_conf(XAPP_CONF_DEV_MODE) === 2 && stripos($message, 'missing argument') !== false)
        {
            throw new self($message, E_USER_ERROR, XAPP_ERROR_WARNING);
        }else if((int)$code == E_RECOVERABLE_ERROR){
            throw new self($message, E_USER_ERROR, XAPP_ERROR_ERROR);
        }else{
            xapp_error(new self($message, $code, XAPP_ERROR_ERROR, $file, $line));
        }
        return false;
    }


    /**
     * stack error in storag array taking care of unique error objects - preventing stacking
     * errors with same error message from same target.
     *
     * @static
     * @error 10508
     * @param Exception $e expects the error object
     * @return void
     */
    protected static function stack(Exception $e = null)
    {
        $k = "";

        if($e !== null)
        {
            $message = $e->getMessage();
            if(!empty($message))
            {
                $k .= preg_replace('/(\s|\W)+/iu', '', strtolower(trim($message)));
                if($e instanceof self)
                {
                    $k .= '::' . $e->getCode();
                }else{
                    $k .= '::' . $e->getFile();
                }
                $k = md5($k);
                if(!array_key_exists($k, self::$stack))
                {
                    self::$stack[$k] = $e;
                }
            }
        }
    }


    /**
     * returns error stack array
     *
     * @static
     * @error  10509
     * @return array
     */
    public static function getStack()
    {
        return self::$stack;
    }


    /**
     * checks whether errors have been stacked or not
     *
     * @static
     * @error 10510
     * @return bool
     */
    public static function hasStack()
    {
        return (sizeof(self::$stack) > 0) ? true : false;
    }


    /**
     * resets error stack to empty array
     *
     * @static
     * @error 10511
     * @return void
     */
    public static function sweepStack()
    {
        self::$stack = array();
    }


    /**
     * xapp default exception handler if xapp base class is used. bounces back error to generic
     * xapp_error function to be redirected to Xapp_Error for stacking, clear all handlers and by firing
     * trigger error without registered error/exception handlers force php to display error
     *
     * @static
     * @error 10512
     * @param Exception $e
     * @return bool false
     */
    public static function exceptionHandler(Exception $e = null)
    {
        if($e !== null)
        {
            if((int)ini_get('display_errors') === 0)
            {
                xapp_error($e, -1, XAPP_ERROR_ALERT);
            }
            restore_exception_handler();
            set_exception_handler(null);
            restore_error_handler();

            //trigger error will stop php immediately so all class destructors will not be called anymore
            //hence no error logging of not called exceptions therefore - log errors if display errors is
            //turned off if not throw fatal error
            if(ini_get('display_errors') === 0)
            {
                $type = E_USER_NOTICE;
            }else{
                $type = E_USER_ERROR;
            }
            if($e->getCode() !== 0)
            {
                trigger_error($e->getCode() . ' : ' . $e->getMessage() . ', in: ' . $e->getFile() . ':' . $e->getLine() . '...', $type);
            }else{
                trigger_error($e->getMessage() . ', in: ' . $e->getFile() . ':' . $e->getLine() . '...', $type);
            }
        }
        return false;
    }
}