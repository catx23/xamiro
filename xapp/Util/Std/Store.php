<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Util.Std.Exception');
xapp_import('xapp.Util.Std');

/**
 * Util std store class
 *
 * @package Util
 * @subpackage Util_Std
 * @class Xapp_Util_Std_Store
 * @error 170
 * @author Frank Mueller
 */
class Xapp_Util_Std_Store /*implements Serializable*/
{
    /**
     * contains the std object either constructor by class or passed/loaded in constructor
     *
     * @var mixed|null
     */
    protected $_object = null;

    /**
     * contains result value when using Xapp_Util_Std_Store::query building
     *
     * @var mixed|null
     */
    protected $_result = null;

    /**
     * internal result init variable holder so queries can be reset and started from root
     *
     * @var bool
     */
    protected $_init = false;

    /**
     * contains dynamic path when using class overloading for getter/setter methods
     *
     * @var array
     */
    protected $_path = array();

    /**
     * contains the file name pointer passed as first possible class constructor value
     *
     * @var null|string
     */
    protected $_file = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public static $options = array
    (
    );


    /**
     * class constructor can receive the following first argument:
     * - null
     * - object expects the working object passed in constructor
     * - array expects any working array that will be converted to object
     * - string as serialized value
     * - file name pointer that exists (will load object from file)
     * - file name pointer that does not exists but will be used as save location
     * will throw exception if any of the above values fails to validate. NOTE: return values from certain methods
     * are returning a reference to the object of the store. the reference can be manipulated outside the class if
     * the methods are called like $ref =& $store->get('//path');
     *
     * @error 17001
     * @param null|mixed $mixed expects one of the above value options
     * @param null|mixed $options expects optional class instance options
     * @throws Xapp_Util_Std_Exception
     */
    public function __construct($mixed = null, $options = null)
    {
        xapp_init_options($options, $this);
        if($mixed !== null)
        {
            if(is_object($mixed))
            {
                $this->_object =& $mixed;
            }else if(is_array($mixed)){
                if(array_keys($mixed) === range(0, count($mixed) - 1))
                {
                    $this->_object = $mixed;
                }else{
                    $this->_object = (object)$mixed;
                }
            }else{
                if(is_file($mixed))
                {
                    $this->_file = $mixed;
                    if(($mixed = file_get_contents($mixed)) !== false)
                    {
                        $this->_object = self::decode($mixed);
                    }else{
                        throw new Xapp_Util_Std_Exception(xapp_sprintf(_("unable to read from file: %s"), $mixed), 1700101);
                    }
                }else{
                    if(is_string($mixed) && preg_match('/^([adObis]\:|N\;)/', trim($mixed)))
                    {
                        $this->_object = self::decode($mixed);
                    }else if(is_string($mixed) && strpos($mixed, '.') !== false && is_writeable(dirname($mixed))){
                        $this->_file = $mixed;
                    }else{
                        throw new Xapp_Util_Std_Exception(_("passed first argument in constructor is not a valid object or file path"), 1700102);
                    }
                }
            }
        }
    }

    public function getResult(){
        return $this->_result;
    }


    /**
     * static method to create class instance to be used for query connecting or other purposes
     *
     * @error 17002
     * @see Xapp_Util_Std_Store::__constructor
     * @param null|mixed $mixed expects one of the above value options
     * @param null|mixed $options expects optional class instance options
     * @return Xapp_Util_Std_Store
     */
    public static function create($mixed = null, $options = null)
    {
        $class = get_called_class();
        return new $class($mixed, $options);
    }


    /**
     * init function is used to start query from root of object since ::get() will not reset the current query result.
     * unless init is called the query and return of ::get() while remain in queried path. use the second parameter "false"
     * to force a hard result unset - use only when you result is not important because its a reference value
     *
     * @error 17003
     * @param bool $soft expects init type as explained above
     * @return $this
     */
    public function init($soft = true)
    {
        $this->_init = true;
        if(!(bool)$soft)
        {
            unset($this->_result);
        }
        return $this;
    }


    /**
     * retrieve or set object if not done so in constructor or add method. this function may be used it the constructor
     * is used to set the target save file path
     *
     * @error 17004
     * @param null|mixed $object expects optional object value which should be an array or object
     * @return null|object
     */
    public function object(&$object = null)
    {
        if($object !== null)
        {
            if(is_object($object))
            {
                $this->_object =& $object;
            }else{
                $this->_object = (object)$object;
            }
        }
        $this->init(true);
        return $this->_object;
    }


    /**
     * query the object with path and query parameters from Xapp_Util_Std_Query::find method and use store
     * methods on search result, e.g.
     * <code>
     * $store = Xapp_Util_Json_Store::create($object)
              ->query('/firstElement', array("id=1000"))
              ->query('.', array('title=foo'))
              ->set(null, 1);
     * </code>
     *
     * the query method must be terminated with a store method!
     *
     * @see Xapp_Util_Std_Query::find
     * @error 17005
     * @param string $path expects the optional search path as outlined above
     * @param array $query expects the optional query filter chain as explained above
     * @param null|string $result_flag expects optional result flag "first" or "last"
     * @return $this
     */
    public function query($path, Array $query = null, $result_flag = null)
    {
        if($this->_result !== null && !$this->_init)
        {
            $this->_result =& Xapp_Util_Std_Query::find($this->_result, $path, $query, $result_flag);
        }else{
            $this->_result =& Xapp_Util_Std_Query::find($this->_object, $path, $query, $result_flag);
        }
        $this->_init = false;
        return $this;
    }


    /**
     * get a value from object by path or path/query or path/query set when using query method to get result of
     * connected query. in this case a result must be present and the first argument must be null
     *
     * @error 17006
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @return boolean|mixed
     */
    public function &get($mixed = null)
    {
        $return = null;

        if($mixed === null)
        {
            if($this->_result !== null)
            {
                return $this->_result;
            }else{
                return false;
            }
        }else{
            return self::_get($this->_object, $mixed);
        }
    }


    /**
     * get result from query/path return, see Store::get and rewind the path pointer to start at root of object.
     * this method can be also called with Store::get and then Store::init. use when in loops to rewind to object root
     *
     * @error 17035
     * @see Store::get
     * @return bool|mixed|null
     */
    public function &rewind()
    {
        if($this->_result !== null)
        {
            $this->init();
            return $this->_result;
        }else{
            return false;
        }
    }


    /**
     * checks if any value is found under path or path/query even if the value is null
     *
     * @error 17007
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @return bool
     */
    public function has($mixed)
    {
        return ($this->get($mixed) !== false) ? true : false;
    }


    /**
     * set any value at path or path/query if found. the third parameter is an optional position which can be null which
     * is the default behaviour overwriting the value at path/query if found. if the value found at path/query is either
     * an array or object the third argument can be one of the following values:
     * - 0|first = at first position of array or object
     * - -1|last = at last position of array or object
     * - [1-?] = a number that is not -1 to set the value at a specific position of array or object
     * using the third parameter requires knowledge of the values structure to work
     *
     * @error 17008
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @param mixed $value expects the value to set at path/query
     * @param null|string|int $position expects the optional position value
     * @return $this
     */
    public function set($mixed = null, $value, $position = null)
    {
        if($this->_result !== null)
        {
            self::_set($this->_result, $mixed, $value, true, $position, false);
        }else{
            self::_set($this->_object, $mixed, $value, true, $position, false);
        }
        return $this;
    }


    /**
     * add key => value pair, object or array to store. this method is intended to fill or create the object without
     * using the class constructor capabilities usually in a scenario where multiple objects or key => value pairs are
     * received from other functionality to furnish the store prior to manipulation. adding values will append those to
     * the path given in first parameter
     *
     * @error 17009
     * @param string $path expects a path where to set the value
     * @param string|mixed $mixed expects either a property name or an object or array
     * @param string|mixed $value expects a value which is not __NIL__ when setting key => value pairs
     * @return $this
     */
    public function add($path, $mixed, $value = '__NIL__')
    {
        if($this->_object === null)
        {
            $this->_object = new stdClass();
        }
        if((is_object($mixed) || is_array($mixed)) && $value === '__NIL__')
        {
            $this->append($path, (object)$mixed);
        }else{
            $this->append($path, (object)array($mixed => $value));
        }
        return $this;
    }


    /**
     * replace a value at path or path/query either typesafe making sure the set value is of the same type as value found
     * at path/query or if set to false overwriting it regardless of data type
     *
     * @error 17010
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @param mixed $value expects value to replace at path/query
     * @param bool $typesafe expects typesafe value
     * @return $this
     */
    public function replace($mixed = null, $value, $typesafe = false)
    {
        if($this->_result !== null)
        {
            self::_set($this->_result, $mixed, $value, false, null, $typesafe);
        }else{
            self::_set($this->_object, $mixed, $value, false, null, $typesafe);
        }
        return $this;
    }


    /**
     * append a value at path or path/query
     *
     * @error 17011
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @param mixed $value expects the value to append at path/query
     * @return $this
     */
    public function append($mixed = null, $value)
    {
        if($this->_result !== null)
        {
            self::_set($this->_result, $mixed, $value, true, -1, false);
        }else{
            self::_set($this->_object, $mixed, $value, true, -1, false);
        }
        return $this;
    }


    /**
     * prepend a value at path or path/query
     *
     * @error 17012
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @param mixed $value expects the value to prepend at path/query
     * @return $this
     */
    public function prepend($mixed = null, $value)
    {
        if($this->_result !== null)
        {
            self::_set($this->_result, $mixed, $value, true, 0, false);
        }else{
            self::_set($this->_object, $mixed, $value, true, 0, false);
        }
        return $this;
    }


    /**
     * inject a value at specific position at path or path/query. if the position does not exist will append the value
     * to value found at path/query. See Xapp_Util_Std_Store::set for possible positions
     *
     * @error 17013
     * @see Xapp_Util_Std_Store::set
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @param mixed $value expects the value to prepend at path/query
     * @param null|string|int $position expects the optional position value
     * @return $this
     */
    public function inject($mixed = null, $value, $position)
    {
        if($this->_result !== null)
        {
            self::_set($this->_result, $mixed, $value, true, (int)$position, false);
        }else{
            self::_set($this->_object, $mixed, $value, true, (int)$position, false);
        }
        return $this;
    }


    /**
     * remove any value found at path or path/query
     *
     * @error 17014
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @return $this
     */
    public function remove($mixed = null)
    {
        if($this->_result !== null)
        {
            self::_set($this->_result, $mixed, '__REMOVE__', false, null, false);
        }else{
            self::_set($this->_object, $mixed, '__REMOVE__', false, null, false);
        }
        return $this;
    }


    /**
     * reset the value to null found at path or path/query
     *
     * @error 17015
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @return $this
     */
    public function reset($mixed = null)
    {
        if($this->_result !== null)
        {
            self::_set($this->_result, $mixed, '__RESET__', false, null, false);
        }else{
            self::_set($this->_object, $mixed, '__RESET__', false, null, false);
        }
        return $this;
    }


    /**
     * copy value found at path or path/query of first parameter to value found at path or path/query in second parameter
     *
     * @error 17016
     * @param string|array $mixed1 expects path as string or path/query as array
     * @param string|array $mixed2 expects path as string or path/query as array
     * @return void
     */
    public function copy($mixed1, $mixed2)
    {
        self::_set($this->_object, $mixed2, self::_get($this->_object, $mixed1), true, null, false);
    }


    /**
     * merge value found at path or path/query at first parameter with value found for path or path/query of second
     * parameter. both values must be of same data type - it is only allowed to merge objects or array!
     *
     * @error 17017
     * @param string|array $mixed1 expects path as string or path/query as array
     * @param string|array $mixed2 expects path as string or path/query as array
     * @throws Xapp_Util_Std_Exception
     * @return void
     */
    public function merge($mixed1, $mixed2)
    {
        $_mixed1 = self::_get($this->_object, $mixed1);
        $mixed2 =& self::_get($this->_object, $mixed2);
        if(xapp_type($_mixed1) === xapp_type($mixed2))
        {
            if(is_array($mixed2))
            {
                $mixed2 = array_merge((array)$mixed2, (array)$_mixed1);
            }else{
                $mixed2 = (object)array_merge((array)$mixed2, (array)$_mixed1);
            }
            $this->remove($mixed1);
        }else{
            throw new Xapp_Util_Std_Exception(_("merging values at paths only allowed for values of same type"), 1701701);
        }
    }


    /**
     * serialize the stores object if first parameter is null or serialize value found at path passed in first argument.
     * returns serialized value
     *
     * @error 17018
     * @param null|string $path expects optional path to serialize value at
     * @return string
     * @throws Xapp_Util_Std_Exception
     */
    public function serialize($path = null)
    {
        if($path !== null)
        {
            if(is_string($path))
            {
                $result =& self::_get($this->_object, $path, false, true);
                $result = serialize($result);
                return $result;
            }else{
                throw new Xapp_Util_Std_Exception(_("first parameter: \$path must be a string"), 1701801);
            }
        }else{
            return serialize($this->_object);
        }
    }


    /**
     * unserialize any value found at path or path/query and return unserialized value
     *
     * @error 17019
     * @param string $path expects path or path/query
     * @return mixed
     * @throws Xapp_Util_Std_Exception
     */
    public function &unserialize($path)
    {
        if(is_string($path))
        {
            return self::_get($this->_object, $path, false, true);
        }else{
            throw new Xapp_Util_Std_Exception(_("first parameter: \$path must be a string"), 1701901);
        }
    }


    /**
     * static object getter function. pass any object in first parameter to retrieve value at second parameter which
     * can be of the following:
     * - string path (to get value at a known path)
     * - array with path and query parameters used in Xapp_Util_Std_Query::find method
     * e.g.
     * $store->get($object, '//root/0');
     * $store->get($object, array('/root', array('id=1');
     *
     * the third parameter if set to true will try to unserialize any value found at second parameter. the method
     * will throw a result exception if nothing was found at second parameter. this method can return a full reference
     * to the result found at second parameter. to use the reference outside class use the class like:
     * e.g.
     * $result =& $store->get('//root/0'):
     *
     * @error 17020
     * @see Xapp_Util_Std_Query::find
     * @see Xapp_Util_Std_Query::retrieve
     * @param object $object expects the object to get values from
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @param bool $unserialize expects boolean value for auto unserialization
     * @return array|bool|mixed
     * @throws Xapp_Result_Exception
     */
    public static function &_get(&$object, $mixed, $unserialize = false)
    {
        $return = null;

        if(is_array($mixed) && sizeof($mixed) >= 2)
        {
            if(array_key_exists(2, $mixed))
            {
                $mixed[2] = strtolower(trim((string)$mixed[2]));
            }else{
                $mixed[2] = null;
            }
            $return =& Xapp_Util_Std_Query::find($object, $mixed[0], (array)$mixed[1], $mixed[2]);
        }else{
            $return =& Xapp_Util_Std_Query::retrieve($object, $mixed);
        }
        if((bool)$unserialize === true)
        {
            return $return = self::decode($return);
        }else{
            return $return;
        }
    }


    /**
     * set any value to object at any point in object structure defined by second parameter which can be a path or
     * path/query array value. the fourth parameter defines if the object is extendable that when nothing was found at
     * path/query the value will be created/append there. the fifth parameter is an optional position value, see
     * Xapp_Util_Std_Query::set for more details. the last optional parameter defines whether the value will only be set
     * /overwritten if the to be replaced value is of the same type. will throw result exception if path/query does not
     * return any value and fourth parameter is to false
     *
     * @error 17021
     * @see Xapp_Util_Std_Query::find
     * @see Xapp_Util_Std_Query::retrieve
     * @param object $object expects the object to set values to
     * @param null|string|array $mixed expects either null for connected query call, string for path or array for path/query
     * @param null|mixed $value expects the value to set at second parameter
     * @param bool $extend expects optional extend value as explained above
     * @param null|string|int $position expects optional set position as explained in Xapp_Util_Std_Query::set
     * @param bool $typesafe expects optional typesafe value as explained above
     * @throws Xapp_Result_Exception
     * @throws Xapp_Util_Std_Exception
     */
    public static function _set(&$object, $mixed = null, $value = null, $extend = true, $position = null, $typesafe = false)
    {
        $last = null;
        $parent = null;
        $extend = (bool)$extend;
        $typesafe = (bool)$typesafe;

        if($mixed === null)
        {
            $mixed = '';
        }else if(is_array($mixed)){
            $mixed[0] = trim($mixed[0], './* ');
        }else if(is_string($mixed)){
            $mixed = trim($mixed, './* ');
        }

        try
        {
            if($value === '__REMOVE__' && is_string($mixed))
            {
                if(substr($mixed, 0, 1) !== '/' && strpos($mixed, '/') !== false)
                {
                    $last = substr($mixed, strrpos($mixed, '/') + 1);
                    $mixed = substr($mixed, 0, strrpos($mixed, '/') );
                }else{
                    $last = $mixed;
                    $mixed = '';
                }
            }
            if(is_array($mixed) && sizeof($mixed) >= 2)
            {
                if(array_key_exists(2, $mixed))
                {
                    $mixed[2] = strtolower(trim((string)$mixed[2]));
                }else{
                    $mixed[2] = 'first';
                }
                $result =& Xapp_Util_Std_Query::find($object, $mixed[0], (array)$mixed[1], $mixed[2], $parent);
            }else{
                $result =& Xapp_Util_Std_Query::retrieve($object, $mixed, false, $parent);
            }
        }
        catch(Xapp_Result_Exception $e)
        {
            $result = false;
        }
        if($result !== false)
        {
            if(is_array($result))
            {
                if($value === '__REMOVE__')
                {
                    unset($result[(int)$last]);
                }else if($value === '__RESET__'){
                    $result = null;
                }else if($value === '__SERIALIZE__'){
                    $result = self::encode($result);
                }else{
                    if($position !== null)
                    {
                        if($position === -1 || $position === 'last')
                        {
                            array_push($result, $value);
                        }else if($position === 0 || $position === 'first'){
                            array_unshift($result, $value);
                        }else if(is_int($position)){
                            if($position < sizeof($result))
                            {
                                array_splice($result, $position, 0, array($value));
                            }else{
                                array_push($result, $value);
                            }
                        }
                    }else{
                        if($typesafe)
                        {
                            if(xapp_type($result) === xapp_type($value))
                            {
                                $result = $value;
                            }else{
                                throw new Xapp_Util_Std_Exception(_("value found at path can not be overwritten due to typesafe protection"), 1702101);
                            }
                        }else{
                            $result = $value;
                        }
                    }
                }
            }else{
                if($value === '__REMOVE__')
                {
                    unset($result->{$last});
                }else if($value === '__RESET__'){
                    $result = null;
                }else if($value === '__SERIALIZE__'){
                    $result = self::encode($result);
                }else{
                    if($position !== null)
                    {
                        if($position === -1 || $position === 'last')
                        {
                            $result = (object)array_merge((array)$result, (array)$value);
                        }else if($position === 0 || $position === 'first'){
                            $result = (object)array_merge((array)$value, (array)$result);
                        }else if(is_int($position)){
                            if($position < sizeof((array)$result))
                            {
                                $result = (object)array_merge(array_slice((array)$result, 0, $position), (array)$value, array_slice((array)$result, $position));
                            }else{
                                $result = (object)array_merge((array)$result, (array)$value);
                            }
                        }
                    }else{
                        if($typesafe)
                        {
                            if(xapp_type($result) === xapp_type($value))
                            {
                                $result = $value;
                            }else{
                                throw new Xapp_Util_Std_Exception(_("value found at path can not be overwritten due to typesafe protection"), 1702101);
                            }
                        }else{
                            $result = $value;
                        }
                    }
                }
            }
        }else{
            if($extend && is_object($parent))
            {
                $parent->{trim(substr($mixed, strrpos($mixed, '/') + 1))} = $value;
            }else{
                throw new Xapp_Result_Exception(_("no result found for query"), 1702102);
            }
        }
    }


    /**
     * decode a value which for std object will be a serialized string
     *
     * @error 17022
     * @param mixed $value expects the value to try to decode
     * @return mixed
     */
    public static function decode($value)
    {
        if(is_string($value))
        {
            return unserialize($value);
        }
        return $value;
    }


    /**
     * encode a value by serializing it
     *
     * @error 17023
     * @param mixed $value expects any value to encode
     * @return string
     */
    public static function encode($value)
    {
        return serialize($value);
    }


    /**
     * dump/print stores object to screen
     *
     * @error 17024
     * @return void
     */
    public function dump()
    {
        echo ((strtolower(php_sapi_name()) === 'cli') ? print_r($this->_object, true) : "<pre>".print_r($this->_object, true)."</pre>");
    }


    /**
     * save object encoded to file if file path has been already set via constructor or if not simply return the ready
     * to save encoded string for further manipulation. throws exception if unable to write to file
     *
     * @error 17025
     * @return bool
     * @throws Xapp_Util_Std_Exception
     */
    public function save()
    {
        if($this->_file !== null)
        {
            if(file_put_contents($this->_file, self::encode($this->_object), LOCK_EX))
            {
                return true;
            }else{
                throw new Xapp_Util_Std_Exception(xapp_sprintf(_("unable to save to file: %s"), $this->_file), 1702501);
            }
        }else{
            return self::encode($this->_object);
        }
    }


    /**
     * save object encoded to file passed in first parameter which is expected to be a valid file pointer. will throw
     * exception if file is not writeable
     *
     * @error 17026
     * @param string $file expects absolute file path to store object at
     * @return bool
     * @throws Xapp_Util_Std_Exception
     */
    public function saveTo($file)
    {
        if(file_put_contents($file, self::encode($this->_object), LOCK_EX))
        {
            return true;
        }else{
            throw new Xapp_Util_Std_Exception(xapp_sprintf(_("unable to save to file: %s"), $file), 1702601);
        }
    }


    /**
     * set path to work with Xapp_Util_Std_Store::exec
     *
     * @error 17027
     * @see Xapp_Util_Std_Store::exec
     * @param string $path expects path element one for each depth
     * @param null|int $index expects optional index if path element is array
     * @return $this
     */
    public function path($path, $index = null)
    {
        if($index !== null)
        {
            array_push($this->_path, array(trim((string)$path), (int)$index));
        }else{
            array_push($this->_path, array(trim((string)$path), null));
        }
        return $this;
    }


    /**
     * magically set/get values in conjunction with using either magic method __call overloading instance with method names
     * not found which will be translated to object path, e.g.
     * - $store->root->element->exec(); //get value at path //root/element
     * - $store->root->element(0)->exec('value'); //set value at path //root/element/0
     * or using the path() method like:
     * - $store->path('root')->path('element')->exec(); //get value at path //root/element
     * - $store->path('root')->path('element', 0)->exec('value'); //set value at path //root/element/0
     * the method expects that at least one path element exists otherwise nothing will happen
     *
     * @error 17028
     * @param string $value
     * @return bool|mixed|null
     */
    public function &exec($value = '__NULL__')
    {
        $tmp = array();
        $return = null;

        if(!empty($this->_path))
        {
            foreach($this->_path as $p)
            {
                $tmp[] = $p[0];
                if(array_key_exists(1, $p) && !is_null($p[1]))
                {
                    $tmp[] = (int)$p[1];
                }
            }
            if($value !== '__NULL__')
            {
                $this->set(implode('/', $tmp), $value);
            }else{
                $return =& $this->get(implode('/', $tmp));
            }
        }
        $this->_path = array();
        return $return;
    }


    /**
     * magic method __call overloading class only in conjunction with Xapp_Util_Std_Store::exec
     *
     * @error 17029
     * @see Xapp_Util_Std_Store::exec
     * @param string $name expects the path element
     * @param array $args expects optional argument which is expected to by array index if path element is array
     * @return $this
     */
    public function __call($name, Array $args)
    {
        if(!empty($args))
        {
            array_push($this->_path, array($name, (int)$args[0]));
        }else{
            array_push($this->_path, array($name, null));
        }
        return $this;
    }


    /**
     * overloading class through setting value for properties will append property name and value to object on first
     * level of depth
     *
     * @error 17030
     * @param string $name expects the key name for value
     * @param mixed $value expects the value to set for key name
     * @return void
     */
    public function __set($name, $value)
    {
        $this->add(null, $name, $value);
    }


    /**
     * overloading class through getting value for properties will get value at first parameter which must be object key
     * name in first level of depth
     *
     * @error 17031
     * @param string $name gets the value at first parameter which is key name
     * @return bool|mixed
     */
    public function &__get($name)
    {
        return $this->get("/$name");
    }


    /**
     * class to string conversion returns encoded/serialized object
     *
     * @error 17032
     * @return string
     */
    public function __toString()
    {
        return self::encode($this->_object);
    }


    /**
     * when cloning object reset instance properties
     *
     * @error 17033
     * @return void
     */
    public function __clone()
    {
        $this->_result = null;
        $this->_path = null;
    }


    /**
     * on class constructor clear cache
     *
     * @error 17034
     */
    public function __destruct()
    {
        @clearstatcache();
    }
}