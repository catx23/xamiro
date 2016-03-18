<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../Core/core.php');

/**
 * Rpc gateway exception class
 *
 * @package Rpc
 * @class Xapp_Rpc_Gateway_Exception
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Gateway_Exception extends Xapp_Rpc_Exception
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