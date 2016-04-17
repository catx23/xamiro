<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../../core/core.php');

xapp_import('xapp.Util.Json.Exception');
xapp_import('xapp.Util.Json.Query');
xapp_import('xapp.Util.Json');

/**
 * Util json mapper class
 *
 * this class maps values from a target object to a source object by placeholders and a mapping command syntax to do so
 * the placeholder is by default a common string placeholder container:
 * <code>${command}</code>
 * the placeholder can be overwritten with a valid regex pattern - see const PLACEHOLDER_REGEX for more. the mapper can
 * map multiple placeholders in a string. like:
 * <code>this is a ${command1} string with ${command2} placeholders</code>
 *
 * mapping syntax:
 *
 * 1) query by path
 * <code>${ns1:/path/to/result}</code>
 *
 * query the target object added with namespace "ns1" - see Xapp_Util_Json_Path for full path syntax
 *
 * 2) use internal function and custom callback pipes
 * <code>${#class::test|$1,1,$2;trim|$1;strtoupper(ns0:/store/book/0/author; ns0:/store/book/1/author)}</code>
 *
 * to use a callback use the basic syntax "#callback()". a callback pipe must always begin with "#" and must enclose
 * the path query in "()" enclosure.
 *
 * NOTE: this example does query multiple path separated by ";" delimiter that is available as "$1", "$2" placeholders for
 * callback parameter assignment.
 *
 * to use callback pipes enclose the path query in "()" brackets and stack your callbacks before the "()" enclosure. the pipe
 * is executed from left to right >. 1-n callbacks can be stacked in the pipe and need to be separated with ";" semicolon
 * delimiter. class methods must always be written like "class::method". the "|" separator separates the callback from option
 * parameters. by default no parameter must be defined since the path query result will be passed by default to callbacks
 * first argument. to use additional static parameters or pipe the path query result to another argument position for the
 * callback use the "$x" placeholder where "$1" refers to first result of first path query, "$2" to the second and so on.
 * "trim" and "trim|$1" will both do the same - pipe the first result to first argument of trim function. to mix multiple
 * static and dynamic parameters, e.g. "class::test|$1,1,$2", you must use the "," comma as delimiter. in this example
 * you must take care that the result from the first callback "class::test" returns a string since the next callback in
 * the pipe expects a string as input! wrong use of callback piping will lead to errors! use "'" single quote enclosures
 * to pass strings safe into the callback: "class::test|$1,'my string with , comma',$2"
 *
 * 3) use actions for extended mapping manipulation
 * <code>${@concat(',', ns0:/store/book/0/author, ns0:/store/book/1/author)}</code>
 *
 * to use actions use the basic action syntax "@action()". an action must always begin with "@" and must enclose the
 * path query or parameters in "()" enclosure.
 *
 * NOTE: actions can not be stacked! there can only be one single action! "@action1;action2()" is not a valid syntax!
 *
 * please refer to internal actions in the Xapp_Util_Json_Mapper::action method. if you use custom action registered
 * with the mapper instance you will access the custom action also like "@youraction()". actions can be used together with
 * path query syntax e.g. for conditional queries like, e.g. "${ns1:/path ? @remove(1)}". see Xapp_Util_Json_Mapper::action
 * for all actions and descriptions
 *
 * 4) special action for mapping from source to source
 * <code>${@:/path/to/internal/key}</code>
 *
 * instead of performing a path query on the target object, this syntax will perform a path query on the source object
 * itself. that way you can copy values inside the source object map. the "@" in this case stands for internal query on
 * source object/map
 *
 * @package Util
 * @subpackage Util_Json
 * @class Xapp_Util_Json_Mapper
 * @error 172
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Util_Json_Mapper
{
    /**
     * contains the php regex pattern to identify the mapping placeholder in json strings which default to ${}. the
     * pattern can be overwritten via this options but must contain a valid tested php regex pattern for matching
     * placeholders
     *
     * @const PLACEHOLDER_REGEX
     */
    const PLACEHOLDER_REGEX                     = 'UTIL_JSON_MAPPER_PLACEHOLDER_REGEX';

    /**
     * default namespace for objects/values added as mapping source via public add() method. every object/value added
     * should have a namespace attached so multiple objects with different namespaces can be added and thus mapped. the
     * namespace should by a meaningful value of chars and numbers. the default namespace however defaults to null which
     * means that all objects/values passed will be added to the root object without ns
     *
     * @const DEFAULT_NAMESPACE
     */
    const DEFAULT_NAMESPACE                     = 'UTIL_JSON_MAPPER_DEFAULT_NAMESPACE';

    /**
     * mapping placeholder can be part of a string and not a single placeholder only. in the first scenario the mapping
     * results need to be injected/replaced in the mapping string which requires mapping return values to be string
     * representable values. this option will force every returned value to a string representable value or if disabled
     * will throw an exception
     *
     * @const FORCE_STRING
     */
    const FORCE_STRING                          = 'UTIL_JSON_MAPPER_FORCE_STRING';

    /**
     * if FORCE_STRING is boolean true and STRINGIFY_BOOL_TO_INT is boolean true will convert boolean true = 1 and
     * boolean false = 0. this option is by default set to false
     *
     * @const STRINGIFY_BOOL_TO_INT
     */
    const STRINGIFY_BOOL_TO_INT                 = 'UTIL_JSON_MAPPER_STRINGIFY_BOOL_TO_INT';

    /**
     * if FORCE_STRING is boolean true and STRINGIFY_OBJECT_TO_JSON is boolean true will encode arrays/objects to json
     * strings. this option is by default set to true
     *
     * @const STRINGIFY_OBJECT_TO_JSON
     */
    const STRINGIFY_OBJECT_TO_JSON              = 'UTIL_JSON_MAPPER_STRINGIFY_OBJECT_TO_JSON';

    /**
     * the mapping return values can be piped to function/callback for further manipulation. all callback must be
     * registered with mapper class instance. only registered callbacks, if ALLOW_ONLY_REGISTERED_CALLBACKS set to bool
     * true (default) will pass. this option contains a list of pre-registered internal functions for mapping result
     * manipulation which defaults to common php functions for string manipulation. the list can be extended to add
     * further default internal php functions (only functions!)
     *
     * @const INTERNAL_FUNCTIONS
     */
    const INTERNAL_FUNCTIONS                    = 'UTIL_JSON_MAPPER_INTERNAL_FUNCTIONS';

    /**
     * defines whether any callback/function or action = callback must be registered prior to use. if this option is set
     * to boolean false any valid callback available in the global scope can be called including all php functions! NOTE:
     * this value should only be set to boolean false considering all possible security risks!
     *
     * @const ALLOW_ONLY_REGISTERED_CALLBACKS
     */
    const ALLOW_ONLY_REGISTERED_CALLBACKS       = 'UTIL_JSON_MAPPER_ALLOW_ONLY_REGISTERED_CALLBACKS';

    /**
     * register a custom function to be thrown when using internal action "@throw()" and also will override xapp´s internal
     * Xapp_Result_Exception when executing the mapping
     *
     * @const CUSTOM_EXCEPTION
     */
    const CUSTOM_EXCEPTION                      = 'UTIL_JSON_MAPPER_CUSTOM_EXCEPTION';


    /**
     * contains the json path instance to query the map store
     *
     * @var null|Xapp_Util_Json_Path
     */
    public $path = null;

    /**
     * contains the json store that contains all objects/values as mapping target
     *
     * @var null|Xapp_Util_Json_Store
     */
    public $map = null;

    /**
     * contains all objects/values added to instance as mapping target
     *
     * @var array
     */
    protected $_objects = array();

    /**
     * contains all callbacks registered with instance as allowed callbacks
     *
     * @var array
     */
    protected $_callbacks = array();

    /**
     * contains array of allowed and supported internal actions - see action() for further explanations
     *
     * @var array
     */
    protected $_internalActions = array
    (
        'call',
        'callback',
        'remove',
        'throw',
        'null',
        'copy',
        'cut',
        'concat',
        'glue',
        'implode',
        'query',
        'map',
        'cast'
    );

    /**
     * contains optional added custom actions
     *
     * @var array
     */
    protected $_customActions = array();

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::PLACEHOLDER_REGEX                 => XAPP_TYPE_STRING,
        self::DEFAULT_NAMESPACE                 => XAPP_TYPE_STRING,
        self::FORCE_STRING                      => XAPP_TYPE_BOOL,
        self::STRINGIFY_BOOL_TO_INT             => XAPP_TYPE_BOOL,
        self::STRINGIFY_OBJECT_TO_JSON          => XAPP_TYPE_BOOL,
        self::INTERNAL_FUNCTIONS                => XAPP_TYPE_ARRAY,
        self::ALLOW_ONLY_REGISTERED_CALLBACKS   => XAPP_TYPE_BOOL,
        self::CUSTOM_EXCEPTION                  => array(null, 'Exception')
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::PLACEHOLDER_REGEX                 => 0,
        self::DEFAULT_NAMESPACE                 => 0,
        self::FORCE_STRING                      => 0,
        self::STRINGIFY_BOOL_TO_INT             => 0,
        self::INTERNAL_FUNCTIONS                => 0,
        self::ALLOW_ONLY_REGISTERED_CALLBACKS   => 0,
        self::CUSTOM_EXCEPTION                  => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public static $options = array
    (
        self::PLACEHOLDER_REGEX                 => '\$\{((?>[^\{\}]+)|(?R))+\}',
        self::DEFAULT_NAMESPACE                 => null,
        self::FORCE_STRING                      => true,
        self::STRINGIFY_BOOL_TO_INT             => false,
        self::STRINGIFY_OBJECT_TO_JSON          => true,
        self::ALLOW_ONLY_REGISTERED_CALLBACKS   => true,
        self::INTERNAL_FUNCTIONS                => array
        (
            '_',
            '__',
            'gettext',
            'ngettext',
            'trim',
            'strlen',
            'strrev',
            'strtok',
            'strtolower',
            'strtoupper',
            'addslashes',
            'addcslashes',
            'strip_tags',
            'stripslashes',
            'stripcslashes',
            'htmlentities',
            'htmlspecialchars',
            'html_entity_decode',
            'lcfirst',
            'ucfirst',
            'ucwords',
            'sprintf',
            'vsprintf',
            'md5',
            'sha1',
            'crc32',
            'crypt',
            'metaphone',
            'quotemeta',
            'soundex',
            'bin2hex',
            'str_rot13',
            'str_shuffle',
            'money_format',
            'number_format',
            'substr',
            'str_repeat',
            'str_replace',
            'str_ireplace',
            'substr_replace',
            'str_pad',
            'serialize',
            'json_encode',
            'preg_filter',
            'preg_replace',
            'preg_replace_callback',
            'mb_ereg_replace',
            'mb_strcut',
            'mb_strstr',
            'mb_substr',
            'mb_strtolower',
            'mb_ereg_replace_callback',
            'mb_eregi_replace',
            'mb_convert_case',
            'iconv_strlen',
            'iconv_substr',
            'iconv'
        )
    );


    /**
     * class constructor sets static options for all instances of mapper since there should only one allowed configuration
     * for all instances
     *
     * @error 17201
     * @param null|mixed $options expects optional options
     */
    public function __construct($options = null)
    {
        $class = get_class();
        xapp_init_options($options, $class);
    }


    /**
     * static method to create class instance with optional options as explained in class constructor
     *
     * @error 17202
     * @param null|mixed $options expects optional options
     * @return Xapp_Util_Json_Mapper
     */
    public static function create($options = null)
    {
        return new self($options);
    }


    /**
     * set/get static options for all instances of class. setting options will overwrite all previous set options. if first
     * argument is null will return all options
     *
     * @error 17229
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
     * add any object/array/value as mapping target to mapper instance by namespace which defaults to default namespace
     * which is null = no namespace. the optional namespace in second argument should contain a meaningful value and
     * consist of alphanumerical characters. the first argument can be of any meaningful data type and also accepts the
     * following:
     * - instance of Xapp_Util_Json_Store
     * - instance of Xapp_Config
     * - instance of ArrayObject
     * - path to (json) file
     *
     * every other value will pass as well but possibly will not be mappable! NOTE: adding twice to the same namespace
     * will mean merging of the values of that namespace!
     *
     * @error 17203
     * @param mixed $mixed expects value to add as explained above
     * @param null|string|int $ns expects optional namespace identifier
     * @return $this
     * @throws Xapp_Util_Json_Exception
     */
    public function add($mixed, $ns = null)
    {
        if($ns !== null)
        {
            if(is_string($ns))
            {
                $ns = trim($ns, ' :\/');
            }
        }else{
            $ns = xapp_get_option(self::DEFAULT_NAMESPACE, $this);
        }

        if($mixed instanceof Xapp_Util_Json_Store)
        {
            $mixed = $mixed->object();
        }else if($mixed instanceof Xapp_Config){
            $mixed = $mixed->get();
        }else if($mixed instanceof ArrayObject){
            $mixed = (array)$mixed;
        }else if(is_string($mixed) && xapp_is('file', $mixed)){
            if(($mixed = file_get_contents($file = $mixed)) === false)
            {
                throw new Xapp_Util_Json_Exception(xapp_sprintf(_("unable to add object from file: %s"), $file), 1720301);
            }
        }

        if(Xapp_Util_Json::isJson($mixed))
        {
            $mixed = Xapp_Util_Json::decode($mixed);
        }

        $this->_objects = (array)$this->_objects;

        if($ns !== '' && $ns !== null)
        {
            if(xapp_array_isset($this->_objects, $ns))
            {
                xapp_array_set($this->_objects, $ns, xapp_array_to_object(array_merge((array)xapp_array_get($this->_objects, $ns), (array)$mixed)));
            }else{
                xapp_array_set($this->_objects, $ns, xapp_array_to_object((array)$mixed));
            }
        }else{
            $this->_objects = xapp_array_to_object(array_merge($this->_objects, (array)$mixed));
        }

        return $this;
    }


    /**
     * get mapping target values or mapping value target by namespace if first argument is not null. if the namespace
     * is not registered will return default value in second argument
     *
     * @error 17204
     * @param null|string|int $ns
     * @param null|mixed $default
     * @return null|mixed
     */
    public function get($ns = null, $default = null)
    {
        if($ns !== null)
        {
            if(is_string($ns))
            {
                $ns = trim($ns, ' :\/');
            }
            if(array_key_exists($ns, $this->_objects))
            {
                return $this->_objects[$ns];
            }else{
                return xapp_default($default);
            }
        }else{
            return $this->_objects;
        }
    }


    /**
     * remove values at namespace passed in first argument
     *
     * @error 17205
     * @param string|int $ns
     * @return $this
     */
    public function remove($ns)
    {
        if(is_string($ns))
        {
            $ns = trim($ns, ' :\/');
        }
        if(array_key_exists($ns, $this->_objects))
        {
            unset($this->_objects[$ns]);
        }

        return $this;
    }


    /**
     * reset all added objects/values
     *
     * @error 17206
     * @return $this
     */
    public function reset()
    {
        $this->_objects = array();

        return $this;
    }


    /**
     * register a callback (php function or class/method(s)) as targets for mapping results. depending on class option
     * ALLOW_ONLY_REGISTERED_CALLBACKS only callbacks registered with this method will be allowed. the following values
     * can be passed to first argument:
     * 1) php function name as string
     * 2) php public static class method like class::method as string
     * 3) php class name as string
     * 4) php class instance as object
     * 5) array as class/method callback either with class name as string or object instance at index 0
     *
     * in case of 3) and 4) all public available methods of class will be added as callbacks with php´s function:
     * get_class_methods(). in order to exclude method names use the second argument to pass methods names or regex
     * expression as exclude list. calling this function for the same class and different methods will result in adding
     * those to the registered callback. NOTE: callbacks will not be validated when registering but only when invoked at
     * runtime. see mapping syntax on how to pipe mapping results to callbacks in query() method
     *
     * @error 17207
     * @param string|array|object $callback expects a valid callback value
     * @param null|array $exclude expects optional method exclude list
     * @return $this
     */
    public function registerCallback($callback, Array $exclude = null)
    {
        $key = 'class';
        $class = '';
        $methods = array();

        if(is_string($callback) && function_exists($callback))
        {
            $this->_callbacks["func:" . strtolower($callback)] = null;
        }else{
            //callback as array
            if(is_array($callback))
            {
                if(is_object($callback[0]))
                {
                    $class = get_class($callback[0]);
                }else{
                    $class = trim($callback[0]);
                }
                $methods[] = trim($callback[1]);
            //callback is class instance
            }else if(is_object($callback)){
                $class = get_class($callback);
                $methods = (array)get_class_methods($callback);
            //callback is class name
            }else if(is_string($callback) && class_exists($callback, true)){
                $class = $callback;
                $methods = (array)get_class_methods($callback);
            //callback is callback string with ::
            }else if(is_string($callback) && preg_match('=^(.*)(?:\:\:)(.*)$=i', $callback, $m) !== false){
                $class = $m[1];
                $methods[] = $m[2];
            }
            //apply ignore values or regex patterns
            if($exclude !== null)
            {
                foreach($methods as $k => &$m)
                {
                    if(preg_match('=^('.trim(implode('|', $exclude), ' ^').')=i', $m))
                    {
                        unset($methods[$k]);
                    }
                }
                $methods = array_values(array_filter($methods));
            }
            //register the callback
            if(class_exists($class, true))
            {
                if(!array_key_exists("$key:$class", $this->_callbacks))
                {
                    $this->_callbacks["$key:$class"] = (array)$methods;
                }else{
                    $this->_callbacks["$key:$class"] = (array)$this->_callbacks["$key:$class"] + $methods;
                }
            }
        }

        return $this;
    }


    /**
     * unregister callbacks by passing function name, class name or class::method name as single string value or array
     * with multiple values. if unregistering a class and all methods just pass the class name as string. if unregistering
     * only certain methods from class pass the class and method like "class::method"
     *
     * @error 17208
     * @param string|array $callbacks expects the function, class, class::method name
     * @return $this
     */
    public function unregisterCallback($callbacks)
    {
        foreach((array)$callbacks as $callback)
        {
            $callback = trim((string)$callback);
            if(stripos($callback, '::') !== false)
            {
                $class = substr($callback, 0, stripos($callback, '::'));
                $method = substr($callback, stripos($callback, '::') + 2);
                if(array_key_exists("class:$class", $this->_callbacks))
                {
                    if(($key = array_search(strtolower($method), $this->_callbacks["class:$class"])) !== false)
                    {
                        unset($this->_callbacks["class:$class"][$key]);
                    }
                }
            }else{
                if(array_key_exists('func:' . strtolower($callback), $this->_callbacks))
                {
                    unset($this->_callbacks['func:' . strtolower($callback)]);
                }
                if(array_key_exists('class:' . $callback, $this->_callbacks))
                {
                    unset($this->_callbacks["class:$callback"]);
                }
            }
        }

        return $this;
    }


    /**
     * register a custom action by passing the action name/hook as first argument and the associated callback as second
     * argument. the first argument must be a string. the second argument must be a valid callback that can be a function
     * name as string, "class::method" as string or array with class name as string, class instance and method name as
     * string - see php´s callable for more
     *
     * @error 17209
     * @param string $action expects the action name/hook
     * @param string|array $callback expects a valid callable callback
     * @return $this
     * @throws Xapp_Util_Json_Exception
     */
    public function registerAction($action, $callback)
    {
        if($this->isCallable($callback))
        {
            $this->_customActions[trim((string)$action)] = $callback;
        }else{
            throw new Xapp_Util_Json_Exception(xapp_sprintf(_("callback for custom action: %s is not a valid callback"), $action), 1720901);
        }

        return $this;
    }


    /**
     * unregister a custom action by custom action name/hook passed in first argument
     *
     * @error 17210
     * @param string $action expects the action name/hook
     * @return $this
     */
    public function unregisterAction($action)
    {
        $action = trim((string)$action);

        if(array_key_exists($action, $this->_customActions))
        {
            unset($this->_customActions[$action]);
        }

        return $this;
    }


    /**
     * get/set objects/values = mapping target. if first argument is not null will overwrite all added objects/values and
     * set the value passed in first argument without namespace. if the first argument is null will return array of added
     * objects/values
     *
     * @error 17211
     * @param null|mixed $objects expects optional object/value to overwrite existing mapping target objects/values
     * @return $this|array
     */
    public function objects($objects = null)
    {
        if($objects !== null)
        {
            $this->reset();
            return $this->add($objects, '');
        }else{
            return $this->_objects;
        }
    }


    /**
     * return all registered callbacks as array or dump all registered callbacks to screen if second argument is set
     * to boolean true
     *
     * @error 17212
     * @param bool $dump optional value to dump callbacks
     * @return array|null
     */
    public function callbacks($dump = false)
    {
        $txt = array();

        if((bool)$dump === false)
        {
            return $this->_callbacks;
        }else{
            foreach($this->_callbacks as $key => $val)
            {
                if(is_array($val))
                {
                    foreach($val as $v)
                    {
                        $txt[] = "$key::$v";
                    }
                }else{
                    $txt[] = $key;
                }
            }
            if(strtolower(php_sapi_name()) === 'cli')
            {
                echo implode("\n", $txt);
            }else{
                echo '<pre>' . implode("\n", $txt) . '</pre>';
            }
            $txt = null;
            return null;
        }
    }


    /**
     * return all registered actions (internal and custom!) as array or dump all registered actions to screen if second
     * argument is set to boolean true
     *
     * @error 17213
     * @param bool $dump optional value to dump actions
     * @return array|null
     */
    public function actions($dump = false)
    {
        $txt = array();

        if((bool)$dump === false)
        {
            return array_merge($this->_internalActions, array_keys($this->_customActions));
        }else{
            foreach(array_merge($this->_internalActions, array_keys($this->_customActions)) as $val)
            {
                $txt[] = $val;
            }
            if(strtolower(php_sapi_name()) === 'cli')
            {
                echo implode("\n", $txt);
            }else{
                echo '<pre>' . implode("\n", $txt) . '</pre>';
            }
            $txt = null;
            return null;
        }
    }


    /**
     * return all internal functions as array or dump all internal functions to screen if second argument is set to
     * boolean true
     *
     * @error 17214
     * @param bool $dump optional value to dump internal functions
     * @return array|null
     */
    public function functions($dump = false)
    {
        $class = get_class();

        if((bool)$dump === false)
        {
            return xapp_get_option(self::INTERNAL_FUNCTIONS, $class);
        }else{
            if(strtolower(php_sapi_name()) === 'cli')
            {
                echo implode("\n", xapp_get_option(self::INTERNAL_FUNCTIONS, $class));
            }else{
                echo '<pre>' . implode("\n", xapp_get_option(self::INTERNAL_FUNCTIONS, $class)) . '</pre>';
            }
            return null;
        }
    }


    /**
     * do the mapping! by passing a map/value source in first argument mapping is invoked by compiling and iterating through
     * passed mapping object/value and dissolving/replacing any mappable placeholders found. the first argument can be of
     * the following type:
     * - encoded json string
     * - path to json file
     * - string with single placeholder or string with multiple placeholders
     * - object/array
     * - instance of Xapp_Util_Json_Store
     * - instance of Xapp_Util_Json_Query
     *
     * any other value will raise an exception. the first argument will be compiled and returned with parsed placeholders.
     * pass a single string with a single placeholder in first argument to use method as assert/test function.
     * if the second argument is set to boolean true the result will be json encoded. if the third argument is set to
     * boolean true will return boolean false on error/exception and throw any exception.
     *
     * @error 17215
     * @param mixed $map expects a valid mappable value as explained above
     * @param bool $encode expects options encoding flag
     * @param bool $exception expects optional exception flag
     * @return bool|mixed
     * @throws Exception|Xapp_Util_Json_Exception
     */
    public function map($map, $encode = false, $exception = true)
    {
        $class = get_class();

        try
        {
            if(!empty($map))
            {
                $this->path = new Xapp_Util_Json_Path(new Xapp_Util_Json_Store($this->objects()));

                if(is_string($map))
                {
                    if(xapp_is('file', $map))
                    {
                        if(($map = file_get_contents($file = $map)) === false)
                        {
                            throw new Xapp_Util_Json_Exception(xapp_sprintf(_("unable to load mapping object from file: %s"), $file), 1721502);
                        }
                    }
                    if(Xapp_Util_Json::isJson($map))
                    {
                        $map = Xapp_Util_Json::decode($map);
                    }
                }
                if(is_object($map) || is_array($map))
                {
                    $this->map = new Xapp_Util_Json_Store($map);
                }else if($map instanceof Xapp_Util_Json_Store){
                    $this->map = $map;
                }else if($map instanceof Xapp_Util_Json_Query){
                    $this->map = new Xapp_Util_Json_Store($map->object());
                }else if(is_string($map)){
                    return $this->execute($map, '');
                }else{
                    throw new Xapp_Util_Json_Exception(_("first argument is not a mappable value"), 1721501);
                }
                $this->compile($this->map->object());
                if((bool)$encode)
                {
                    return Xapp_Util_Json::encode($this->map->object());
                }else{
                    return $this->map->object();
                }
            }
            return false;
        }
        catch(Exception $e)
        {
            if((bool)$exception){
                if($e instanceof Xapp_Result_Exception && (xapp_is_option(self::CUSTOM_EXCEPTION, $class) && ($_e = xapp_get_option(self::CUSTOM_EXCEPTION, $class)) !== null))
                {
                    throw $_e;
                }else{
                    throw $e;
                }
            }else{
                return false;
            }
        }
    }


    /**
     * static shortcut method to map objects in one call. see the relevant methods for more explanation
     *
     * @error 17216
     * @param mixed $objects expects object/value as mapping target
     * @param mixed $map expects mapping object/value as mapping source
     * @param bool $encode expects options encoding flag
     * @param bool $exception expects optional exception flag
     * @param null|mixed $options expects optional options
     * @see Xapp_Util_Json_Mapper::add
     * @see Xapp_Util_Json_Mapper::map
     * @return bool|mixed
     * @throws Exception|Xapp_Util_Json_Exception
     */
    public static function m($objects, $map, $encode = false, $exception = true, $options = null)
    {
        return self::create($options)->add($objects)->map($map, $encode, $exception);
    }


    /**
     * simulate/assert mapping source against mapping target objects/value. returns boolean value and should only be used
     * to test mapping paths against expected values
     *
     * @error 17217
     * @param mixed $value expects mapping object/value as mapping source
     * @return bool
     */
    public function assert($value)
    {
        try
        {
            return (bool)$this->map($value);
        }
        catch(Exception $e)
        {
            return false;
        }
    }


    /**
     * object compiler/iterator walls through mapping source object and executes/maps all values that are mappable = strings
     * that contain placeholder values. the compile iterator will keep the source path in memory
     *
     * @error 17218
     * @param mixed $object expects object to compile
     * @param string $path contains the source path/key of to be mapped value
     * @return mixed
     */
    final protected function compile(&$object, $path = '')
    {
        foreach($object as $k => &$v)
        {
            if(is_object($v) || is_array($v))
            {
                $this->compile($v, "$path/$k");
            }else{
                $this->execute($v, "$path/$k");
            }
        }
        return $object;
    }


    /**
     * execute mapping on a single values found that contains a mapping placeholder defined by class option. the value is
     * a string that contains either a single placeholder or a string with multiple placeholders which results in different
     * mapping approaches whereas the former will replace the placeholder completely and will accept any type of return
     * value the later will either force any return to string or throw exceptions if mappings can not be resolved to string.
     * see class description for more information of placeholder/query/path syntax
     *
     * @error 17219
     * @param string $value expects the string with placeholder(s) to map
     * @param string $path contains the path to current element in mapping source
     * @return mixed|string
     * @throws Xapp_Util_Json_Exception
     */
    final protected function execute(&$value, $path)
    {
        $class = get_class();
        $regex = xapp_regex_delimit(xapp_get_option(self::PLACEHOLDER_REGEX, $class));

        //placeholder is a object replacement since placeholder replaces the complete value
        if(preg_match("=^".trim($regex, ' ^$')."$=is", $value, $m))
        {
            $value = $this->query($m, false);
        //placeholder is part of string requiring the placeholder replacement to be a string representable value
        }else{
            $value = preg_replace_callback("=$regex=is", array($this, 'query'), $value);
        }
        //execute internal actions if found
        if((!is_object($value) && !is_array($value)) && stripos($value, '@') !== false)
        {
            //shorthand path @: to get value from mapping object itself
            if(preg_match('=\@\:(\/?(?:[^\/]+))+=is', $value, $m))
            {
                $this->action('map', array($m[1]), $value, $path);
            //action mapping for syntax @map(*)
            }else if(preg_match('=(?:\@([a-z\_]+)(?:\(([^\)]*)\))?)=is', $value, $m)){
                $m[1] = trim($m[1]);
                if(isset($m[2]) && $m[2] !== '')
                {
                    $m[2] = str_getcsv(trim($m[2]), ',', '\'');
                }else{
                    $m[2] = null;
                }
                if(in_array($m[1], $this->_internalActions) || array_key_exists($m[1], $this->_customActions))
                {
                    $this->action($m[1], $m[2], $value, $path);
                }else{
                    throw new Xapp_Util_Json_Exception(xapp_sprintf(_("action: %s is not a registered or internal action"), $m[1]), 1721901);
                }
            }
        }

        return $value;
    }


    /**
     * query the path/action/ found inside placeholder tag which consists of isolating callbacks from mapping command,
     * perform a query if command contains a path to map, and pipe query results to callback chain if callbacks have been
     * isolated before. the second argument if set to true will force any result exception of path queries to empty strings,
     * boolean false will instantly throw an exception if path query return either false or throws a result exception.
     * see class description for more on mapping syntax
     *
     * @error 17220
     * @param array $path expects an array from execute() method containing the matched values
     * @param bool $stringify expects stringify flag
     * @return mixed
     * @throws Xapp_Util_Json_Exception
     * @throws Exception
     * @throws Xapp_Result_Exception
     */
    final protected function query(Array $path, $stringify = true)
    {
        $class = get_class();
        $result = array();
        $callbacks = array();

        $path = trim($path[1]);

        //isolate callbacks from placeholder string
        if(preg_match('/(?:(?:(?:^|\s)\#(?<!\@)([^\@\)]+))\(((?=.*[\/])(?:[^\)])+\)?)\))/is', $path, $m))
        {
            if(stripos($m[2], ';') !== false)
            {
                $path = preg_split('/\s*\;\s*/i', $m[2]);
            }else{
                $path = array(trim($m[2]));
            }
            foreach(array_filter(preg_split('/\s*\;\s*/i', trim($m[1], ' ;'))) as $c)
            {
                if(stripos($c, '|') !== false)
                {
                    $p = str_getcsv(trim(substr($c, strpos($c, '|') + 1), ' ,'), ',', '\'');
                    $c = str_replace(array('.'), '::', substr($c, 0, strpos($c, '|')));
                }else{
                    $p = array();
                    $c = str_replace(array('.'), '::', $c);
                }
                //callback is class::method
                if(stripos($c, '::') !== false)
                {
                    $callbacks[] = array(substr($c, 0, stripos($c, '::')), strtolower(substr($c, stripos($c, '::') + 2)), $p);
                //callback is function
                }else{
                    $callbacks[] = array(strtolower($c), null, $p);
                }
            }
        }else{
            $path = array($path);
        }

        //execute path queries
        foreach($path as &$p)
        {
            //normalize namespaces
            $p = $this->normalize($p);

            //query path
            if((bool)$stringify)
            {
                try
                {
                    $result[] = $this->stringify($this->path->query($p));
                }
                catch(Xapp_Result_Exception $e)
                {
                    if(xapp_get_option(self::FORCE_STRING, $class))
                    {
                        $result[] = "";
                    }else{
                        throw $e;
                    }
                }
            }else{
                $result[] = $this->path->query($p);
            }
        }

        //execute isolated callbacks
        foreach($callbacks as $callback)
        {
            if($this->isRegistered($callback))
            {
                $this->callback($callback, $result);
            }else{
                throw new Xapp_Util_Json_Exception(vsprintf("callback: %s is not a registered callback", (array)$callback));
            }
        }

        //return result
        return (is_array($result)) ? $result[0] : $result;
    }


    /**
     * execute/invoke a callback found in mapping command. a callback, by default, will receive the result from executing
     * /querying the rest of the command as first argument. callbacks, according to the callback syntax, see class
     * description for more, can be feed with additional static arguments and/or dynamic argument assignment by $
     * placeholders. see class description for full explanation
     *
     * @error 17221
     * @param array $callback expects the callback data passed from execute/query function
     * @param mixed $result reference to result
     * @return array
     * @throws Xapp_Util_Json_Exception
     */
    protected function callback(Array $callback, &$result)
    {
        if(sizeof($callback[2]) === 0)
        {
            $callback[2] = $result;
        }else{
            foreach($callback[2] as &$param)
            {
                if(substr($param, 0, 1) === '$')
                {
                    if(strlen($param) > 1)
                    {
                        if(array_key_exists(((int)substr($param, 1) - 1), $result))
                        {
                            $param = $result[((int)substr($param, 1) - 1)];
                        }else{
                            throw new Xapp_Util_Json_Exception(_("out of range for variable matching for callback"), 1722101);
                        }
                    }else{
                        $param = $result[0];
                    }
                }
            }
        }
        if(is_null($callback[1]))
        {
            $result[0] = $this->invoke($callback[0], $callback[2]);
        }else{
            $result[0] = $this->invoke(array($callback[0], $callback[1]), $callback[2]);
        }

        return $result = array_slice($result, 0, 1);
    }


    /**
     * execute an action identified by action syntax "@action(args)". see class description and comments in method body
     * for all action types and explanations
     *
     * @error 17222
     * @param string $action expects the action name
     * @param null|array $params expects optional parameters
     * @param mixed $value expects the original value
     * @param string $path expects the path of where action was found
     * @return mixed
     * @throws Xapp_Util_Json_Exception
     * @throws Exception
     * @throws Xapp_Result_Exception
     */
    final protected function action($action, $params = null, &$value, $path)
    {
        $tmp = array();
        $class = get_class();
        $action = strtolower(trim($action));

        switch($action)
        {
            /**
             * ${@call(func,1)}
             * execute a callback as action. expects the callback as first parameter and additional parameters "," comma
             * separated as second, third etc. value. passes the source path as first argument, reference to mapper instance
             * as second argument and any other additional parameter found as next arguments to callback. you can not have
             * query path´ or and other action as additional argument!
             */
            case ($action === 'call' || $action === 'callback')
            :
                if($params !== null)
                {
                    if($this->isRegistered($params[0]) && $this->isCallable($params[0]))
                    {
                        if(sizeof($params) > 1)
                        {
                            $value = $this->invoke($params[0], array_merge(array($path, &$this), array_slice($params, 1)));
                        }else{
                            $value = $this->invoke($params[0], array($path, &$this));
                        }
                    }else{
                        throw new Xapp_Util_Json_Exception(xapp_sprintf(_("action: call and callback: %2 is not a registered or valid callback"), $params[0]), 1722201);
                    }
                }
                break;
            /**
             * ${@map|copy(/source/target/key,default)}
             * query path on source mapping object instead of target objects just like using "@:/" syntax. second parameter
             * can be a default return value if path query can not be resolved and class option FORCE_STRING is set to
             * boolean true or else will throw exception
             */
            case ($action === 'map' || $action === 'copy')
            :
                if($params !== null)
                {
                    try
                    {
                        $value = $this->map->get(trim($params[0]));
                    }
                    catch(Xapp_Result_Exception $e)
                    {
                        if(xapp_get_option(self::FORCE_STRING, $class))
                        {
                            if(array_key_exists(1, $params)){ $value = Xapp_Util_Json_Query::typify($params[1]); }
                        }else{
                            throw $e;
                        }
                    }
                }
                break;
            /**
             * ${@remove(1)}
             * remove the node or parent node where the first parameter defines the number of parent nodes to remove, eg.
             * "@remove(3)" will try to go up the tree to delete everything below the parent 3 levels up of the node from
             * where the remove action was found. usually this will be used in conditional path queries like:
             * "${ns1:/path ? @remove(1)}"
             */
            case 'remove'
            :
                try
                {
                    if($params !== null)
                    {
                        $path = explode('/', trim($path, ' /'));
                        $path = array_slice($path, 0, sizeof($path) - abs((int)$params[0]));
                        $path = implode('/', $path);
                        $path = trim($path);
                    }
                    if(!empty($path))
                    {
                        $this->map->remove($path);
                    }
                }
                catch(Xapp_Result_Exception $e){}
                break;
            /**
             * ${@throw()}
             * ${@throw(Exception,message,code,severity)}
             * throw a exception which extends either php´s Exception or ErrorException. this will be used in most cases
             * in conditional path queries like ${ns1:/path ? @throw()}". the first parameter defines the exception class
             * as string, the second the exception message, the third the optional error code, the fourth the optional
             * severity level if the exception is instance of ErrorException. if the class option CUSTOM_EXCEPTION holds
             * a valid exception the throw action does not need any parameter but can be called only with: ${@throw()} =
             * the custom exception is thrown directly
             */
            case 'throw'
            :
                if($params !== null && sizeof($params) >= 2)
                {
                    if($params[0] instanceof Exception)
                    {
                        if(isset($params[3]))
                        {
                            throw new $params[0]($params[1], (($params[2]) ? (int)$params[2] : 0), (int)$params[3]);
                        }else{
                            throw new $params[0]($params[1], (($params[2]) ? (int)$params[2] : 0));
                        }
                    }else{
                        throw new Xapp_Util_Json_Exception(_("action: throw - first parameter must be instance of Exception"));
                    }
                }else{
                    if(xapp_is_option(self::CUSTOM_EXCEPTION, $class) && ($e = xapp_get_option(self::CUSTOM_EXCEPTION, $class)) !== null)
                    {
                        throw $e;
                    }
                }
                break;
            /**
             * ${@cut(/path/to/key,default)}
             * cut and paste within source object/map will copy the value at path and paste/replace the key and remove
             * the node at path as well. pass optional default value in case class option FORCE_STRING is set to true or
             * throw exception if path could not be resolved
             */
            case 'cut'
            :
                if($params !== null)
                {
                    try
                    {
                        $value = $this->map->get(trim($params[0]));
                        $this->map->remove(trim($params[0]));
                    }
                    catch(Xapp_Result_Exception $e)
                    {
                        if(xapp_get_option(self::FORCE_STRING, $class))
                        {
                            if(array_key_exists(1, $params)){ $value = Xapp_Util_Json_Query::typify($params[1]); }
                        }else{
                            throw $e;
                        }
                    }
                }
                break;
            /**
             * ${@query(/path/to/key,default)}
             * re-query a path which is a path. the value of the path in first parameter will be used to run a second path
             * query expected the value of the first path query to be a valid path. pass optional default value in case
             * class option FORCE_STRING is set to true or throw exception if path could not be resolved
             */
            case 'query'
            :
                if($params !== null)
                {
                    try
                    {
                        $value = $this->normalize($params[0]);
                        $value = $this->path->query($value);
                        $value = $this->path->query($value);
                    }
                    catch(Xapp_Result_Exception $e)
                    {
                        if(xapp_get_option(self::FORCE_STRING, $class))
                        {
                            if(array_key_exists(1, $params)){ $value = Xapp_Util_Json_Query::typify($params[1]); }
                        }else{
                            throw $e;
                        }
                    }
                }
                break;
            /**
             * ${@glue(ns0:/store/book/0/firstName,+,ns0:/store/book/0/LastName)}
             * ${@concat(', ',ns0:/store/book/0/firstName,ns0:/store/book/0/LastName)}
             * glue values from path queries and/or static values together or concat sql like the value with separator at
             * first parameter. results of path queries will be reduced to string. no exceptions thrown
             */
            case ($action === 'concat' || $action === 'glue')
            :
                if($params !== null)
                {
                    try
                    {
                        for($i = (($action === 'concat') ? 1 : 0); $i < sizeof($params); $i++)
                        {
                            $tmp[] = $this->stringify($this->path->query($this->normalize($params[$i])));
                        }
                    }
                    catch(Xapp_Result_Exception $e){}
                    if($action === 'concat')
                    {
                        $value = trim(implode((string)$params[0], $tmp));
                    }else{
                        $value = trim(implode('', $tmp));
                    }
                }
                break;
            /**
             * ${@cast('int',ns0:/store/book/0/id)}
             * ${@cast('int','default',ns0:/store/book/0/id)}
             * cast value to data type passing data type compatible for php´s settype function in first argument and optional
             * second argument default return value if path in second or third argument does not resolve
             */
            case ($action === 'cast')
            :
                if($params !== null && sizeof($params) >= 2)
                {
                    $default = null;
                    $type = strtolower(trim((string)$params[0]));
                    if(array_key_exists(2, $params)){
                        $default = trim((string)$params[1]);
                        $path = trim((string)$params[2]);
                    }else{
                        $path = trim((string)$params[1]);
                    }
                    if(($value = $this->path->query($this->normalize($path), '__FALSE__')) !== '__FALSE__')
                    {
                        settype($value, $type);
                    }else{
                        $value = Xapp_Util_Json_Query::typify($default);
                    }
                }
                break;
            /**
             * ${@null()}
             * return php null value mostly used in conditional path queries like ${ns1:/path ? @null()}" as alternative
             * to ${ns1:/path ? null}"
             */
            case 'null'
            :
                $value = null;
                break;
            /**
             * ${@customAction(param1,param2)}
             * user custom action will pass the passed parameter as first argument to callback, the value as second, the
             * path as third and reference to mapping instance as fourth.
             */
            default
            :
              if(array_key_exists($action, $this->_customActions))
              {
                  $value = $this->invoke($this->_customActions[$action], array($params, &$value, $path, &$this));
              }
        }
        return $value;
    }


    /**
     * invoke callback. will throw exception on reflection error
     *
     * @error 17223
     * @param string|array $callback expects callback to invoke
     * @param null|mixed $params expects optional parameter to pass to callback
     * @return mixed
     * @throws Xapp_Util_Json_Exception
     */
    protected function invoke($callback, $params = null)
    {
        if(is_string($callback))
        {
            if(preg_match('=^([a-z0-9\_]+)(?:\.|\:+)([a-z0-9\_]+)=i', $callback, $m))
            {
                $callback = array($m[1], $m[2]);
            }
        }
        if(is_array($callback))
        {
            try
            {
                $class = new ReflectionClass($callback[0]);
                $method = $class->getMethod(trim((string)$callback[1]));
                if($method->isStatic())
                {
                    return $method->invokeArgs(null, (array)$params);
                }else{
                    return $method->invokeArgs($class->newInstance(), (array)$params);
                }
            }
            catch(ReflectionException $e)
            {
                throw new Xapp_Util_Json_Exception(xapp_sprintf(_("unable to invoke callback due to reflection error: %s"), $e->getMessage()), 1722301);
            }
        }else{
            return call_user_func_array($callback, (array)$params);
        }
    }


    /**
     * check if callable, a function or a class method, is callable - see php´s callable explanation for more
     *
     * @error 17224
     * @param string|array $callable expects callable
     * @return bool
     */
    protected function isCallable($callable)
    {
        if(is_array($callable))
        {
            if(is_callable($callable))
            {
                return true;
            }
            try
            {
                $class = new ReflectionClass($callable[0]);
                $method = trim((string)$callable[1]);
                if($class->hasMethod($method))
                {
                    $method = $class->getMethod($method);
                    if($method->isPublic())
                    {
                        if($method->isStatic())
                        {
                            return true;
                        }else if($class->isInstantiable()){
                            return true;
                        }
                    }
                }
            }
            catch(ReflectionException $e){}
        }else if(is_callable($callable)){
            return true;
        }
        return false;
    }


    /**
     * check whether a callback has been registered at mapping class instance or not
     *
     * @error 17225
     * @param string|array $callback expects the callback to check
     * @return bool
     */
    protected function isRegistered($callback)
    {
        $class = get_class();

        if(xapp_get_option(self::ALLOW_ONLY_REGISTERED_CALLBACKS, $class))
        {
            if(!is_array($callback))
            {
                $callback = array($callback);
            }
            if(isset($callback[1]) && !is_null($callback[1]))
            {
                if(array_key_exists("class:$callback[0]", $this->_callbacks) && in_array(strtolower($callback[1]), $this->_callbacks["class:$callback[0]"]))
                {
                    return true;
                }
            }else{
                if(in_array($callback[0], xapp_get_option(self::INTERNAL_FUNCTIONS, $class)) || array_key_exists("func:$callback[0]", $this->_callbacks))
                {
                    return true;
                }
            }
            return false;
        }else{
            return true;
        }
    }


    /**
     * text a value if it contains a mappable placeholder defined by class option PLACEHOLDER_REGEX and return boolean
     * true if so. NOTE: can only test for string values
     *
     * @error 17230
     * @param mixed $value expects value to test
     * @return bool
     */
    public function hasPlaceholder($value)
    {
        if(!is_array($value) && !is_object($value))
        {
            $regex = xapp_regex_delimit(xapp_get_option(self::PLACEHOLDER_REGEX, $this));
            if(preg_match("=^".trim($regex, ' ^$')."$=is", $value))
            {
                return true;
            }
        }
        return false;
    }


    /**
     * stringify = convert to string representable value
     *
     * @error 17226
     * @param mixed $value expects the value to stringify
     * @return string
     */
    protected function stringify($value)
    {
        $class = get_class();

        if(is_string($value) || is_numeric($value))
        {
            return (string)$value;
        }else if(is_bool($value) && xapp_get_option(self::STRINGIFY_BOOL_TO_INT, $class)){
            return (string)(int)$value;
        }else if((is_array($value) || is_object($value)) && xapp_get_option(self::STRINGIFY_OBJECT_TO_JSON, $class)){
            return Xapp_Util_Json::encode($value);
        }else{
            return "";
        }
    }


    /**
     * normalize path with mapping namespace, e.g. ns0:/path to /ns0/path, to receive a json path compatible path
     *
     * @error 17227
     * @param string $path
     * @return string
     */
    protected function normalize($path)
    {
        return preg_replace("=(^\s*)\/?([a-z0-9\_\-]+)\:\/?=is", '$1/$2/', trim($path));
    }


    /**
     * on instance clone delete all custom actions
     *
     * @error 17228
     */
    public function __clone()
    {
        $this->_customActions = array();
    }
}