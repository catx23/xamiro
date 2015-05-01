<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Mail config exception class
 *
 * @package Conf
 * @class Xapp_Config_Exception
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Config_Exception extends ErrorException
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