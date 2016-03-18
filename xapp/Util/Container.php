<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Util container class
 *
 * @package Util
 * @class Xapp_Util_Container
 * @error 163
 * @author Frank Mueller
 */
class Xapp_Util_Container extends ArrayObject
{
    /**
     * contains the instance of ArrayIterator set at instance creation
     *
     * @var ArrayIterator|null
     */
    public $iterator = null;


    /**
     * constructor sets the ArrayIterator instance as reference and calls parent constructor
     *
     * @see ArrayObject::__construct
     * @error 16301
     * @param array|null|object $input expects a object or an array
     */
    public function __construct($input)
    {
        parent::__construct($input);
        $this->iterator =& $this->getIterator();
    }


    /**
     * get a value at a specific index as passed in first parameter. the index can be a array key
     * or formerly object property. if the first parameter is not set will return the currents index
     * value
     *
     * @error 16302
     * @param null|mixed $index
     * @return mixed
     */
    public function at($index = null)
    {
        if($index === null)
        {
            return $this->current();
        }else{
            return $this->offsetGet($index);
        }
    }


    /**
     * sets a value at the specified index passed in first parameter. the first parameter can be null which will
     * append the value at the end of the data collection
     *
     * @see ArrayObject::offsetSet
     * @error 16303
     * @param null|mixed $index expects optional index key
     * @param null|mixed $value expects the value to set
     * @return void
     */
    public function set($index = null, $value = null)
    {
        $this->offsetSet($index, $value);
    }


    /**
     * get a value at a specific index if not not found will return default value of second parameter. if the
     * first parameter is not set will return the value of current index
     *
     * @see ArrayObject::offsetGet
     * @error 16304
     * @param null|mixed $index expects the optional index kex
     * @param null $default expects the optional default return value
     * @return mixed|null
     */
    public function get($index = null, $default = null)
    {
        if($index === null)
        {
            return $this->current();
        }else{
            if($this->offsetExists($index))
            {
                return $this->offsetGet($index);
            }else{
                return xapp_default($default);
            }
        }
    }


    /**
     * checks whether the requested index exists or not. if the first parameter is not set will check if any
     * value is set at all in the data collection
     *
     * @see ArrayObject::offsetExists
     * @error 16305
     * @param null|mixed $index
     * @return bool
     */
    public function has($index = null)
    {
        if($index === null)
        {
            return ($this->count() > 0) ? true : false;
        }else{
            return $this->offsetExists($index);
        }
    }


    /**
     * searches the data collection for the value passed in first parameter and returns the index or false
     * if not found
     *
     * @error 16306
     * @param mixed $value expects the value to search index key for
     * @return mixed|bool
     */
    public function indexOf($value)
    {
        while($this->iterator->valid())
        {
            if($this->iterator->current() === $value)
            {
                return $this->iterator->key();
            }
            $this->iterator->next();
        }
    }


    /**
     * replace the old storage array with a new input array or object
     *
     * @error 16320
     * @param array|null|object $input expects a object or an array
     * @return array
     */
    public function replace($input)
    {
        return $this->exchangeArray($input);
    }


    /**
     * iterate the complete data collection and execute callback for each key => value pair passing the key
     * and the value to the callback function/method as first and second parameter. the callback must be
     * a valid callable that can be executed call_user_func_array
     *
     * @error 16307
     * @param callable $callback expects the callback function/method
     * @return void
     */
    public function each(Callable $callback)
   	{
   		while($this->iterator->valid())
        {
            call_user_func_array($callback, array($this->iterator->key(), $this->iterator->current()));
            $this->iterator->next();
        }
   	}


    /**
     * overwrites the ArrayObject serialize function so that:
     * - $container->serialize() will serialize only the data collection but not the object instance
     * - serialize($container) will serialize the whole container instance
     *
     * @error 16308
     * @return string
     */
    public function serialize()
    {
        $debug = debug_backtrace();
        if(is_array($debug) && array_key_exists('line', $debug[0]))
        {
            return serialize($this->getArrayCopy());
        }else{
            $this->rewind();
            return parent::serialize();
        }
    }


    /**
     * return the data collection as array
     *
     * @error 16309
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }


    /**
     * return the data collection as STD object
     *
     * @error 16310
     * @return object
     */
    public function toObject()
    {
        return (object)$this->getArrayCopy();
    }


    /**
     * return the data collection as json encoded string
     *
     * @error 16319
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }


    /**
     * prints the data collection to screen
     *
     * @error 16311
     * @return void
     */
    public function export()
    {
        print "<pre>";
        print_r($this->getArrayCopy());
        print "</pre>";
    }


    /**
     * overload class by requesting the value by calling $container->foo
     *
     * @see Xapp_Util_Container::get
     * @error 16312
     * @param mixed $name expects the index key
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->get($name, null);
    }


    /**
     * overload class by setting value at index calling $container->foo = $value
     *
     * @see Xapp_Util_Container::set
     * @error 16313
     * @param mixed $name expects the index key
     * @param mixed $value expects the value to set at index
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }


    /**
     * overload class by checking if index is set calling isset($container->foo)
     *
     * @see Xapp_Util_Container::set
     * @error 16314
     * @param mixed $name expects the index key
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }


    /**
     * overloading class by unsetting value by calling unset($container->foo)
     *
     * @error 16315
     * @param mixed $name expects the index key
     * @return void
     */
    public function __unset($name)
    {
        if($this->has($name))
        {
            $this->offsetUnset($name);
        }
    }


    /**
     * overloading class when calling for class methods which do not exists. if the method equals to
     * ('current', 'key', 'next', 'rewind', 'valid') will be forwarded to iterator instance so the iterator
     * can also be used without being in a functional loop like foreach. if the method name is not a iterator
     * validator but is missing an argument will try to get the method name = index from the data collection or
     * return null if not found. if the arg is not empty will try to set the method name = index and the value
     * from args to data collection
     *
     * @error 16316
     * @param mixed $name expects the index key
     * @param array $args expects optiona value to set
     * @return mixed|null
     */
    public function __call($name, $args)
    {
        if(in_array(strtolower($name), array('current', 'key', 'next', 'rewind', 'valid')))
        {
            return $this->iterator->$name();
        }else{
            if(empty($args))
            {
                if($this->has($name))
                {
                    return $this->get($name);
                }else{
                    return null;
                }
            }else{
                 $this->set($name, $args[0]);
            }
        }
    }


    /**
     * return spl object hash when casting to string
     *
     * @error 16317
     * @return string
     */
    public function __toString()
    {
        return spl_object_hash($this);
    }


    /**
     * reset the iterator pointer when cloning instance
     *
     * @error 16318
     */
    public function __clone()
    {
        $this->rewind();
    }
}