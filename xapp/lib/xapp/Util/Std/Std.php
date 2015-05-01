<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

class Xapp_Util_Std extends stdClass
{
    public function __construct($data = null)
    {
        foreach((array)$data as $k => $v)
        {
            $this->$k = $v;
        }
    }


    public function extend($object)
    {
        if(is_object($object))
        {
            $vars = get_object_vars($object);
            foreach($vars as $k => $v)
            {
                $this->$k = $v;
            }
            return $this;
        }else{
            //error
        }
    }


    public function import($object)
    {
        if(is_object($object))
        {
            $this->reset();
            $this->extend($object);
            return $this;
        }else{
            //error
        }
    }


    public function reset()
    {
         foreach(array_keys(get_object_vars($this)) as $k)
         {
             unset($this->$k);
         }
    }

    //merge multiple objects to new one
    public static function merge(){}
}