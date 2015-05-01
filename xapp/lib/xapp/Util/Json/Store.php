<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Util.Exception.Json');

/**
 * Util json class
 *
 * @package Util
 * @class Xapp_Util_Json
 * @error 165
 * @author Frank Mueller
 */
class Xapp_Util_Json_Store extends Xapp_Util_Json_Query
{
    public function __construct($json)
    {
        parent::__construct($json);
    }

    public function get($path, $default = null)
    {

    }


    public function set($path, $value = null)
    {

    }


    public function replace($path, $value)
    {

    }


    public function append($path, $value = null)
    {

    }


    public function prepend($path, $value = null)
    {

    }


    public function copy($path1, $path1)
    {

    }


    public function remove($path)
    {

    }


    public function has($path)
    {

    }
}