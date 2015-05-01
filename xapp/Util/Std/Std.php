<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Util.Std.Exception');

/**
 * Util std class
 *
 * @package Util
 * @subpackage Util_Std
 * @class Xapp_Util_Std
 * @error 166
 * @author Frank Mueller
 */
class Xapp_Util_Std extends stdClass
{
    /**
     * construct std extended class by passing optional data
     *
     * @error 16601
     * @param null|mixed $data expects data as array or object
     */
    public function __construct($data = null)
    {
        $this->append($data);
    }


    /**
     * static shortcut function to create new std class instance
     *
     * @error 16602
     * @param null|mixed $data expects data as array or object
     * @return Xapp_Util_Std
     */
    public static function create($data = null)
    {
        return new self($data);
    }


    /**
     * extend current std instance with object copying all properties
     *
     * @error 16603
     * @param object $object expects object instance
     * @return $this
     * @throws Xapp_Util_Std_Exception
     */
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
            throw new Xapp_Util_Std_Exception(_("passed object is not an object"), 1660301);
        }
    }


    /**
     * import object into current std instance resetting all properties first
     *
     * @error 16604
     * @param object $object expects object instance
     * @return $this
     * @throws Xapp_Util_Std_Exception
     */
    public function import($object)
    {
        if(is_object($object))
        {
            $this->reset();
            $this->extend($object);
            return $this;
        }else{
            throw new Xapp_Util_Std_Exception(_("passed object is not an object"), 1660302);
        }
    }


    /**
     * append mixed data (multidimensional array or object) to current instance
     *
     * @error 16605
     * @param array|object $data expects the object to append
     * @return $this
     */
    public function append($data)
    {
        foreach((array)$data as $k => $v)
        {
            if(is_object($v) || is_array($v))
            {
                $this->{$k} = self::create($v);
            }else{
                $this->{$k} = $v;
            }
        }
        return $this;
    }


    /**
     * get object/value from instance by property name or path defined by "." notation returning default value if
     * path can not be resolved
     *
     * @error 16606
     * @param string $name expects the property name or path
     * @param mixed $default expects the default return value
     * @return mixed|Xapp_Util_Std
     */
    public function get($name, $default = false)
    {
        $object = &$this;

        foreach(explode('.', trim($name, ' .')) as $n)
        {
            if(!is_object($object) || !isset($object->{$n}))
            {
                return $default;
            }
            $object = &$object->{$n};
        }

        return $object;
    }


    /**
     * set object/value to instance by property name or path defined by "." notation extending the instance if the
     * path does not exist yet
     *
     * @error 16607
     * @param string $name expects the property name or path
     * @param null|mixed $value expects the value to set
     * @return $this
     */
    public function set($name, $value = null)
    {
        $object = $this;

        $keys = explode('.', trim($name, '.'));
        while(count($keys) > 1)
        {
            $key = array_shift($keys);
            if(!isset($object->$key) || !is_object($object->$key))
            {
                $object->$key = new self();
            }
            $object = &$object->{$key};
        }
        $object->{array_shift($keys)} = $value;
        return $this;
    }


    /**
     * check if property or path exists and return boolean value
     *
     * @error 16608
     * @param string $name expects the property name or path
     * @return bool
     */
    public function has($name)
    {
        return ($this->get($name) !== false) ? true : false;
    }


    /**
     * reset all properties of instance
     *
     * @error 16609
     * @return void
     */
    public function reset()
    {
         foreach(array_keys(get_object_vars($this)) as $k)
         {
             unset($this->$k);
         }
    }


    /**
     * return object as array
     *
     * @error 16610
     * @return array
     */
    public function toArray()
    {
        return (array)$this;
    }


    /**
     * return object as json string
     *
     * @error 16611
     * @return string
     */
    public function toJson()
    {
        return json_encode($this);
    }


    /**
     * merges all objects, which can be std objects or arrays, passed in the function as new merged object
     *
     * @error 16612
     * @return null|object
     */
    public static function merge()
    {
        if(func_num_args() >= 2)
        {
            $n = new stdClass();
            foreach(func_get_args() as $o)
            {
                $n = (object)array_merge((array)$n, (array)$o);
            }
            return $n;
        }else{
            return null;
        }
    }


    /**
     * copies one objects properties to another defined by source and target
     *
     * @error 16613
     * @param object $source expects the source object
     * @param object $target ecpects the target object
     * @return object
     */
    public static function copy($source, &$target)
    {
        if(is_object($source) && is_object($target))
        {
            foreach(get_object_vars($source) as $k => $v)
            {
                $target->{$k} = $v;
            }
        }
        return $target;
    }


    /**
     * magic method to get property value will not raise warning but return null if property does not exist
     *
     * @error 16614
     * @param string $name expects the property name
     * @return null|mixed
     */
    public function __get($name)
    {
        if(array_key_exists($name, get_object_vars($this)))
        {
            return $this->{$name};
        }else{
            return null;
        }
    }


    /**
     * returns spl object hash string for object
     *
     * @error 16615
     * @return string
     */
    public function __toString()
    {
        return spl_object_hash($this);
    }
}