<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Log interface
 *
 * @package Log
 * @author Frank Mueller
 */
interface Xapp_Log_Interface
{
    /**
     * log function receives the log message/object to be processed
     *
     * @param null|string|array|Exception $message expects mixed object
     */
    function log($message);
}