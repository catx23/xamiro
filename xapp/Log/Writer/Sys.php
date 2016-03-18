<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Log.Writer');
xapp_import('xapp.Log.Writer.Exception');

/**
 * Log Writer Sys class
 *
 * @package Log
 * @subpackage Log_Writer
 * @class Xapp_Log_Writer_Sys
 * @error 121
 * @author Frank Mueller
 */
class Xapp_Log_Writer_Sys extends Xapp_Log_Writer
{
    /**
     * contains syslog identifier
     *
     * @var null|string
     */
    protected $_ident = null;


    /**
     * class constructor opens syslog with passed log ident and options
     *
     * @error 12101
     * @param string $ident expects log identifier
     * @param int $facility expects optional bitmasked values
     * @throws Xapp_Log_Writer_Exception
     */
    public function __construct($ident = 'Xapp', $facility = LOG_USER)
    {
        $this->_ident = trim((string)$ident);
        if(!openlog($this->_ident, LOG_CONS, $facility))
        {
            throw new Xapp_Log_Writer_Exception(_("unable to open sys log with passed parameters"), 1210101);
        }
    }


    /**
     * write message to syslog stream return boolean value in case of success. the syslog
     * implementation requires a log priority parameter which can be passed as second parameter
     * or pass the priority value as array key.
     *
     * @error 12202
     * @param string|array|object $message expects the message object
     * @param null|mixed $params expects optional parameters
     * @return bool
     */
    public function write($message, $params = null)
    {
        if(is_int($params))
        {
            $priority = $params;
        }else if(is_array($params) && isset($params['priority'])){
            $priority = (int)$params['priority'];
        }else{
            $priority = LOG_INFO;
        }
        return syslog($priority, $this->format($message));
    }


    /**
     * class constructor closes logsys
     *
     * @error 12203
     */
    public function __destruct()
   	{
   		closelog();
   	}
}