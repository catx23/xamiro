<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Log Writer class
 *
 * @package Log
 * @subpackage Log_Writer
 * @class Xapp_Log_Writer
 * @error 117
 * @author Frank Mueller
 */
abstract class Xapp_Log_Writer
{
    /**
     * writes messages to log writer. see concrete implementation of sub classes
     *
     * @error 11701
     * @param string $message expects the log message as string
     * @param null|mixed $params expects optional parameters
     * @return mixed
     */
    abstract public function write($message, $params = null);


    /**
     * formats a log message which can be of the following:
     * 1)   string
     * 2)   array of single string values
     * 3)   array of array key => value pairs (label => value)
     * 4)   array of object with properties & values
     * the format function will determine the message type and contstruct a string
     * message from the passed message formatting the string with the help of the
     * following parameters:
     * 1)   lld = log line delimiter (separate each line with this value)
     * 2)   led = log entry delimiter (separate each entry with this value)
     * 3)   lvd = log value delimiter (separate each value with this value)
     * 4)   lod = log object delimiter (separate each key => value pair with this value)
     *
     * @error 11702
     * @param string|array|object $message expects the message object as explained above
     * @param string $lld expects the log line delimiter value
     * @param string $led expects the log entry delimiter value
     * @param string $lvd expects the log value delimiter value
     * @param string $lod expects the log object delimiter value
     * @return string
     */
    public function format($message, $lld = PHP_EOL, $led = PHP_EOL, $lvd = "", $lod = ": ")
    {
        $out = array();

        $lld = (string)$lld;
        $led = (string)$led;
        $lvd = (string)$lvd;
        $lod = (string)$lod;

        if(is_string($message))
        {
            return str_replace("\n.", "\n..", trim($message)) . $led;
        }else{
            foreach((array)$message as $key => $val)
            {
                if(is_string($val))
                {
                    $out[] = $lvd . trim($val) . $lvd . $lld;
                }else{
                    $tmp = array();
                    foreach((array)$val as $k => $v)
                    {
                        $tmp[] = $lvd . ((!is_int($k)) ? $k.$lod : "") . str_replace("\n.", "\n..", trim($v)) . $lvd;
                    }
                    $out[] = trim(implode($lld, $tmp), $lld) . $led;
                }
            }
            return trim(implode("", $out), $lld . $led) . $led;
        }
    }


    /**
     * returns unique object hash
     *
     * @error 11703
     * @return string
     */
    final public function __toString()
   	{
   		return spl_object_hash($this);
   	}
}