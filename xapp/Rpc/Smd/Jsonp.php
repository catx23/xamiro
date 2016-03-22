<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Smd');

/**
 * Rpc smd jsonp class
 *
 * @package Rpc
 * @subpackage Rpc_Smd
 * @class Xapp_Rpc_Smd_Jsonp
 * @error 149
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Smd_Jsonp extends Xapp_Rpc_Smd_Json
{
    /**
     * option defined jsonp callback parameter name
     *
     * @const CALLBACK
     */
    const CALLBACK                  = 'RPC_SMD_JSONP_CALLBACK';


    /**
     * contains static singleton class instance
     *
     * @var null|Xapp_Rpc_Smd_Jsonp
     */
    protected static $_instance = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CALLBACK              => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CALLBACK              => 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::IGNORE_PREFIXES           => array('__'),
        self::TRANSPORT                 => 'JSONP',
        self::ENVELOPE                  => 'URL',
        self::CONTENT_TYPE              => 'application/json',
        self::VERSION                   => '2.0',
        self::DESCRIPTION               => 'Xapp JSONP RPC Server',
        self::RELATIVE_TARGETS          => false,
        self::CALLBACK                  => 'callback',
        self::SERVICE_DESCRIPTION       => false,
        self::CLASS_METHOD_SEPARATOR    => '.'
    );


    /**
     * creates and returns singleton instance of class
     *
     * @error 14901
     * @param null|array|object $options expects optional class options
     * @return Xapp_Rpc_Smd_Jsonp
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
     * compile root part of smd schema overwriting parent json compile method
     *
     * @error 14902
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
        if(xapp_is_option(self::CALLBACK, $this))
        {
            $obj->jsonpCallbackParameter = xapp_get_option(self::CALLBACK, $this);
        }
        if(xapp_is_option(self::TARGET, $this))
        {
            $obj->target = xapp_get_option(self::TARGET, $this);
        }else{
            $obj->target = $this->getTarget();
        }
    }


    /**
     * compile service part of smd schema overwriting parent json compile method
     *
     * @error 14903
     * @param XO $obj expects reference variable of schema object
     * @return void
     */
    protected function compileService(&$obj)
    {
        $tmp = array();
        $separator = xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this);

        foreach($this->map() as $key => $val)
        {
            if(is_array($val))
            {
                foreach($val as $k => $v)
                {
                    $v->transport = 'GET';
                    $v->target = $this->getTarget($key . $separator . $k);
                    $tmp[$k] = $v;
                }
            }else{
                $val->transport = 'GET';
                $val->target = $this->getTarget($key);
                $tmp[$key] = $val;
            }
        }
        $obj->service = $tmp;
    }
}