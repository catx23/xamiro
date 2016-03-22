<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Server.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Server.Json');
xapp_import('xapp.Rpc.Smd.Jsonp');
xapp_import('xapp.Rpc.Server.Jsonp');
xapp_import('xapp.Rpc.Response.Json');
xapp_import('xapp.Rpc.Request.Json');


/**
 * Rpc server jsonp class
 *
 * @package Rpc
 * @subpackage Rpc_Server
 * @class Xapp_Rpc_Server_Raw
 * @error 147
 * @author Guenter Baumgart
 */

class Xapp_Rpc_Server_Raw extends Xapp_Rpc_Server_Jsonp
{
    /**
     * executing requested service if found passing result from service invoking to response
     * or pass compile smd map to response if no service was called. if a callback was supplied
     * will wrap result into callback function
     *
     * @error 14706
     * @return void
     */
    protected function execute($call)
    {
	    $get = $this->request()->getGet();
	    $response = $this->response();
	    try {
		    $result = $this->invoke($call, $call[3]);
		    $this->response()->skipHeader();
		    if (array_key_exists(xapp_get_option(self::CALLBACK, $this), $get)) {
		    } else {
			    $result = $response->encode($result);
		    }
		    $response->body($result);
	    } catch (Exception $e) {
		    if (xapp_is_option(self::EXCEPTION_CALLBACK, $this)) {
			    $e = call_user_func_array(xapp_get_option(self::EXCEPTION_CALLBACK, $this), array(&$e, $this, $call));
			    if (!($e instanceof Exception)) {
				    if (xapp_get_option(self::COMPLY_TO_JSONRPC_1_2, $this)) {
					    if (array_key_exists($e->getCode(), $this->codeMap)) {
						    xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, $this->codeMap[$e->getCode()], $response);
					    } else {
						    xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, 500, $response);
					    }
				    }
				    if (xapp_is_option(self::ERROR_CALLBACK, $this) && array_key_exists(
						    xapp_get_option(self::ERROR_CALLBACK, $this),
						    $get
					    )
				    ) {
					    $e = $get[xapp_get_option(self::ERROR_CALLBACK, $this)] . '(' . $response->encode($e) . ')';
				    } else {
					    if (array_key_exists(xapp_get_option(self::CALLBACK, $this), $get)) {
						    $e = $get[xapp_get_option(self::CALLBACK, $this)] . '(' . $response->encode($e) . ')';
					    } else {
						    $e = $response->encode($e);
					    }
				    }
				    $response->body($e);
			    }
		    }
	    }
    }
}