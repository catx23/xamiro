<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../Core/core.php');

xapp_import('xapp.Log.Exception');

/**
 * Log writer exception class
 *
 * @package Log
 * @subpackage Log_Writer
 * @class Xapp_Log_Writer_Exception
 * @author Frank Mueller
 */
class Xapp_Log_Writer_Exception extends Xapp_Log_Exception
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