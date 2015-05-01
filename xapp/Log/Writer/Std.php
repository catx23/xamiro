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
 * Log Writer Std class
 *
 * @package Log
 * @subpackage Log_Writer
 * @class Xapp_Log_Writer_Mail
 * @error 120
 * @author Frank Mueller
 */
class Xapp_Log_Writer_Std extends Xapp_Log_Writer
{
    /**
     * contains php std resource
     *
     * @var mixed|null
     */
    protected $_std = null;


    /**
     * class constructor sets std flag to init valid str resource. std
     * flag can be err, out
     *
     * @error 12001
     * @param string $type expects the std flag
     * @throws Xapp_Log_Writer_Exception
     */
    public function __construct($type = 'err')
    {
        $type = 'STD' . strtoupper(trim((string)$type));
        if(defined($type))
        {
            $this->_std = constant($type);
        }else{
            throw new Xapp_Log_Writer_Exception(xapp_sprintf(_("std type: %s is not defined"), $type), 1200101);
        }
    }


    /**
     * write message to std stream return boolean value in case of success
     *
     * @error 12002
     * @param string|array|object $message expects the message object
     * @param null|mixed $params expects optional parameters
     * @return bool
     */
    public function write($message, $params = null)
    {
        return fwrite($this->_std, trim($this->format($message), PHP_EOL) .  PHP_EOL);
    }
}