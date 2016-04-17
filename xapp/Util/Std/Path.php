<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../core/core.php');

xapp_import('xapp.Util.Std.Exception');
xapp_import('xapp.Util.Std.Query');

/**
 * Util std path class
 *
 * perform xpath like string path queries on std objects. see Xapp_Util_Std_Query for query syntax. instead of using
 * Xapp_Util_Std_Query for object querying the path syntax allows a more versatile syntax to query objects, manipulate
 * results as part of the path syntax and account for default return values. path queries also allows for using only one
 * method $path->query('path') to query objects instead of Xapp_Util_Std_Query´s more complex public interfaces
 *
 * path syntax:
 *
 * 1) connectors
 * <code>$path->query($path1 $connector1 $path2');</code>
 * <code>$path->query('/object/key/1 ? false');</code>
 *
 * the most powerful features of the path class is to use connectors to connect multiple queries and manipulate results.
 * the path connector must be a single connector value in between the path commands. the following connector are supported:
 *
 * "?"
 * <code>$path->query('/object/key/1 ? /object/key/2 ? false');</code>
 * if/else(if) conditional path statements allow for building conditional chains that will execute each path command and
 * return the first in the chain to return the expected value. the example above will try to execute the first 2 path
 * queries and return "false" if both paths can not be resolved
 *
 * ">"
 * <code>$path->query('/object > /key > /1');</code>
 * piping results to filter down into the targets object tree. the above statement does the same as calling the path like
 * "/object/key/1" but allows for more complex queries with less probability of a malformed path
 *
 * "+"
 * <code>$path->query('/object/key/1 + /object/key/2');</code>
 * union connector will return a union of the results of each path query treating each result as array and appending the
 * right handed result to the left handed result with php´s "+" array operator. doing so each path query part result is
 * converted to an array and after the union again converted to an object
 *
 * "&"
 * <code>$path->query('/object/key/1 & /object/key/2');</code>
 * merge connector will, unlike the union connector, merge the results of the path query parts overwriting the left handed
 * result with the right handed result using php´s array_merge() function. doing so each path query part result is
 * converted to an array and after the union again converted to an object
 *
 * "|", "*"
 * <code>$path->query('/object/key/1 | /object/key/2');</code>
 * add or any connector will return an array with all path query part results treated as separate path queries. this way
 * multiple independent queries can be execute with one path statement instead of using the query() function multiple
 * times
 *
 * 2) predicates
 * <code>$path->query('/object[$predicate]');</code>
 * <code>$path->query('/object[name=test]');</code>
 *
 * predicate syntax borrowed from xpath to filter values in path. predicated must be embedded in square brackets just
 * like xpath syntax. there are two type of predicates supported: 1) sub queries or filter, 2) selectors:
 * - sub queries/filters:
 * <code>$path->query('/object[$key $operator $value]');</code>
 * <code>$path->query('/object[price>9.99]');</code>
 * inside the object found at path a sub query will return all matching elements according to filter type/operator and
 * filter value. e.g. the above would return all elements that has a key with the name price and a value geather than 9.99.
 * see Xapp_Util_Std_Query for all supported filter operators
 *
 * - selectors:
 * <code>$path->query('/object[$selector]');</code>
 * <code>$path->query('/object[1+2]');</code>
 * inside the object found at path use the selector syntax to return array elements by single index, ranges or functions.
 * e.g. the above would return all 2 array elements starting from index 1. see Xapp_Util_Std_Query for all supported
 * selectors
 *
 * 3) default values/actions
 * <code>$path->query($path1 ? $default');</code>
 * <code>$path->query(/object/key/1 ? false');</code>
 *
 * in connection with path connectors, or even as default path command, default values and actions can help to cover the
 * most common if/else and default return value scenarios. the above example simply will return boolean false if the first
 * path query fails. the following default/values are supported
 *
 * "null", "NULL"
 * <code>$path->query(/object/key/1 ? null');</code>
 * as string will resolve to php´ NULL if class option NORMALIZE_DEFAULT_VALUES is true
 *
 * "true", "TRUE"
 * <code>$path->query(/object/key/1 ? true');</code>
 * as string will resolve to php boolean true if class option NORMALIZE_DEFAULT_VALUES is true
 *
 * "false", "FALSE"
 * <code>$path->query(/object/key/1 ? false');</code>
 * as string will resolve to php boolean false if class option NORMALIZE_DEFAULT_VALUES is true
 *
 * "callback"
 * <code>$path->query(/object/key/1 ? class::method|1,test');</code>
 * return result of a callback as default return value. callbacks are supported and invokable if class option ALLOW_CALLBACKS
 * is enabled. callbacks can either by a function or class::method, have no arguments or arguments separated by "|" and
 * multiple arguments again delimited by ",". see class option ALLOW_CALLBACKS for more
 *
 * the default behaviour is to return any value that is not a resolvable query path as string!
 * the return value of "$path->query()" can be returned as reference so any changes will also be reflected in the path
 * instances object
 *
 * @package Util
 * @subpackage Util_Std
 * @class Xapp_Util_Std_Path
 * @see Xapp_Util_Std_Query
 * @error 171
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Util_Std_Path
{
    /**
     * defines the enclosure string for callback parameters that will be used to parse the parameter chain with php´s
     * native str_getcsv() function. defaults to single quote
     *
     * @const PARAMETER_ENCLOSURE
     */
    const PARAMETER_ENCLOSURE               = 'UTIL_STD_PATH_PARAMETER_ENCLOSURE';

    /**
     * allow callbacks in path query. by default disabled = boolean false for security reasons. to activate and allow
     * callbacks either set to boolean true or pass array with allowed callbacks (functions names or class::method names
     * as strings). disabling callbacks or calling not allowed callbacks will throw exception
     *
     * @const ALLOW_CALLBACKS
     */
    const ALLOW_CALLBACKS                   = 'UTIL_STD_PATH_ALLOW_CALLBACKS';

    /**
     * defines whether default values like "null", "NULL", "false", "FALSE", "true", "TRUE", etc. will be normalized =
     * converted to its php native data type or not
     *
     * @const NORMALIZE_DEFAULT_VALUES
     */
    const NORMALIZE_DEFAULT_VALUES          = 'NORMALIZE_DEFAULT_VALUES';


    /**
     * contains the std store or query instance as object target for path query
     *
     * @var null|Xapp_Util_Std_Query|Xapp_Util_Std_Store
     */
    public $object = null;


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::PARAMETER_ENCLOSURE           => XAPP_TYPE_STRING,
        self::ALLOW_CALLBACKS               => array(XAPP_TYPE_BOOL, XAPP_TYPE_ARRAY),
        self::NORMALIZE_DEFAULT_VALUES      => XAPP_TYPE_BOOL
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::PARAMETER_ENCLOSURE           => 1,
        self::ALLOW_CALLBACKS               => 0,
        self::NORMALIZE_DEFAULT_VALUES      => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public static $options = array
    (
        self::PARAMETER_ENCLOSURE           => '\'',
        self::ALLOW_CALLBACKS               => false,
        self::NORMALIZE_DEFAULT_VALUES      => true
    );



    /**
     * class constructor sets object as path target which can either by any object or array or instance of
     * Xapp_Util_Std_Store or Xapp_Util_Std_Query
     *
     * @error 17101
     * @param array|object|Xapp_Util_Std_Store|Xapp_Util_Std_Query $object expects object
     * @param null|mixed $options expects optional options
     * @throws Xapp_Util_Std_Exception
     */
    public function __construct($object, $options = null)
    {
        $class = get_class();

        xapp_init_options($options, $class);

        if($object instanceof Xapp_Util_Std_Store || $object instanceof Xapp_Util_Std_Query)
        {
            $this->object = $object;
        }else if(is_object($object) || is_array($object)){
            $this->object = new Xapp_Util_Std_Query($object);
        }else{
            throw new Xapp_Util_Std_Exception(_("first argument is not a valid object"), 1710101);
        }
    }


    /**
     * static method to create instance of class
     *
     * @error 17102
     * @see Xapp_Util_Std_Path::__construct
     * @param array|object|Xapp_Util_Std_Store|Xapp_Util_Std_Query $object
     * @param null|mixed $options expects optional options
     * @return Xapp_Util_Std_Path
     */
    public static function create($object, $options = null)
    {
        return new self($object, $options);
    }


    /**
     * set/get static options for all instances of class. setting options will overwrite all previous set options. if first
     * argument is null will return all options
     *
     * @error 17103
     * @param null|mixed $options expects options to set
     * @return array|mixed
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
     * set/get the class instance target object
     *
     * @error 17108
     * @see Xapp_Util_Std_Query::object
     * @see Xapp_Util_Std_Store::object
     * @param null|mixed $object
     * @return mixed
     */
    public function &object($object = null)
    {
        return $this->object->object($object);
    }


    /**
     * perform the path query passing the path command/syntax to first argument. see more about the path syntax refer
     * to execute() and connect() functions. the query function returns a reference to the original object if used with
     * reference identifier "&" in function: <code>$result = &path->query('/path');</code>. changes in the returned result
     * object will also change the value in the original object. if the path query fails will return either boolean false
     * or throw an exception according to preferences in the target object class or optional second argument
     *
     * @error 17104
     * @param string $path expects the query path
     * @param bool|mixed $default expects optional default return value which default to null throwing exception or boolean false
     * @return mixed
     * @throws Exception
     * @throws Xapp_Result_Exception
     */
    public function &query($path, $default = null)
    {
        $e = null;
        $last = false;
        $error = false;
        $result = null;
        $return = false;
        $connector = null;

        $path = rawurldecode(trim((string)$path));
        $paths = preg_split('=\s+(\+|\?|\>|\&|\||\*)\s+=is', $path, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        for($i = 0; $i < sizeof($paths); $i += 2)
        {
            $path = trim($paths[$i]);
            try
            {
                //remember the connector
                if(array_key_exists(($i + 1), $paths))
                {
                    $connector = strtolower(trim($paths[($i + 1)]));
                }else{
                    $last = true;
                }
                //perform the query
                if($this->execute($result, $path))
                {
                    //get result and return action
                    $result = &$this->connect($result, $connector, $last, $return);
                    if($return === true)
                    {
                        return $result;
                    }
                }
            }
            catch(Xapp_Result_Exception $e)
            {
                $result = false;
            }
            $return = false;
        }
        if($result !== false)
        {
            return $result;
        }else{
            if(!is_null($default))
            {
                $default = xapp_default($default);
                return $default;
            }else{
                if(($e !== null && $e instanceof Exception) && stripos($path, 'false') === false)
                {
                    throw $e;
                }else{
                    return $error;
                }
            }
        }
    }


    /**
     * static query function. see Xapp_Util_Std_Path::query for more
     *
     * @error 17105
     * @see Xapp_Util_Std_Path::query
     * @param array|object|Xapp_Util_Std_Store|Xapp_Util_Std_Query $object
     * @param string $path expects the query path
     * @param null|mixed $default expects optional default return value
     * @param null|mixed $options expects optional options
     * @return mixed
     */
    public static function &q(&$object, $path, $default = null, $options = null)
    {
        return self::create($object, $options)->query($path, $default);
    }


    /**
     * executes a path query part on the object passed in constructor. see class description for syntax. returns a boolean
     * value on whether a path query result is expected or a default value is returned
     *
     * @error 17106
     * @param mixed $object expects the result object as reference
     * @param string $path expects the path query part
     * @return bool
     * @throws Xapp_Util_Std_Exception
     */
    final protected function execute(&$object, $path)
    {
        $class = get_class();

        switch($path)
        {
            //return null as default
            case ($path === 'null' || $path === 'NULL')
            :
                if(xapp_get_option(self::NORMALIZE_DEFAULT_VALUES, $class))
                {
                    $object = null;
                }
                $object = null;
                break;
            //return boolean false as default
            case ($path === 'false' || $path === 'FALSE')
            :
                if(xapp_get_option(self::NORMALIZE_DEFAULT_VALUES, $class))
                {
                    $object = false;
                }
                break;
            //return boolean true as default
            case ($path === 'true' || $path === 'TRUE')
            :
                if(xapp_get_option(self::NORMALIZE_DEFAULT_VALUES, $class))
                {
                    $object = true;
                }
                break;
            //return result from callback as default
            case ((bool)preg_match('=^(([a-z0-9\_]+)(?:(?:\:\:)([a-z0-9\_]+)))?(?:\|(.+))?$=is', $path, $m))
            :
                if(($callbacks = xapp_get_option(self::ALLOW_CALLBACKS, $class)) !== false)
                {
                    if(is_array($callbacks) && !in_array(trim($m[1]), $callbacks))
                    {
                        throw new Xapp_Util_Std_Exception(xapp_sprintf(_("callback: %s is not a allowed callback"), $m[1]), 1710601);
                    }
                    if(array_key_exists(3, $m) && !empty($m[3]))
                    {
                        $callback = array(trim($m[2]), trim($m[3]));
                    }else{
                        $callback = trim($m[2]);
                    }
                    if(array_key_exists(4, $m))
                    {
                        $params = str_getcsv(trim($m[4], xapp_get_option(self::PARAMETER_ENCLOSURE, $class) . ' '), ',', xapp_get_option(self::PARAMETER_ENCLOSURE, $class));
                        array_walk($params, function(&$v){ $v = Xapp_Util_Std_Query::typify(stripslashes($v)); });
                    }else{
                        $params = array();
                    }
                    if(is_callable($callback))
                    {
                        if(is_string($callback))
                        {
                            $object = call_user_func_array($callback, $params);
                        }else{
                            try
                            {
                                $class = new ReflectionClass($callback[0]);
                                $method = $class->getMethod($callback[1]);
                                if($method->isPublic())
                                {
                                    if($method->isStatic())
                                    {
                                        $object = call_user_func_array($callback, $params);
                                    }else{
                                        $object = call_user_func_array(array($class->newInstance(), $callback[1]), $params);
                                    }
                                }
                            }
                            catch(ReflectionException $e)
                            {
                                throw new Xapp_Util_Std_Exception(xapp_sprintf(_("unable to call callback: %s due to reflection error: %s"), $m[1], $e->getMessage()), 1710602);
                            }
                        }
                        break;
                    }
                }
            //return string or any other value that is not a path that can be queried
            case (!preg_match('/(^\s*\/+(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$))/is', $path))
            :
                if(xapp_get_option(self::NORMALIZE_DEFAULT_VALUES, $class))
                {
                    $object = Xapp_Util_Std_Query::typify($path);
                }
                break;
            //default - return the query result of path
            default
            :
                //path has most likely predicate(s)
                if(stripos($path, '[') !== false && stripos($path, ']') !== false)
                {
                    $p = null;
                    $path = preg_split('/(\[[^\]]+\])/is', rtrim($path, './ '), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                    for($j = 0; $j < sizeof($path); $j += 2)
                    {
                        $query  = null;
                        $filter = null;

                        if(array_key_exists(($j + 1), $path))
                        {
                            $p = trim($path[($j + 1)], '[] ');
                            //assume filter part
                            if(Xapp_Util_Std_Query::isFilter($p) !== false){
                                $filter = $p;
                            //assume query part
                            }else{
                                $query = $p;
                            }
                        }
                        $this->object->query($path[$j], $query, $filter);
                    }
                }else{
                    $this->object->query($path);
                }
                return true;
        }
        return false;
    }


    /**
     * connects multiple path query paths according to the connector string - see class description for path/connector
     * syntax. returns the object after connection execution
     *
     * @error 17107
     * @param mixed $object expects the result object as reference
     * @param string $connector expects the connector
     * @param boolean $last expects boolean flag for last connector in chain
     * @param bool $return expects reference var for return flag
     * @return mixed
     */
    final protected function &connect(&$object, $connector, $last = false, &$return = false)
    {
        $connector = strtolower(trim((string)$connector));

        switch($connector)
        {
            //else/if connector (/store/book/10 ? /store/book/1)
            case '?'
            :
                $object = &$this->object->get();
                if($object !== false)
                {
                    $return = true;
                }
                break;
            //pipe connector (/store > /book > /1)
            case '>'
            :
                if($last)
                {
                    $object = &$this->object->get();
                }
                break;
            //union connector (/store/book/1 + /store/book/2)
            case '+'
            :
                $object = xapp_array_to_object((array)$object + (array)$this->object->get());
                break;
            //merge connector (/store/book/1 & /store/book/2)
            case '&'
            :
                $object = xapp_array_to_object(array_merge((array)$object, (array)$this->object->get()));
                break;
            //add or any connector (/store/book/1 | /store/book/2)
            case ('|' || '*')
            :
                if($object === null)
                {
                    $object = array();
                }
                $object[] = &$this->object->get();
                break;
            //default connector which is "" empty value
            default
            :
                $object = &$this->object->get();
        }

        return $object;
    }
}