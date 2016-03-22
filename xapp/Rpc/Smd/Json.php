<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Smd');

/**
 * Rpc smd json class
 *
 * @package Rpc
 * @subpackage Rpc_Smd
 * @class Xapp_Rpc_Smd_Json
 * @error 148
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Smd_Json extends Xapp_Rpc_Smd
{
    /**
     * option to define smd json transport protocol
     *
     * @const TRANSPORT
     */
    const TRANSPORT                 = 'RPC_SMD_JSON_TRANSPORT';

    /**
     * option to define smd json base target value
     *
     * @const TARGET
     */
    const TARGET                    = 'RPC_SMD_JSON_TARGET';

	/**
	 * option to define smd json base target value
	 *
	 * @const TARGET
	 */
	const METHOD_TARGET                    = 'RPC_SMD_JSON_METHOD_TARGET';

    /**
     * option to define smd json envelope value
     *
     * @const ENVELOPE
     */
    const ENVELOPE                  = 'RPC_SMD_JSON_ENVELOPE';


    /**
     * contains static singleton class instance
     *
     * @var null|Xapp_Rpc_Smd_Json
     */
    protected static $_instance = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::TRANSPORT             => XAPP_TYPE_STRING,
        self::TARGET                => XAPP_TYPE_STRING,
        self::ENVELOPE              => XAPP_TYPE_STRING,
        self::RELATIVE_TARGETS      => XAPP_TYPE_BOOL,
	    self::METHOD_TARGET         => XAPP_TYPE_BOOL
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::TRANSPORT             => 1,
        self::TARGET                => 0,
        self::ENVELOPE              => 1,
        self::RELATIVE_TARGETS      => 1,
	    self::METHOD_TARGET         => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::IGNORE_PREFIXES           => array('__'),
        self::TRANSPORT                 => 'POST',
        self::ENVELOPE                  => 'JSON-RPC-2.0',
        self::CONTENT_TYPE              => 'application/json',
        self::VERSION                   => '2.0',
        self::DESCRIPTION               => 'Xapp JSON RPC Server',
        self::RELATIVE_TARGETS          => false,
        self::SERVICE_DESCRIPTION       => false,
        self::CLASS_METHOD_SEPARATOR    => '.',
	    self::METHOD_TARGET             => true
    );


    /**
     * class constructor sets class options
     *
     * @error 14801
     * @param null|array|object $options expects optional class options
     */
    public function __construct($options = null)
    {
	    xapp_init_options($options, $this);
    }


    /**
     * creates and returns singleton instance of class
     *
     * @error 14802
     * @param null|array|object $options expects optional class options
     * @return Xapp_Rpc_Smd_Json
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
     * compile smd schema by calling compile function for root smd part, additional parameters
     * and service part. returns XO object which is a stdObject which can be casted to array and
     * digested by json_encode function
     *
     * @error 14803
     * @return XO
     */
    public function compile()
    {
	    $obj = new XO();
        $this->compileRoot($obj);
        $this->compileAdditionalParameter($obj);
        $this->compileService($obj);
        return $obj;
    }


    /**
     * compile root part of smd schema
     *
     * @error 14804
     * @param XO $obj expects reference variable of schema object
     * @return void
     */
    protected function compileRoot(&$obj)
    {
	    $obj->SMDVersion = xapp_get_option(self::VERSION, $this);
        if(xapp_is_option(self::DESCRIPTION, $this))
        {
            $obj->description = xapp_get_option(self::DESCRIPTION, $this);
        }
        if(xapp_is_option(self::CONTENT_TYPE, $this))
        {
            $obj->contentType = xapp_get_option(self::CONTENT_TYPE, $this);
        }
        $obj->transport = xapp_get_option(self::TRANSPORT, $this);
        $obj->envelope = xapp_get_option(self::ENVELOPE, $this);
        if(xapp_is_option(self::TARGET, $this))
        {
            $obj->target = xapp_get_option(self::TARGET, $this);
        }else{
            $obj->target = $this->getTarget();
        }
    }


    /**
     * compile additional parameter part of smd schema
     *
     * @error 14805
     * @param XO $obj expects reference variable of schema object
     * @return void
     */
    protected function compileAdditionalParameter(&$obj)
    {
        $tmp = array();

        if(xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
        {
            $obj->additionalParameters = true;
            foreach(xapp_get_option(self::ADDITIONAL_PARAMETERS, $this) as $k => $v)
            {
                $o = new XO();
                $o->name = $k;
                if(array_key_exists(0, $v))
                {
                    $o->type = $v[0];
                }
                if(array_key_exists(1, $v))
                {
                    $o->optional = $v[1];
                }
                if(array_key_exists(2, $v))
                {
                    $o->default = $v[2];
                }
                $tmp[] = $o;
            }
            $obj->parameters = $tmp;
        }
    }


    /**
     * compile service part of smd schema
     *
     * @error 14806
     * @param XO $obj expects reference variable of schema object
     * @return void
     */
    protected function compileService(&$obj)
    {
        $tmp = array();
        $separator = xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this);

        if(xapp_is_option(self::SERVICE_OVER_GET, $this))
        {
            foreach($this->map() as $key => $val)
            {
                if(is_array($val))
                {
                    foreach($val as $k => $v)
                    {
                        $v->transport = 'POST';
	                    if(xapp_is_option(self::METHOD_TARGET, $this))
	                    {
		                    $v->target = $this->getTarget("$key.$k");
	                    }
	                    $tmp[$key . $separator . $k] = $v;
                    }
                }else{
                    $val->transport = 'POST';
	                if(xapp_is_option(self::METHOD_TARGET, $this))
	                {
		                $val->target = $this->getTarget($key);
	                }
                    $tmp[$key] = $val;
                }
            }
        }else{
            foreach($this->map() as $key => $val)
            {
                if(is_array($val))
                {
                    foreach($val as $k => $v)
                    {
                        $v->transport = 'POST';
                        $v->target = $this->getTarget();
                        $tmp[$key . $separator . $k] = $v;
                    }
                }else{
                    $val->transport = 'POST';
	                if(xapp_is_option(self::METHOD_TARGET, $this))
	                {
		                $val->target = $this->getTarget();
	                }
                    $tmp[$key] = $val;
                }
            }
        }
        $obj->services = $tmp;
    }
}