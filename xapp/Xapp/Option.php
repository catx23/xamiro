<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Xapp.Error');

/**
 * Option class
 *
 * @package Xapp
 * @class Xapp_Option
 * @error 103
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Option extends ArrayObject
{
    /**
     * class constructor to set option values as array or object
     *
     * @see ArrayObject
     * @error 10301
     * @param array|object $mixed expects a valid array or object
     * @param int $flags expects optional ArrayObject flags
     */
    public function __construct($mixed, $flags = 0)
    {
        parent::__construct($mixed, $flags);
    }


    /**
     * setter class for single option value
     *
     * @see ArrayObject::offsetGet
     * @error 10302
     * @param mixed $key expects array index
     * @return mixed value at index or null
     */
    public function get($key)
    {
        return parent::offsetGet($key);
    }


    /**
     * getter class for single option value
     *
     * @see ArrayObject::offsetSet
     * @error 10303
     * @param mixed $key expects array index
     * @param mixed $val expects the option value
     * @return Xapp_Option
     */
    public function set($key, $val = null)
    {
        parent::offsetSet($key, $val);
        return $this;
    }


    /**
     * check if option key is set
     *
     * @see ArrayObject::offsetExists
     * @error 10304
     * @param mixed $key expects the array index
     * @return boolean
     */
    public function has($key)
    {
        return parent::offsetExists($key);
    }


    /**
     * remove key from option
     *
     * @see ArrayObject::offsetUnset
     * @error 10305
     * @param mixed $key expects the array index
     * @return void
     */
    public function remove($key)
    {
        parent::offsetUnset($key);
    }


    /**
     * use overloading for all methods that are supported by ArrayAccess interface
     *
     * @see ArrayAccess
     * @error 10306
     * @param string $name expects overload function name
     * @param null|array $args expects overloading function arguments
     * @return null|mixed
     * @throws Xapp_Error
     */
    public function __call($name, $args = null)
    {
        $name = strtolower(trim($name));
        if(in_array($name, array('get', 'set', 'unset', 'exists')))
        {
            return call_user_func_array(array('parent', 'offset' . ucfirst($name)), (array)$args);
        }else{
            throw new Xapp_Error(xapp_sprintf(_("unable to overload class with: %s"), $name), 1030601);
        }
    }


    /**
     * php bug fix
     *
     * @error 10307
     * @return string
     */
    public function __toString()
    {
        return 'Array';
    }
}