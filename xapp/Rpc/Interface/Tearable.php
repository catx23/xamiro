<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Rpc tearable interface
 *
 * @package Rpc
 * @author Frank Mueller <set@cooki.me>
 */
interface Xapp_Rpc_Interface_Tearable
{
    /**
     * method that will be called when tearing down rpc server and just before flushing response to output stream. useful
     * for modifying response body or header
     *
     * @param Xapp_Rpc_Server $server
     * @return void
     */
    public function onTeardown(\Xapp_Rpc_Server $server);
}