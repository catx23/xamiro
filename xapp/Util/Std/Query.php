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
 * Util std query class
 *
 * @package Util
 * @subpackage Util_Std
 * @class Xapp_Util_Std_Query
 * @error 167
 * @author Frank Mueller
 */
class Xapp_Util_Std_Query implements Iterator
{
    /**
     * defines whether search fails return a boolean false value or throw a result exception
     *
     * @const THROW_EXCEPTION
     */
    const THROW_EXCEPTION       = 'THROW_EXCEPTION';


    /**
     * internal iteration position pointer
     *
     * @var int
     */
    private $position = 0;

    /**
     * contains the std object to be queried when class is used with instance
     *
     * @var null|object
     */
    protected $_object = null;

    /**
     * contains the search result when class is used with instance
     *
     * @var null|object|array
     */
    protected $_result = null;

    /**
     * allowed filter operators when using search query
     *
     * @var array
     */
    private static $_operators = array('\!\%', '\%', '\!\-\>', '\-\>', '\!\<\>', '\<\>', '\*', '\=\=', '\!\=\=', '\!\=', '\>\=', '\<\=', '\=', '\>', '\<');

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::THROW_EXCEPTION   => XAPP_TYPE_BOOL
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::THROW_EXCEPTION   => 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public static $options = array
    (
        self::THROW_EXCEPTION   => false
    );

    /**
     * Getter
     *
     * @var null|object
     */
    public function  getObject(){
        return $this->_object;
    }

    /**
     * class constructor sets to be searched object and class options
     *
     * @error 16701
     * @param object|array $object expects the object to be searched
     * @param null|mixed $options expects optional options
     * @throws Xapp_Util_Std_Exception
     */
    public function __construct(&$object, $options = null)
    {
        $class = get_class();

        xapp_init_options($options, $class);
        if(is_array($object) || is_object($object))
        {
            $this->_object =& $object;
        }else{
            throw new Xapp_Util_Std_Exception(_("passed object in constructor is not an object or array"), 1670101);
        }
    }


    /**
     * static method to create instance of class
     *
     * @error 16702
     * @param object $object expects the object to be searched
     * @param null|mixed $options expects optional options
     * @return Xapp_Util_Std_Query
     */
    public static function create($object, $options = null)
    {
        return new self($object, $options);
    }


    /**
     * set/get static options for all instances of query class. setting options will overwrite all previous set options.
     * if first argument is null will return all options
     *
     * @error 16703
     * @param null|mixed $options expects optional options as array or object
     * @return array|mixed|null
     */
    public static function options($options = null)
    {
        $class = get_class();

        if($options !== null)
        {
            return xapp_set_options($options, $class);
        }else{
            return xapp_get_options($class);
        }
    }


    /**
     * query/search an object by path and optional query parameters. the path can be empty or path to target any
     * depth of the object by delimiter value e.g. root.object. the query parameter can contains a query or filter
     * chain with valid query filters e.g. key=1, title!=test. the query method returns not a result but the class
     * instance so the result can further be manipulated using the query()->get(), query()->first(), query->last()
     * methods. the use of this method in context of not using the static query functions is the prefered way when
     * performance is not an issue
     *
     * @error 16704
     * @see Xapp_Util_Std_Query::find
     * @param string $path expects the entry path or empty value
     * @param array $query expects optional query filter chain
     * @return $this
     */
    public function query($path, Array $query = null)
    {
        $this->_result = self::find($this->_object, $path, $query);
        return $this;
    }


    /**
     * get the real query result after using query method which can be null if not used before query method. use like
     * query()->get();
     *
     * @error 16705
     * @return array|null|object
     */
    public function get()
    {
        return $this->_result;
    }


    /**
     * get the first object of query result regardless if result has one or multiple objects. throws exception
     * if called without having used the query method first. use like query()->first()
     *
     * @error 16706
     * @return array|null|object
     * @throws Xapp_Util_Std_Exception
     */
    public function first()
    {
        if($this->_result !== null)
        {
            return (is_array($this->_result)) ? $this->_result[0] : $this->_result;
        }else{
            throw new Xapp_Util_Std_Exception(_("method: first() used in wrong context"), 1670601);
        }
    }


    /**
     * get the last object of query result regardless if result has one or multiple objects. throws exception
     * if called without having used the query method first. use like query()->last()
     *
     * @error 16707
     * @return array|null|object
     * @throws Xapp_Util_Std_Exception
     */
    public function last()
    {
        if($this->_result !== null)
        {
            return (is_array($this->_result)) ? $this->_result[sizeof($this->_result) -1] : $this->_result;
        }else{
            throw new Xapp_Util_Std_Exception(_("method: last() used in wrong context"), 1670701);
        }
    }


    /**
     * get an object at index defined in parameter one. throws exception if the method is called without
     * having the used the query method first. use like query()->at(0). will return false or throw result
     * exception, according to static options if the result is not an array an index does not exist
     *
     * @error 16708
     * @param int $index expects the index to return object for
     * @return bool|mixed
     * @throws Xapp_Result_Exception
     * @throws Xapp_Util_Std_Exception
     */
    public function at($index = 0)
    {
        $class = get_class();

        if($this->_result !== null)
        {
            if(is_array($this->_result) && array_key_exists((int)$index, $this->_result))
            {
                return $this->_result[(int)$index];
            }else{
                if(xapp_get_option(self::THROW_EXCEPTION, $class))
                {
                    throw new Xapp_Result_Exception(_("no object found at index"), 1670801);
                }else{
                    return false;
                }
            }
        }else{
            throw new Xapp_Util_Std_Exception(_("method: at() used in wrong context"), 1670801);
        }
    }


    /**
     * implemented from iterator interface returns object or array at current index
     *
     * @error 16709
     * @return object|array
     */
    public function current()
    {
        return $this->_result[$this->position];
    }


    /**
     * implemented from iterator interface returns the current key or index
     *
     * @error 16710
     * @return int|mixed
     */
    public function key()
    {
        return $this->position;
    }


    /**
     * implemented from iterator interface moves the iterator position one up
     *
     * @error 16711
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }


    /**
     * implemented from iterator interface rewinds the iterator position to initial state
     *
     * @error 16712
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }


    /**
     * implemented from iterator interface checks if the current iterator position is valid
     *
     * @error 16713
     * @return bool
     */
    public function valid()
    {
        return isset($this->_result[$this->position]);
    }


    /**
     * static method to retrieve objects by path for object passed in first parameter. the second parameter
     * path can be an empty value or string path to retrieve objects in multidimensional object hierarchy defined
     * be delimited path string like root.0.key. a default return value can be passed in third argument which will
     * be returned if the static class option THROW_EXCEPTION is set to false - otherwise will throw result exception
     *
     * @error 16714
     * @param object $object expects the object to be searched
     * @param string $path expects search path or empty value
     * @param bool $default expects default return value
     * @param null|mixed $parent stores optional parent element as reference
     * @return bool|mixed
     * @throws Xapp_Result_Exception
     */
    public static function &retrieve(&$object, $path, $default = false, &$parent = null)
    {
        $class = get_class();
        $path = trim((string)$path, "/. ");

        if(empty($path))
        {
            return $object;
        }else{
            foreach(explode('/', $path) as $p)
            {
                $parent = $object;
                if(is_numeric($p) && (intval($p) == floatval($p)))
                {
                    if(!is_array($object) || !array_key_exists($p, $object))
                    {
                        if($default === false && xapp_get_option(self::THROW_EXCEPTION, $class))
                        {
                            throw new Xapp_Result_Exception(_("no result found for path"), 1671401);
                        }else{
                            return $default;
                        }
                    }else{
                        $object = &$object[$p];
                    }
                }else{
                    if(!isset($object->$p))
                    {
                        if($default === false && xapp_get_option(self::THROW_EXCEPTION, $class))
                        {
                            throw new Xapp_Result_Exception(_("no result found for path"), 1671401);
                        }else{
                            return $default;
                        }
                    }else{
                        $object = &$object->{$p};
                    }
                }
            }
            return $object;
        }
    }


    /**
     * search and find objects by path and optional query parameters. the first parameter expects the object
     * to be searched. the second parameter a search path with delimiter style like "root.0" or "." or "" to search
     * from root element. the third parameter expects an array with query filter chain elements, each value a query
     * filter with {key}{operator}{value} syntax e.g. key=1. the following operators are supported:
     *
     * !%   = not like (expecting word to match)
     * %    = like (expecting word to match)
     * !->  = not in (expecting comma separated value list)
     * ->   = in (expecting comma separated value list)
     * !<>  = not between (expecting two values, min and max comma separated)
     * <>   = between (expecting two values, min and max comma separated)
     * *    = any value that is not null or empty string
     * ==   = equal and of same data type
     * !==  = not equal and of same data type
     * !=   = not equal
     * >=   = greater than equal
     * <=   = lesser than equal
     * =    = equal
     * >    = greater
     * <    = lesser
     *
     * regex patterns are also allowed and need to be passed like key=/pattern/
     * reference to other keys and their value are also allowed and need to passed like key={otherkey}
     *
     * the fourth optional parameter when set will return either the first or last object if passed as "last" or
     * "first" or will return the result untouched resulting in either array or object to be returned. the result is
     * return as reference and can be manipulated outside this method which changes will be reflected in the passed object.
     * that way this function is not only a search function but also a means to change object values. will either
     * return boolean false or throw result exception if static option THROW_EXCEPTION is set to true.
     *
     * @error 16715
     * @param object $object expects the object to search
     * @param string $path expects the optional search path as outlined above
     * @param array $query expects the optional query filter chain as explained above
     * @param null|string $result_flag expects optional result flag "first" or "last"
     * @param null|mixed $parent stores optional parent element as reference
     * @return array|bool|mixed
     * @throws Xapp_Result_Exception
     */
    public static function &find($object, $path, Array $query = null, $result_flag = null, &$parent = null)
    {
        $class = get_class();
        $return = false;

        //xapp_dumpObject($query,'std query');

        if($result_flag !== null)
        {
            $result_flag = strtolower(trim((string)$result_flag));
        }
        if($query === null)
        {
            return self::retrieve($object, $path);
        }else{
            if(($object =& self::retrieve($object, $path)) !== false)
            {
                $result = array();
                if(sizeof($query) > 1)
                {
                    foreach($query as $q)
                    {
                        $parent = $object;
                        $result = self::execute($object, $q);
                        if(!empty($result))
                        {
                            $object =& $result;
                        }else{
                            if(xapp_get_option(self::THROW_EXCEPTION, $class))
                            {
                                throw new Xapp_Result_Exception("no result found for query", 1671501);
                            }else{
                                return $return;
                            }
                        }
                    }
                    if($result_flag === 'first')
                    {
                        return $result[0];
                    }else if($result_flag === 'last'){
                        return $result[sizeof($result) - 1];
                    }else{
                        return $result;
                    }
                }else{
                    $result = array();
                    self::execute($object, $query[0], $result);
                    if(!empty($result))
                    {
                        if($result_flag === 'first')
                        {
                            return $result[0];
                        }else if($result_flag === 'last'){
                            return $result[sizeof($result) - 1];
                        }else{
                            return $result;
                        }
                    }else{
                        if(xapp_get_option(self::THROW_EXCEPTION, $class))
                        {
                            throw new Xapp_Result_Exception(_("no result found for query"), 1671501);
                        }else{
                            return $return;
                        }
                    }
                }
            }
        }
        if(xapp_get_option(self::THROW_EXCEPTION, $class))
        {
            throw new Xapp_Result_Exception(_("no result found for query"), 1671501);
        }else{
            return $return;
        }
    }


    /**
     * internal function that will do the magic and recursively search objects that match the search queries
     *
     * @error 16716
     * @param mixed $object expects object and array for iteration
     * @param string $query expects the query filter string to match
     * @param array $result expects optional result reference
     * @return array
     */
    final private static function execute(&$object, $query, &$result = array())
    {
        foreach($object as $k => &$v)
        {
            if(is_object($v) || is_array($v))
            {
                self::execute($v, $query, $result);
            }else{
                if(self::assert($k, $v, $query, $object))
                {
                    $result[] = &$object;
                }
            }
        }
        return $result;
    }


    /**
     * assert object items value again single query filter matching against all supported operators. the function
     * will typify all query filter values to native php types for proper and data type correct assertion
     *
     * @error 16717
     * @param string $key expects the object item key
     * @param mixed $value expects the object item value
     * @param string $query expects the query filter entry to match
     * @param null|object $object expects optional object for reference purpose
     * @return bool
     */
    protected static function assert($key, $value, $query, &$object = null)
    {
        //error_reporting(E_ERROR);
        //ini_set('display_errors', 0);
        if(preg_match("/^($key)(".implode('|', self::$_operators).")(.*)$/i", $query, $m))
        {
            if(sizeof($m) >= 3)
            {
                if(array_key_exists(3, $m))
                {
                    $m[3] = self::typify(trim($m[3]));
                }else{
                    $m[3] = null;
                }
                switch((string)$m[2])
                {
                    case '=':
                        if($m[3] === '*')
                        {
                            return (!is_null($value) && $value !== '') ? true : false;
                        }else if(substr_count($m[3], '/') === 2){
                            return (preg_match($m[3], $value)) ? true : false;
                        }else if(substr_count($m[3], '{') === 1 && substr_count($m[3], '}') === 1){
                            return (!is_null($object) && isset($object->{trim($m[3], ' {}')}) && $value == $object->{trim($m[3], ' {}')}) ? true : false;
                        }else{
                            return ($value == $m[3]) ? true : false;
                        }
                    case '==':
                        return ($value === $m[3]) ? true : false;
                    case '!=':
                        return ($value != $m[3]) ? true : false;
                    case '!==':
                        return ($value !== $m[3]) ? true : false;
                    case '>':
                        return ($value > $m[3]) ? true : false;
                    case '>=':
                        return ($value >= $m[3]) ? true : false;
                    case '<':
                        return ($value < $m[3]) ? true : false;
                    case '<=':
                        return ($value <= $m[3]) ? true : false;
                    case '%':
                        return (stripos($value, $m[3]) !== false) ? true : false;
                    case '!%':
                        return (stripos($value, $m[3]) === false) ? true : false;
                    case '<>':
                        $m[3] = explode(',', trim($m[3], ' ,'));
                        if(sizeof($m[3]) >= 2)
                        {
                            return (is_numeric($value) && in_array($value, range((int)$m[3][0], (int)$m[3][1]))) ? true : false;
                        }else{
                            return false;
                        }
                    case '!<>':
                        $m[3] = explode(',', trim($m[3], ' ,'));
                        if(sizeof($m[3]) >= 2)
                        {
                            return (is_numeric($value) && !in_array($value, range((int)$m[3][0], (int)$m[3][1]))) ? true : false;
                        }else{
                            return false;
                        }
                    case '*':
                        return true;
                    case '->':
                        return (in_array($value, explode(',', trim($m[3], ' ,')))) ? true : false;
                    case '!->':
                        return (!in_array($value, explode(',', trim($m[3], ' ,')))) ? true : false;
                    default:
                }
            }
        }
        return false;
    }


    /**
     * typify a string value to its expected native php data type
     *
     * @error 16718
     * @param string $value expects the value to typify
     * @return bool|float|int|null|string
     */
    final private static function typify($value)
    {
        if(is_numeric($value))
        {
            if((intval($value) == floatval($value)))
            {
                return (int)$value;
            }else{
                return (float)$value;
            }
        }else{
            if($value === 'true' || $value === 'TRUE')
            {
                return true;
            }else if($value === 'false' || $value === 'false'){
                return false;
            }else if($value === 'null' || $value === 'NULL'){
                return null;
            }else{
                return strval($value);
            }
        }
    }
}