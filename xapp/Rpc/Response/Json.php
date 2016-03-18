<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Response.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Response');

/**
 * Rpc response json class
 *
 * @package Rpc
 * @subpackage Rpc_Response
 * @class Xapp_Rpc_Response_Json
 * @error 151
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Response_Json extends Xapp_Rpc_Response
{
    /**
     * option defines if response should be dojo compatible or not
     *
     * @const DOJO_COMPATIBLE
     */
    const DOJO_COMPATIBLE       = 'RPC_RESPONSE_JSON_DOJO_COMPATIBLE';

    /**
     * option defines optional json encode flags
     *
     * @const ENCODE_FLAGS
     */
    const ENCODE_FLAGS          = 'RPC_RESPONSE_JSON_ENCODE_FLAGS';


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::DOJO_COMPATIBLE   => XAPP_TYPE_BOOL,
        self::ENCODE_FLAGS      => XAPP_TYPE_INT
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::DOJO_COMPATIBLE   => 1,
        self::ENCODE_FLAGS      => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::CHARSET           => 'utf-8',
        self::CONTENT_TYPE      => 'application/json',
        self::PROTOCOL          => 'HTTP/1.1',
        self::STATUS_CODE       => 200,
        self::DOJO_COMPATIBLE   => false
    );


    /**
     * class constructor set class instance options and calls parent constructor
     *
     * @error 15101
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        xapp_init_options($options, $this);
        parent::__construct();
    }


    /**
     * encodes any data passed in first parameter if not already encoded to json string throwing exception if json_encode
     * is not supported by system
     *
     * @error 15102
     * @param mixed $data expects the data to encode
     * @return string
     * @throws Xapp_Rpc_Response_Exception
     */
    public function encode($data)
    {
        if(function_exists('json_encode'))
        {
            if($this->encoded($data))
            {
                return $data;
            }else{
                $data = json_encode($data, (int)xapp_get_option(self::ENCODE_FLAGS, $this));
                if($data !== false)
                {
                    return $data;
                }else{
                    if(version_compare(PHP_VERSION, '5.3.0', '>='))
                    {
                        Xapp_Rpc_Fault::t('unable to encode object to json', array(1510202, -32700), XAPP_ERROR_IGNORE, array('jsonError' => $this->error(json_last_error())));
                    }else{
                        Xapp_Rpc_Fault::t('unable to encode object to json', array(1510202, -32700));
                    }
                }
            }
        }else{
            throw new Xapp_Rpc_Response_Exception(_("php function json_decode is not supported by system"), 1510201);
        }
    }


    /**
     * sets version to response data according to rpc version or if dojo compatible always under
     * parameter "version". will always cast to string for compare version function
     *
     * @error 15103
     * @param int|string $version expects the version to compare
     * @return void
     */
    public function setVersion($version)
    {
        if(xapp_get_option(self::DOJO_COMPATIBLE, $this))
        {
            $this->set('version', $version);
        }else{
            if(version_compare((string)$version, '2.0', '>='))
            {
                $this->set('jsonrpc', $version);
            }else{
                $this->set('version', $version);
            }
        }
    }


    /**
     * returns json encode error message for json error code in first parameter.
     * if first parameter is not set tries to get last error automatically
     *
     * @error 15104
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


    /**
     * checks whether json data is already encoded or not
     *
     * @error 15105
     * @param string $data expects the encoded json data
     * @return bool
     */
    protected function encoded($data)
    {
        if(is_string($data))
        {
            @json_decode($data);
            return (bool)(json_last_error() === JSON_ERROR_NONE);
        }else{
            return false;
        }
    }
}