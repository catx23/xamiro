<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Request.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Request');

/**
 * Rpc request json class
 *
 * @package Rpc
 * @subpackage Rpc_Request
 * @class Xapp_Rpc_Request_Json
 * @error 150
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Request_Json extends Xapp_Rpc_Request
{
    /**
     * class constructor calls parent constructor to initialize class.
     *
     * @error 15001
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * decodes expected json raw data throwing error if phps native json_decode function is not supported by system.
     * returns data in first parameter if is not a string and supposed post array. throws fault if json string could not
     * be decoded
     *
     * @error 15002
     * @param mixed $data expects the raw json string to decode or post data
     * @return mixed
     * @throws Xapp_Rpc_Request_Exception
     */
    public function decode($data)
    {

        if(!is_string($data))
        {
            return $data;
        }
	    if(function_exists('json_decode'))
        {
            $data = json_decode($this->prepare($data), true);
            if($data !== null)
            {
                return (array)$data;
            }else{
	            return '{}';
                if(version_compare(PHP_VERSION, '5.3.0', '>='))
                {
                    Xapp_Rpc_Fault::t('unable to decode json string', array(1500202, -32700), XAPP_ERROR_IGNORE, array('jsonError' => $this->error(json_last_error())));
                }else{
                    Xapp_Rpc_Fault::t('unable to decode json string', array(1500202, -32700));
                }
            }
        }else{
            throw new Xapp_Rpc_Request_Exception(_("php function json_decode is not supported by system"), 1500201);
        }
    }


    /**
     * gets the rpc version from request request object/call by looking for jsonrpc or version parameter in request.
     * if second argument is set to boolean true will return array with version string and version number
     *
     * @error 15003
     * @param array $call expects request object with all params
     * @param bool $full expects [optional] boolean value to whether return array with full version info
     * @return null|string
     */
    public function getVersion(Array $call, $full = false)
    {
        if(array_key_exists('jsonrpc', $call))
        {
            return ((bool)$full) ? array('jsonrpc', $call['jsonrpc']) : $call['jsonrpc'];
        }
        if(array_key_exists('version', $call))
        {
            return ((bool)$full) ? array('version', $call['version']) : $call['version'];
        }
        return null;
    }


    /**
     * prepares raw json string before decoding
     *
     * @error 15004
     * @param string $json expects the raw json string
     * @return string
     */
    protected function prepare($json)
    {
        $json = trim($json);
        $json = mb_convert_encoding($json, 'UTF-8', 'ASCII,UTF-8,ISO-8859-1');
        if(substr($json, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF))
        {
            $json = substr($json, 3);
        }
        return $json;
    }


    /**
     * returns json decode error message for json error code in first parameter.
     * if first parameter is not set tries to get last error automatically
     *
     * @error 15005
     * @param null|int $error expects optional json error code
     * @return string
     */
    protected function error($error = null)
    {
        if($error !== null)
        {
            $error = (int)$error;
        }else{
            $error = (int)json_last_error();
        }
        switch($error)
        {
            case JSON_ERROR_NONE:
                return 'no errors';
            case JSON_ERROR_DEPTH:
                return 'maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'syntax error, malformed json';
            case JSON_ERROR_UTF8:
                return 'malformed utf-8 characters, possibly incorrectly encoded';
            default:
                return 'unknown error';
        }
    }
}