<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Server.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Server.Json');
xapp_import('xapp.Rpc.Smd.Jsonp');
xapp_import('xapp.Rpc.Response.Json');
xapp_import('xapp.Rpc.Request.Json');

/**
 * Rpc server jsonp class
 *
 * @package Rpc
 * @subpackage Rpc_Server
 * @class Xapp_Rpc_Server_Jsonp
 * @error 147
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Server_Jsonp extends Xapp_Rpc_Server_Json
{
    /**
     * option can contain jsonp callback parameter name
     *
     * @const CALLBACK
     */
    const CALLBACK                      = 'RPC_SERVER_JSONP_CALLBACK';

    /**
     * option to set custom js error callback function in which error object is wrapped
     *
     * @const ERROR_CALLBACK
     */
    const ERROR_CALLBACK                = 'RPC_SERVER_JSONP_ERROR_CALLBACK';


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CALLBACK                  => XAPP_TYPE_STRING,
        self::ERROR_CALLBACK            => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CALLBACK                  => 1,
        self::ERROR_CALLBACK            => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::DEBUG                     => false,
        self::ALLOW_FUNCTIONS           => false,
        self::VERSION                   => '2.0',
        self::DOJO_COMPATIBLE           => true,
        self::OMIT_ERROR                => false,
        self::SERVICE_OVER_GET          => true,
        self::APPLICATION_ERROR         => false,
        self::CALLBACK                  => 'callback',
        self::NAMESPACE_IDENTIFIER      => '_',
        self::CLASS_METHOD_SEPARATOR    => '.',
        self::COMPLY_TO_JSONRPC_1_2     => true,
        self::ALLOW_BATCHED_REQUESTS    => false
    );

    /**
     * contains singleton instance of class
     *
     * @var null|Xapp_Rpc_Server_Jsonp
     */
    protected static $_instance = null;


    /**
     * class constructor sets class options if smd instance is not set in class options
     * will create smd instance with appropriate options.
     *
     * @error 14701
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        xapp_set_options($options, $this);
        if(!xapp_is_option(self::SMD, $this))
        {
            $opt = array
            (
                Xapp_Rpc_Smd_Jsonp::TRANSPORT => 'JSONP',
                Xapp_Rpc_Smd_Jsonp::ENVELOPE => 'URL',
                Xapp_Rpc_Smd_Jsonp::RELATIVE_TARGETS => false,
                Xapp_Rpc_Smd_Jsonp::CALLBACK => xapp_get_option(self::CALLBACK, $this)
            );
            xapp_set_option(self::SMD, Xapp_Rpc_Smd_Jsonp::instance($opt), $this);
        }
        parent::__construct();
    }


    /**
     * creates and returns singleton instance of class
     *
     * @error 14702
     * @param null|array|object $options expects optional options
     * @return Xapp_Rpc_Server_Jsonp
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }


    /**
     * init server by extracting class and function/method value from service parameter found
     * in $_GET parameter. the service must be always contained in service parameter and can also
     * be set by htaccess rewrite rule resolving the url path to service $_GET parameter like:
     * http://foo.com/gateway/class.method. resulting in service $_GET parameter service=class.method
     * which can be resolved by server to method/function and class. will remove all additional parameter
     * from get array so params array only contains parameter needed for method or function call. Use namespace
     * identifier RPC_SERVER_NAMESPACE_IDENTIFIER, defaults to "/" to resolve complex class names/paths like #
     * http://foo.com/gateway/Api/Foo/User.login to class "Api/Foo/User" and method "login".
     *
     * @error 14704
     * @return void
     * @throws Xapp_Rpc_Fault
     */
    protected function init()
    {
        $get        = $this->request()->getGet();
        $params     = null;
        $service    = $this->_service;
        $response   = $this->response();

        if(!empty($get))
        {
            if(array_key_exists('service', $get))
            {
                unset($get['service']);
            }
            if(array_key_exists(xapp_get_option(self::CALLBACK, $this), $get))
            {
                unset($get[xapp_get_option(self::CALLBACK, $this)]);
            }
            if(xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
            {
                foreach(xapp_get_option(self::ADDITIONAL_PARAMETERS, $this) as $k => $v)
                {
                    if(array_key_exists($k, $get)) unset($get[$k]);
                }
            }
            $params = (!empty($get)) ? $get : null;
        }
        if($service !== null)
        {
            if(strpos($service, xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this)) !== false)
            {
                $call = explode(xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this), trim($service, ' ' . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this)));
                $this->_calls[] = array
                (
                    $this->_services[] = $call[0] . xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this) . $call[1],
                    $call[1],
                    $call[0],
                    $params,
                    self::CALL
                );
            }else{
                $this->_calls[] = array
                (
                    $this->_services[] = $service,
                    $service,
                    null,
                    $params,
                    self::CALL
                );
            }
        }
        if(xapp_has_option(self::DOJO_COMPATIBLE, $this))
        {
            xapp_set_option(Xapp_Rpc_Response_Json::DOJO_COMPATIBLE, xapp_get_option(self::DOJO_COMPATIBLE, $this), $response);
        }

        $get = null;
        $params = null;
        $service = null;
        $response = null;
    }


    /**
     * validate jsonp request testing for request parameter to be valid and checking all
     * additional parameters
     *
     * @error 14705
     * @param array $call expects the call to validate
     * @return void
     * @throws Xapp_Rpc_Fault
     */
    protected function validate($call)
    {
        $get = $this->request()->getGet();
        $service = null;

        if($this->smd()->has($call[2], $call[1]))
        {
            if($call[2] !== null)
            {
                $service = $this->smd()->get($call[2] . '.' . $call[1]);
            }else{
                $service = $this->smd()->get($call[1]);
            }
        }else{
            Xapp_Rpc_Fault::t("method or function is not registered as service", array(1470501, -32601));
        }
        if(!empty($service->parameters))
        {
            foreach($service->parameters as $k => $v)
            {
                if(!$v->optional && (!array_key_exists($v->name, $get) || !xapp_is_value($get[$v->name])))
                {
                    Xapp_Rpc_Fault::t(xapp_sprintf("param: %s must be set", array($v->name)), array(1470503, -32602));
                }
                if(isset($v->type) && array_key_exists($v->name, $get) && !in_array('mixed', (array)$v->type) && !in_array(xapp_type($get[$v->name], true), (array)$v->type))
                {
                    Xapp_Rpc_Fault::t(xapp_sprintf("param: %s must be of the following types: %s", array($v->name, implode('|', (array)$v->type))), array(1470504, -32602));
                }
            }
        }
        if(xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
        {
            foreach(xapp_get_option(self::ADDITIONAL_PARAMETERS, $this) as $k => $v)
            {
                $type = (isset($v[0])) ? (array)$v[0] : false;
                $optional = (isset($v[1])) ? (bool)$v[1] : true;
                if(!$optional && !array_key_exists($k, $get))
                {
                    Xapp_Rpc_Fault::t(xapp_sprintf("additional param: %s must be set", array($k)), array(1470505, -32602));
                }
                if($type && !in_array('mixed', $type) && !in_array(xapp_type($get[$k], true), $type))
                {
                    Xapp_Rpc_Fault::t(xapp_sprintf("additional param: %s must be of the following types: %s", array($k, implode('|', $type))), array(1470506, -32602));
                }
            }
        }
    }


    /**
     * executing and validating requested service found in jsonp GET request returning result either as json object
     * or wrapped inside jsonp callback
     *
     * @error 14706
     * @param array $call
     * @throws Exception|mixed|string
     */
    protected function execute($call)
    {
        $get        = $this->request()->getGet();
        $response   = $this->response();

        try
        {
            $this->validate($call);
            $result = $this->invoke($call, $call[3]);
            if(array_key_exists(xapp_get_option(self::CALLBACK, $this), $get))
            {
                $result = $get[xapp_get_option(self::CALLBACK, $this)] . '(' . $response->encode($result) . ')';
            }else{
                $result = $response->encode($result);
            }
            $response->body($result);
        }
        catch(Exception $e)
        {
            if(xapp_is_option(self::EXCEPTION_CALLBACK, $this))
            {
                $e = call_user_func_array(xapp_get_option(self::EXCEPTION_CALLBACK, $this), array(&$e, $this, $call));
                if(!($e instanceof Exception))
                {
                    if(xapp_get_option(self::COMPLY_TO_JSONRPC_1_2, $this))
                    {
                        if(array_key_exists($e->getCode(), $this->codeMap))
                        {
                            xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, $this->codeMap[$e->getCode()], $response);
                        }else{
                            xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, 500, $response);
                        }
                    }
                    if(xapp_is_option(self::ERROR_CALLBACK, $this) && array_key_exists(xapp_get_option(self::ERROR_CALLBACK, $this), $get))
                    {
                        $e = $get[xapp_get_option(self::ERROR_CALLBACK, $this)] . '(' . $response->encode($e) . ')';
                    }else if(array_key_exists(xapp_get_option(self::CALLBACK, $this), $get)){
                        $e = $get[xapp_get_option(self::CALLBACK, $this)] . '(' . $response->encode($e) . ')';
                    }else{
                        $e = $response->encode($e);
                    }
                    $response->body($e);
                }
            }
            throw $e;
        }
    }


    /**
     * flush response
     *
     * @error 14707
     * @return void
     */
    protected function flush()
    {
        $this->response()->flush();
    }


    /**
     * flush exception as json error object encapsulated in js callback or custom error callback function if supplied
     *
     * @error 14708
     * @param Exception $error expects the exception to flush
     */
    public function error(Exception $error)
    {
        $get        = $this->request()->getGet();
        $response   = $this->response();

        if(xapp_get_option(self::COMPLY_TO_JSONRPC_1_2, $this))
        {
            if(array_key_exists($error->getCode(), $this->codeMap))
            {
                xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, $this->codeMap[$error->getCode()], $response);
            }else{
                xapp_set_option(Xapp_Rpc_Response::STATUS_CODE, 500, $response);
            }
        }

        $error  = $this->compileError($error);

        if(xapp_is_option(self::ERROR_CALLBACK, $this) && array_key_exists(xapp_get_option(self::ERROR_CALLBACK, $this), $get))
        {
            $error = $get[xapp_get_option(self::ERROR_CALLBACK, $this)] . '(' . $response->encode($error) . ')';
        }else if(array_key_exists(xapp_get_option(self::CALLBACK, $this), $get)){
            $error = $get[xapp_get_option(self::CALLBACK, $this)] . '(' . $response->encode($error) . ')';
        }else{
            $error = $response->encode($error);
        }
        $response->body($error);
        $this->flush();
    }
}