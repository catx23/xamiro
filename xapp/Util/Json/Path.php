<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../core/core.php');

xapp_import('xapp.Util.Json.Std.Path');
xapp_import('xapp.Util.Json.Exception');

/**
 * Util json path class
 *
 * @package Util
 * @subpackage Util_Json
 * @class Xapp_Util_Json_Path
 * @error 173
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Util_Json_Path extends Xapp_Util_Std_Path
{
    /**
     * class constructor sets object as path target which can either by any object or array or instance of
     * Xapp_Util_Json_Store or Xapp_Util_Json_Query
     *
     * @error 17301
     * @param array|object|Xapp_Util_Json_Store|Xapp_Util_Json_Query $object expects object
     * @param null|mixed $options expects optional options
     * @throws Xapp_Util_Json_Exception
     */
    public function __construct($object, $options = null)
    {
        $class = get_class();

        xapp_init_options($options, $class);

        if($object instanceof Xapp_Util_Json_Store || $object instanceof Xapp_Util_Json_Query)
        {
            $this->object = $object;
        }else if(is_object($object)){
            $this->object = new Xapp_Util_Json_Query($object);
        }else if(is_array($object)){
            $this->object = new Xapp_Util_Json_Query($object);
        }else{
            throw new Xapp_Util_Json_Exception(_("first argument is not a valid object"), 1730101);
        }
    }
}