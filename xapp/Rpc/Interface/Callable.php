<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Rpc callable interface
 *
 * @package Rpc
 * @author Frank Mueller <set@cooki.me>
 */
interface Xapp_Rpc_Interface_Callable
{
    /**
     * method that will be called before the actual requested method is called. the callback can return anything but unless
     * the returned value is a boolean "false" the result is not used. in case of returning a boolean false the actual
     * rpc request method is not called instead the "onAbort" function is called.
     *
     * @param Xapp_Rpc_Server $server
     * @param array $params
     * @return null|mixed
     */
    public function onBeforeCall(Xapp_Rpc_Server $server, Array $params);


    /**
     * method that will be called after the requested method has been invoked. if the method returns anything that is not
     * "NULL" the result from the invoked method or "onBeforeCall" is overwritten!
     *
     * @param Xapp_Rpc_Server $server
     * @param array $params
     * @return null|mixed
     */
    public function onAfterCall(Xapp_Rpc_Server $server, Array $params);


    /**
     * method that will be called if onBeforeCall returns boolean false. the returned value will be send to rpc response
     * instead of the value returned by the to be invoked method from rpc request
     *
     * @param Xapp_Rpc_Server $server
     * @param array $params
     * @return null|mixed
     */
    public function onAbort(Xapp_Rpc_Server $server, Array $params);


    /**
     * method that will be called when invoking called service and service throws error while execute. catch this error/
     * exception for logging or otherwise manipulating it and pass it back into the error handling process.
     *
     * @param Xapp_Rpc_Server $server
     * @param Exception $e
     * @return void|null|Exception
     */
    public function onError(Xapp_Rpc_Server $server, Exception $e);
}