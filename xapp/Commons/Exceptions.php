<?php

/**
 * @author     Guenter Baumgart
 * @author     David Grudl
 * @copyright 2004 David Grudl (http://davidgrudl.com)
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @license : http://opensource.org/licenses/BSD-3-Clause
 * @package XApp-Commander
 *
 * @original header : This file is part of the Nette Framework (http://nette.org)
 */

class XApp_Commons_Exception{}


/**
 * The exception that is thrown when the value of an argument is
 * outside the allowable range of values as defined by the invoked method.
 */
class XApp_ArgumentOutOfRangeException extends InvalidArgumentException
{
}


/**
 * The exception that is thrown when a method call is invalid for the object's
 * current state, method has been invoked at an illegal or inappropriate time.
 */
class XApp_InvalidStateException extends RuntimeException
{
}


/**
 * The exception that is thrown when a requested method or operation is not implemented.
 */
class XApp_NotImplementedException extends LogicException
{
}


/**
 * The exception that is thrown when an invoked method is not supported. For scenarios where
 * it is sometimes possible to perform the requested operation, see InvalidStateException.
 */
class XApp_NotSupportedException extends LogicException
{
}


/**
 * The exception that is thrown when a requested method or operation is deprecated.
 */
class XApp_DeprecatedException extends XApp_NotSupportedException
{
}


/**
 * The exception that is thrown when accessing a class XApp_ member (property or method) fails.
 */
class XApp_MemberAccessException extends LogicException
{
}


/**
 * The exception that is thrown when an I/O error occurs.
 */
class XApp_IOException extends RuntimeException
{
}


/**
 * The exception that is thrown when accessing a file that does not exist on disk.
 */
class XApp_FileNotFoundException extends XApp_IOException
{
}


/**
 * The exception that is thrown when part of a file or directory cannot be found.
 */
class XApp_DirectoryNotFoundException extends XApp_IOException
{
}


/**
 * The exception that is thrown when an argument does not match with the expected value.
 */
class XApp_InvalidArgumentException extends ErrorException
{
}


/**
 * The exception that is thrown when an illegal index was requested.
 */
class XApp_OutOfRangeException extends ErrorException
{
}


/**
 * The exception that is thrown when a value (typically returned by function) does not match with the expected value.
 */
class XApp_UnexpectedValueException extends ErrorException
{
}


/**
 * The exception that is thrown when static class XApp_ is instantiated.
 */
class XApp_StaticClassException extends LogicException
{
}


/**
 * The exception that indicates errors that can not be recovered from. Execution of
 * the script should be halted.
 */
class XApp_FatalErrorException extends ErrorException
{

    public function __construct($message, $code, $severity, $file, $line, $context, Exception $previous = NULL)
    {
        parent::__construct($message, $code, $severity, $file, $line, $previous);
        $this->context = $context;
        trigger_error(__CLASS__ . ' is deprecated, use ErrorException.', E_USER_DEPRECATED);
    }

}

class XApp_File_Exception extends ErrorException
{
    /**
     * error exception class constructor directs instance
     * to error xapp error handling
     *
     * @param string $message excepts error message
     * @param int $code expects error code
     * @param int $severity expects severity flag
     */
    public function __construct($message, $code = 0, $severity = XAPP_ERROR_ERROR)
    {
        parent::__construct($message, $code, $severity);
        xapp_error($this);
    }
}

/**
 * The exception that indicates error of the last Regexp execution.
 */
class XApp_RegexpException extends Exception
{
	static public $messages = array(
		PREG_INTERNAL_ERROR => 'Internal error',
		PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit was exhausted',
		PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted',
		PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 data',
		5 => 'Offset didn\'t correspond to the begin of a valid UTF-8 code point', // PREG_BAD_UTF8_OFFSET_ERROR
	);

	public function __construct($message, $code = NULL, $pattern = NULL)
	{
		if (!$message) {
			$message = (isset(self::$messages[$code]) ? self::$messages[$code] : 'Unknown error') . ($pattern ? " (pattern: $pattern)" : '');
		}
		parent::__construct($message, $code);
	}

}
