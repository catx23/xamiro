<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Rpc');
xapp_import('xapp.Rpc.Smd');
xapp_import('xapp.Rpc.Server.Exception');
xapp_import('xapp.Rpc.Request');
xapp_import('xapp.Rpc.Response');
xapp_import('xapp.Rpc.Fault');


/**
 * Rpc server class
 *
 * @package Rpc
 * @class Xapp_Rpc_Server
 * @error 142
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Rpc_Server extends Xapp_Rpc implements Xapp_Singleton_Interface
{
    /**
     * rpc call type: call
     *
     * @const CALL
     */
    const CALL                      = 0;

    /**
     * rpc call type: notification
     *
     * @const NOTIFICATION
     */
    const NOTIFICATION              = 1;


    /**
     * contains request instance
     *
     * @const REQUEST
     */
    const REQUEST                   = 'RPC_SERVER_REQUEST';

    /**
     * contains response instance
     *
     * @const RESPONSE
     */
    const RESPONSE                  = 'RPC_SERVER_RESPONSE';

    /**
     * contains smd service mapping instance
     *
     * @const SMD
     */
    const SMD                       = 'RPC_SERVER_SMD';

    /**
     * tells the server to allow debugging in response error/fault object with the
     * following values:
     * 1) false or 0 = no extended debugging, the data parameter will be omitted completely
     * 2) true or 1 = complete debugging, the data parameter will contain all debug parameters
     * 3) 2 = will return only the "message", "code", "type" and custom parameters
     *
     * @const DEBUG
     */
    const DEBUG                     = 'RPC_SERVER_DEBUG';

    /**
     * tells server if to allow function or only class methods
     *
     * @const ALLOW_FUNCTIONS
     */
    const ALLOW_FUNCTIONS           = 'RPC_SERVER_ALLOW_FUNCTIONS';

    /**
     * set additional parameters that are not part of the service function or method
     * but part of the service itself, like session, signature, etc. the additional
     * parameter must be passed as an array where each parameter is an array also:
     *
     * <code>
     *  ADDITIONAL_PARAMETERS => array('session' => array('string', false), ...)
     * </code>
     *
     * the key can be the parameter name or in case of unnamed parameter the array index.
     * the array itself must contain as first value the data type of parameter as string or
     * multiple as array and boolean value if optional or not as second value. additional
     * parameters can also be set with addParam function
     *
     * @const ADDITIONAL_PARAMETERS
     */
    const ADDITIONAL_PARAMETERS     = 'RPC_SERVER_ADDITIONAL_PARAMETERS';

    /**
     * boolean value defining whether to omit error message in response showing
     * only error code or not
     *
     * @const OMIT_ERROR
     */
    const OMIT_ERROR                = 'RPC_SERVER_OMIT_ERROR';

    /**
     * pass service (class/method or function) in "service" parameter via GET. the rpc server will look for service
     * key and resolve value as if it would be passed in POST request and "method" parameter. this value is
     * important for smd mapping and will be passed to smd instance. the server
     * class will try to look for "service" GET parameter in any case to find class,
     * method or function
     *
     * @const SERVICE_OVER_GET
     */
    const SERVICE_OVER_GET          = 'RPC_SERVER_SERVICE_OVER_GET';

    /**
     * define whether to return exceptions as are from within application returning
     * all error messages readable or summarize application error as one containing
     * only the application error code. use this option to make sure that all sensitive
     * error messages from your application are not readable - just the code for reference
     *
     * @const APPLICATION_ERROR
     */
    const APPLICATION_ERROR         = 'RPC_SERVER_APPLICATION_ERROR';

    /**
     * defines if server is used in rewrite mode and contains the rewrite pattern so that
     * smd can resolve the right target. see smd constant and function getTarget for more
     *
     * @see Xapp_Rpc_Smd::getTarget
     * @const REWRITE_URL
     */
    const REWRITE_URL               = 'RPC_SERVER_REWRITE_URL';

    /**
     * defines the the namespace identifier or class resolver identifier that makes the rpc
     * server compatible with php5 namespace or zend style namespace string separators "_".
     * the value must be compatible to the global application namespace convention so any
     * registered autoloader will find the class path. defaults to default xapp none php5
     * namespace identifier "_". if you use php5 namespaces the identifier should be set to "\\"
     *
     * @const NAMESPACE_IDENTIFIER
     */
    const NAMESPACE_IDENTIFIER      = 'RPC_SERVER_NAMESPACE_IDENTIFIER';

    /**
     * defines the class/method separator to identify class and methods in method parameter
     * or service url parameter. defaults to "." as separator. should never be the same string value
     * as NAMESPACE_IDENTIFIER since class and method can not be distinguished than.
     *
     * @const CLASS_METHOD_SEPARATOR
     */
    const CLASS_METHOD_SEPARATOR    = 'RPC_SERVER_CLASS_METHOD_SEPARATOR';

    /**
     * defines a transaction id regex pattern. must be a valid regex pattern compatible with php´s
     * preg_match method. the pattern must be set including delimiters and flags. there is no regex
     * pattern validation!
     *
     * @const TRANSACTION_ID_REGEX
     */
    const TRANSACTION_ID_REGEX      = 'RPC_SERVER_TRANSACTION_ID_REGEX';

    /**
     * pass xapp cache instance to rpc server in order to cache response returns according to cache
     * instance options. the cache key will be constructed from request signature as in class/method or
     * function name and the functions/methods arguments serialized to string
     *
     * @const CACHE
     */
    const CACHE                     = 'RPC_SERVER_CACHE';

    /**
     * define services that can be cached. by default and if cache instance is set all services, methods and functions
     * included in the SMD are cachable. to only allow certain services to be cached use this option by passing an array
     * of regex patterns. these patterns must identify the service as found in the smd map. e.g. a php5 namespace service
     * would look like "\rpc\service\class.method" and a simple function "my_function". the regex pattern must NOT
     * include delimiters as the patterns are included like '/'.implode('|', (array)$services).'/i'
     *
     * @const CACHE_SERVICES
     */
    const CACHE_SERVICES            = 'RPC_SERVER_CACHE_SERVICES';

    /**
     * defines which is the cache key when using rpc cache. if set to boolean false (default) will cache with service
     * name and passed params (serialized). if set to true will use the transaction id as cache key
     *
     * @const CACHE_BY_TRANSACTION_ID
     */
    const CACHE_BY_TRANSACTION_ID   = 'RPC_SERVER_CACHE_BY_TRANSACTION_ID';

    /**
     * controls how the request params = method/function arguments are passed to called method/function. the default
     * behaviour is passing all parameters as std object to method/function when invoked. this behaviour can be changed
     * by setting this option to true so that all parameters/arguments are passed as array
     *
     * @const PARAMS_AS_ARRAY
     */
    const PARAMS_AS_ARRAY           = 'RPC_SERVER_PARAMS_AS_ARRAY';

    /**
     * enable and allow rpc request for notifications that do not return a response beside the response header. if set
     * the request call does not need the transaction parameter "id" since no response is returned. NOTE: in batched
     * requests notification requests are omitted in response
     *
     * @const ALLOW_NOTIFICATIONS
     */
    const ALLOW_NOTIFICATIONS       = 'RPC_SERVER_ALLOW_NOTIFICATIONS';

    /**
     * callback for error response objects when executing call. the callback receives the exception thrown anywhere during
     * validation and executing/invoking of call. the callback will receive the following arguments:
     * <code>
     *    function callback(Exception &$e, Xapp_Rpc_Server $server, Array $call){}
     * </code>
     *
     * the exception can be either manipulated by reference or returning exception in callback. the callback can also
     * return anything else that will become the error response object! use this function with care since error object
     * should comply with rpc specs. NOTE: if a callback has been set all failed calls with be send to this callback. if
     * not and the request is not a batched request the exception will be thrown outside of rpc server instance to be
     * caught and processed by either custom functionality or dumped via static rpc dump method, e.g.:
     * <code>
     *   xapp_dump($e, 'Xapp_Rpc_Server_Json');
     * </code>
     *
     * NOTE: any errors outside of actual call invocation should be handled that way to return a valid rpc error response
     * object
     *
     * @const EXCEPTION_CALLBACK
     */
    const EXCEPTION_CALLBACK            = 'RPC_SERVER_EXCEPTION_CALLBACK';

    /**
     * option that will force rpp jsonp response behaviour to 1.2 specs in returning http status error codes in case
     * of thrown exception. the map in 1.2 draft maps json rpc error codes to http error codes so rpc client can check
     * first for http response status code and then evaluate error object if required
     *
     * @const COMPLY_TO_JSONRPC_1_2
     */
    const COMPLY_TO_JSONRPC_1_2         =  'RPC_SERVER_COMPLY_TO_JSONRPC_1_2';

    /**
     * by default batched requests or multiple rpc calls are not allowed since api logic may not be designed to handle
     * batched request. to allow batched request set option to boolean true
     *
     * @const ALLOW_BATCHED_REQUESTS
     */
    const ALLOW_BATCHED_REQUESTS        = 'RPC_SERVER_ALLOW_BATCHED_REQUESTS';

	/**
	 * set if method will be identified by "service" get parameter or within
	 * request post/get parameter method, e.g. for json as method "class.method". this value is
	 * important for smd mapping and will be passed to smd instance. the server
	 * class will try to look for service parameter in any case to find class,
	 * method or function
	 *
	 * @const METHOD_AS_SERVICE
	 */
	const METHOD_AS_SERVICE      = 'RPC_SERVER_METHOD_AS_SERVICE';

	/**
	 * defines if server is doing validation
	 *
	 * @see Xapp_Rpc_Smd::getTarget
	 * @const REWRITE_URL
	 */
	const VALIDATE              = 'RPC_SERVER_VALIDATE';

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::REQUEST                   => 'Xapp_Rpc_Request',
        self::RESPONSE                  => 'Xapp_Rpc_Response',
        self::SMD                       => 'Xapp_Rpc_Smd',
        self::DEBUG                     => array(XAPP_TYPE_BOOL, XAPP_TYPE_INT),
        self::ALLOW_FUNCTIONS           => XAPP_TYPE_BOOL,
        self::ADDITIONAL_PARAMETERS     => XAPP_TYPE_ARRAY,
        self::OMIT_ERROR                => XAPP_TYPE_BOOL,
        self::SERVICE_OVER_GET          => XAPP_TYPE_BOOL,
        self::APPLICATION_ERROR         => XAPP_TYPE_BOOL,
        self::REWRITE_URL               => array(XAPP_TYPE_BOOL, XAPP_TYPE_STRING),
        self::NAMESPACE_IDENTIFIER      => XAPP_TYPE_STRING,
        self::CLASS_METHOD_SEPARATOR    => XAPP_TYPE_STRING,
        self::TRANSACTION_ID_REGEX      => array(XAPP_TYPE_BOOL, XAPP_TYPE_STRING),
        self::CACHE                     => array('Xapp_Cache_Driver', XAPP_TYPE_NULL),
        self::CACHE_SERVICES            => array(XAPP_TYPE_STRING, XAPP_TYPE_ARRAY),
        self::CACHE_BY_TRANSACTION_ID   => XAPP_TYPE_BOOL,
        self::PARAMS_AS_ARRAY           => XAPP_TYPE_BOOL,
        self::ALLOW_NOTIFICATIONS       => XAPP_TYPE_BOOL,
        self::EXCEPTION_CALLBACK        => array(XAPP_TYPE_NULL, XAPP_TYPE_CALLABLE),
        self::COMPLY_TO_JSONRPC_1_2     => XAPP_TYPE_BOOL,
        self::ALLOW_BATCHED_REQUESTS    => XAPP_TYPE_BOOL,
	    self::VALIDATE                  => XAPP_TYPE_BOOL
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::REQUEST                   => 0,
        self::RESPONSE                  => 0,
        self::SMD                       => 0,
        self::DEBUG                     => 1,
        self::ALLOW_FUNCTIONS           => 1,
        self::ADDITIONAL_PARAMETERS     => 0,
        self::OMIT_ERROR                => 1,
        self::SERVICE_OVER_GET          => 1,
        self::APPLICATION_ERROR         => 1,
        self::REWRITE_URL               => 0,
        self::NAMESPACE_IDENTIFIER      => 0,
        self::CLASS_METHOD_SEPARATOR    => 0,
        self::TRANSACTION_ID_REGEX      => 0,
        self::CACHE                     => 0,
        self::CACHE_SERVICES            => 0,
        self::CACHE_BY_TRANSACTION_ID   => 0,
        self::PARAMS_AS_ARRAY           => 1,
        self::ALLOW_NOTIFICATIONS       => 0,
        self::EXCEPTION_CALLBACK        => 0,
        self::COMPLY_TO_JSONRPC_1_2     => 1,
        self::ALLOW_BATCHED_REQUESTS    => 1,
	    self::VALIDATE                  => 0
    );

    /**
     * contains if set the requested service in GET array which isset when using rpc services over GET and service in
     * "service" GET parameter.
     *
     * @var null|string
     */
    protected $_service = null;

    /**
     * contains return/result data from invoked methods/functions as well as errors to be returned as response
     *
     * @var array
     */
    protected $_data = array();

    /**
     * contains instances of classes that are registered instead of class names as string
     *
     * @var array
     */
    protected $_objects = array();

    /**
     * contains all service calls or requests with each item consisting of 0 = service name, 1 = method/function name
     * 2) class name (if set), 3) the complete request parameters object/array
     *
     * @var array
     */
    protected $_calls = array();

    /**
     * contains all services which are the method/function (and class) to be invoked in request. the service name is
     * either found in the request parameter "method" or via the service GET parameter.
     *
     * @var array
     */
    protected $_services = array();

    /**
     * contains the class instance if called service is a class/method that will allow for extended functionality since
     * the called class can contain methods, such as onBeforeCall, onAbort to handle that will be called during rpc call
     * handling
     *
     * @var null|object
     */
    protected $_class = null;


    /**
     * init server
     *
     * @return mixed
     */
    abstract protected function init();

    /**
     * execute request
     *
     * @param array $call
     * @return mixed
     */
    abstract protected function execute($call);

    /**
     * flush result
     *
     * @return mixed
     */
    abstract protected function flush();

    /**
     * shutdown server
     *
     * @return mixed
     */
    abstract protected function shutdown();

    /**
     * dump error
     *
     * @param Exception $error
     * @return mixed
     */
    abstract public function error(Exception $error);


    /**
     * class constructor that must be called from child class to initialize
     * options and pass options to smd instance. always make sure the concrete
     * server calls parent::__construct() in constructor
     *
     * @error 14201
     * @throw Xapp_Rpc_Server_Exception
     */
    protected function __construct()
    {
        $smd = $this->smd();

        if(xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
        {
            $params = xapp_get_option(self::ADDITIONAL_PARAMETERS, $this);
            foreach($params as $k => &$v)
            {
                if(is_array($v) && isset($v[0]) && !empty($v[0]))
                {
                    $v[0] = Xapp_Rpc_Smd::mapType($v[0]);
                }else{
                    throw new Xapp_Rpc_Server_Exception(xapp_sprintf(_("additional parameter: %s must have a valid data type value"), $k), 1420101);
                }
            }
            xapp_reset_option(self::ADDITIONAL_PARAMETERS, $params, $this);
        }
        if($smd !== null && xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
        {
            xapp_set_option(Xapp_Rpc_Smd::ADDITIONAL_PARAMETERS, xapp_get_option(self::ADDITIONAL_PARAMETERS, $this), $smd);
        }
        if($smd !== null && xapp_is_option(self::SERVICE_OVER_GET, $this))
        {
            xapp_set_option(Xapp_Rpc_Smd::SERVICE_OVER_GET, xapp_get_option(self::SERVICE_OVER_GET, $this), $smd);
        }
        if($smd !== null && xapp_is_option(self::REWRITE_URL, $this))
        {
            xapp_set_option(Xapp_Rpc_Smd::REWRITE_URL, xapp_get_option(self::REWRITE_URL, $this), $smd);
        }
        if($smd !== null && xapp_is_option(self::CLASS_METHOD_SEPARATOR, $this))
        {
            xapp_set_option(Xapp_Rpc_Smd::CLASS_METHOD_SEPARATOR, xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this), $smd);
        }
    }


    /**
     * setup server instance by setting variables, calling pre execute handler and initializing concrete
     * server implementation
     *
     * @error 14218
     * @return void
     * @throws Xapp_Rpc_Server_Exception
     */
    final public function setup()
    {
        if(xapp_is_option(self::OMIT_ERROR, $this))
        {
            $this->request()->set('OMIT_ERROR', true, 'RPC');
        }
        if($this->request()->is('service', 'GET'))
        {
            if(xapp_is_option(self::SERVICE_OVER_GET, $this))
            {
                $service = $this->request()->get('service', 'GET');
                if(xapp_get_option(self::NAMESPACE_IDENTIFIER, $this) === NAMESPACE_SEPARATOR && strpos($service, xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this)) !== false)
                {
                    $pattern = array('/', '-', '_', '.', ':', '#', '+', ',', ';');
                    if(($index = array_search(xapp_get_option(self::CLASS_METHOD_SEPARATOR, $this), $pattern)) !== false)
                    {
                        unset($pattern[$index]);
                    }
                    $service = preg_replace('=(\\'.implode('|\\', $pattern).')+=i', NAMESPACE_SEPARATOR, $service);
                    if(stripos($service, NAMESPACE_SEPARATOR) !== false)
                    {
                        $service = NAMESPACE_SEPARATOR . ltrim($service, NAMESPACE_SEPARATOR);
                    }
                }
                $this->_service = $service;
            }else{
                throw new Xapp_Rpc_Server_Exception(_("service over get is not supported by this rpc server"), 1421801);
            }
        }
        if(!xapp_get_option(self::ALLOW_BATCHED_REQUESTS, $this) && $this->request()->isPost() && !is_null($this->request()->getParam(0)))
        {
            throw new Xapp_Rpc_Server_Exception(_("batched requests are not allowed"), 1421802);
        }
	    $this->preHandle();
	    $this->init();
    }


    /**
     * factory class creates singleton instance of server implementation
     * defined by first parameter driver
     *
     * @error 14202
     * @param string $driver expects the server type, e.g. "json" to instantiate json server
     * @param null|array|object $options expects optional options array
     * @return Xapp_Rpc_Server
     * @throws Xapp_Rpc_Server_Exception
     */
    public static function factory($driver, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(strtolower(trim($driver)));
        if(class_exists($class, true))
        {
            return $class::instance($options);
        }else{
            throw new Xapp_Rpc_Server_Exception(xapp_sprintf(_("rpc server class: %s not found"), $class), 1420201);
        }
    }


    /**
     * register service to server. a service can by anything of the following:
     * - function (a user php function defined and existing)
     * - class (a user class)
     * - class.method (a single method of a class ignoring all others)
     * - directory (a directory too look for classes in)
     * - an object instance of a class
     *
     * the smd mapper will try to find all the above values to map. the most
     * clean way is to always use classes and omit certain methods using the
     * second parameter which can contain an array of class methods to ignore
     *
     * @error 14203
     * @param null|string|object $mixed expects any of the above values
     * @param null|array $ignore expects optional ignore method array
     * @return Xapp_Rpc_Server
     */
    public function register($mixed = null, $ignore = null)
    {
	    if(is_object($mixed))
        {
            $this->_objects[strtolower(get_class($mixed))] = $mixed;
        }
        $this->smd()->set($mixed, $ignore);
        return $this;
    }


    /**
     * unregister service class from smd map. only classes can be unregistered
     * individually - pass class name as object, class name string or "class.method"
     * dot notation. unregister everything if you leave first parameter to null. NOTE:
     * calling this function like $server->unregister() will unregister all services!
     *
     * @error 14217
     * @param null|string|object $class expects class as explained above
     * @return Xapp_Rpc_Server
     */
    public function unregister($class = null)
    {
        if(is_object($class))
        {
            $name = strtolower(get_class($class));
        }else{
            $name = $class;
        }
        if(array_key_exists($name, $this->_objects))
        {
            unset($this->_objects[strtolower(get_class($class))]);
        }
        $this->smd()->reset($class);
        return $this;
    }


    /**
     * add additional parameter instead of using class option directly set parameters
     * with this function. the parameter will be directly passed to the smd instance
     * if already set. the first parameter \$name can be either the name of additional
     * parameter or null which will create unnamed additional parameter with index starting
     * with 0. NOTE: use this function only after smd instance has been set, it is advised
     * to set additional parameters via class options
     *
     * @error 14204
     * @param null|string|integer $name expects optional name
     * @param string|array $type expects data type as string or array
     * @param bool $optional expects boolean value whether parameter is optional or not
     * @return Xapp_Rpc_Server
     */
    public function addParam($name = null, $type, $optional = false)
    {
        $smd = $this->smd();

        if($name !== null)
        {
            $param = array($name => array($type, $optional));
        }else{
            $param = array(array($type, $optional));
        }

        xapp_set_option(self::ADDITIONAL_PARAMETERS, $param, $this);
        if($smd !== null)
        {
            xapp_set_option(Xapp_Rpc_Smd::ADDITIONAL_PARAMETERS, $param, $smd);
        }
        return $this;
    }


    /**
     * setter/getter for smd instance. if you set smd instance not via class options
     * but at later stage you must make sure that smd instance has all options set required
     * to work with server instance since server instance passes options to smd instance
     * in class constructors
     *
     * @error 14205
     * @param Xapp_Rpc_Smd $smd expects smd instance when to set instance
     * @return null|Xapp_Rpc_Smd
     */
    public function smd(Xapp_Rpc_Smd $smd = null)
    {
        if($smd !== null)
        {
            xapp_set_option(self::SMD, $smd, $this);
        }
        return xapp_get_option(self::SMD, $this);
    }


    /**
     * setter/getter for cache instance. if you set cache instance not via class options but at a later
     * stage you must make sure the cache instance has all required options set
     *
     * @error 14220
     * @param Xapp_Cache_Driver $cache expects cache instance when to set instance
     * @return null|Xapp_Cache_Driver
     */
    public function cache(Xapp_Cache_Driver $cache = null)
    {
        if($cache !== null)
        {
            xapp_set_option(self::CACHE, $cache, $this);
        }
        return xapp_get_option(self::CACHE, $this);
    }


    /**
     * setter/getter for request instance. if you set request instance not via class options
     * but at later stage you must make sure that request instance has all options set required
     * to work with server instance since server instance passes options to request instance
     * in class constructors
     *
     * @error 14206
     * @param Xapp_Rpc_Request $request expects request instance when to set instance
     * @return null|Xapp_Rpc_Request
     */
    public function request(Xapp_Rpc_Request $request = null)
    {
        if($request !== null)
        {
            xapp_set_option(self::REQUEST, $request, $this);
        }
        return xapp_get_option(self::REQUEST, $this);
    }


    /**
     * setter/getter for response instance. if you set response instance not via class options
     * but at later stage you must make sure that response instance has all options set required
     * to work with server instance since server instance passes options to response instance
     * in class constructors
     *
     * @error 14207
     * @param Xapp_Rpc_Response $response expects request instance when to set instance
     * @return null|Xapp_Rpc_Response
     */
    public function response(Xapp_Rpc_Response $response = null)
    {
        if($response !== null)
        {
            xapp_set_option(self::RESPONSE, $response, $this);
        }
        return xapp_get_option(self::RESPONSE, $this);
    }


    /**
     * get all services as array request in request. can be multiple in batch request
     *
     * @error 14208
     * @return array
     */
    public function getServices()
    {
        return $this->_services;
    }


    /**
     * returns boolean value for whether any service has been set or not
     *
     * @error 14209
     * @return bool
     */
    public function hasServices()
    {
        return (!empty($this->_services)) ? true : false;
    }


    /**
     * check if service (class/method or function) in rpc requested calls exists either by checking against name or regex
     * or index when in batched request. will return either boolean false if not found or the service name as string
     * if found
     *
     * @error 14222
     * @param int|string $mixed expects either the index or service name to look for
     * @return bool|string
     */
    public function hasService($mixed)
    {
        if(is_int($mixed))
        {
            return (array_key_exists($mixed, $this->_services)) ? $this->_services[$mixed] : false;
        }else{
            foreach($this->_services as $service)
            {
                if(stristr($service, $mixed) !== false || (bool)@preg_match("/".xapp_regex_delimit($mixed)."/i", $service) !== false)
                {
                    return $service;
                }
            }
        }
        return false;
    }


    /**
     * get service name of (class/method or function) in rpc requested calls either by checking against name or regex or
     * index when in batched request. returns default value on second argument if first parameter does not produce a
     * service
     *
     * @error 14223
     * @param int|string $mixed expects either the index or service name to look for
     * @param null|mixed $default [optional] expects default return value
     * @return null|mixed
     */
    public function getService($mixed, $default = null)
    {
        if(($service = $this->hasService($mixed)) !== false)
        {
            return $service;
        }else{
            return $default;
        }
    }


    /**
     * get all calls from request. can be multiple in batch request
     *
     * @error 14210
     * @return array
     */
    public function getCalls()
    {
        return $this->_calls;
    }


    /**
     * returns boolean value for whether any call has been set or not
     *
     * @error 14221
     * @return bool
     */
    public function hasCalls()
    {
        return (!empty($this->_calls)) ? true : false;
    }


    /**
     * check if called service is a php class and if already set
     *
     * @error 14225
     */
    public function hasClass()
    {
       return ($this->_class !== null) ? true : false;
    }


    /**
     * get php class instance of called service
     *
     * @error 14224
     * @return null|object
     */
    public function getClass()
    {
        return $this->_class;
    }


    /**
     * invoke class/method or function via rpc or outside rpc functionality for testing purposes. the first parameter
     * thus can be a valid callable when used outside of rpc service or the internal array containing all call parameters
     * from concrete rpc server implementation. throws exception with extended exception properties if in debug mode.
     * summarizes all caught applications exceptions when invoking callable to a general application error so sensitive
     * error messages can be omitted. when invoked with named parameters will reorder parameters to reflect
     * order of method/function call since call_user_func_array needs to pass parameter named or unnamed in
     * correct order
     *
     * @error 14211
     * @param array|callable $call expects either valid callable or array with all call parameters
     * @param array $params expects the parameters to pass to function/method to invoke
     * @return mixed
     * @throws Xapp_Rpc_Server_Exception
     * @throws Xapp_Rpc_Fault
     * @throws Exception
     */
    public function invoke($call, $params = null)
    {
        $key = null;
        $hash = null;
        $class = null;
        $return = null;
        $result = null;
        $callable = null;

        try
        {
            //invoke from outside of rpc service
            if(is_callable($call))
            {
                $callable = $call;
                if(is_array($call))
                {
                    $call = array(null, $call[1], ((is_object($call[0])) ? get_class($call[0]) : $call[0]));
                    $class = new ReflectionClass($call[2]);
                }else if(strpos((string)$call, '::') !== false){
                    $call = array(null, substr($call, strpos($call, '::') + 2), substr($call, 0, strpos($call, '::')));
                    $class = new ReflectionClass($call[2]);
                }else{
                    $call = array(null, $call, $call);
                    $class = null;
                }
            }
            //invoke from inside rpc service
            else if(is_array($call))
            {
                if($call[2] !== null)
                {
                    if(xapp_get_option(self::NAMESPACE_IDENTIFIER, $this) === NAMESPACE_SEPARATOR)
                    {
                        if(strpos($call[2], NAMESPACE_SEPARATOR) !== false)
                        {
                            $call[2] = NAMESPACE_SEPARATOR . str_replace(array('\\', '/', '_', '.'), xapp_get_option(self::NAMESPACE_IDENTIFIER, $this), trim($call[2], ' ' . NAMESPACE_SEPARATOR));
                        }else{
                            $call[2] = str_replace(array('/', '_', '.'), xapp_get_option(self::NAMESPACE_IDENTIFIER, $this), trim($call[2]));
                        }
                    }else{
                        $call[2] = str_replace(array('\\', '/', '_', '.'), xapp_get_option(self::NAMESPACE_IDENTIFIER, $this), trim($call[2]));
                    }
                    $key = "{$call[2]}.{$call[1]}";
                    try
                    {
                        if(array_key_exists(strtolower($call[2]), $this->_objects))
                        {
                            $class = new ReflectionClass($this->_objects[strtolower($call[2])]);
                        }else{
                            $class = new ReflectionClass($call[2]);
                        }
                        if($class->hasMethod($call[1]))
                        {
                            $method = $class->getMethod($call[1]);
                            if($method->isPublic())
                            {
                                if($method->isStatic())
                                {
                                    $callable = array($class->getName(), $call[1]);
                                }else{
                                    $callable = array(((array_key_exists(strtolower($call[2]), $this->_objects)) ? $this->_objects[strtolower($call[2])] : $class->newInstance()), $call[1]);
                                }
                            }else{
                                Xapp_Rpc_Fault::t("method: {$call[1]} of class: {$call[2]} is not public", array(1421105, -32601));
                            }
                        }else{
                            Xapp_Rpc_Fault::t("method: {$call[1]} of class: {$call[2]} does not exist", array(1421104, -32601));
                        }
                    }
                    catch(ReflectionException $e)
                    {
                        throw new Xapp_Rpc_Server_Exception(xapp_sprintf(_("unable to initialize class due to reflection error: %d, %s"), $e->getCode(), $e->getMessage()), 1421103);
                    }
                }else{
                    $key = $call[1];
                    $callable = $call[1];
                }
            }else{
                throw new Xapp_Rpc_Server_Exception(_("invalid callable passed to invoke method"), 1421106);
            }
            if(is_callable($callable))
            {
                if(is_array($callable) && is_object($callable[0]))
                {
                    $this->_class = $callable[0];
                }
                if(!is_null($key) && !is_null($params) && array_values((array)$params) !== (array)$params)
                {
                    $tmp = array();
                    $arr = (array)$params;
                    $map = $this->smd()->get($key);
                    if(!is_null($map))
                    {
                        foreach((array)$map->parameters as $p)
                        {
                            if(array_key_exists($p->name, $arr))
                            {
                                $tmp[$p->name] = $arr[$p->name];
                            }
                        }
                    }
                    $params = $tmp;
                    $tmp = null;
                    $arr = null;
                    $map = null;
                }
                if(!is_null($key) && xapp_is_option(self::CACHE, $this) && preg_match(Xapp_Rpc::regex(xapp_get_option(self::CACHE_SERVICES, $this)), $call[0]))
                {
                    if(xapp_get_option(self::CACHE_BY_TRANSACTION_ID, $this))
                    {
                        $hash = $call[3]['id'];
                    }else{
                        $hash = Xapp_Cache::hash(serialize($call[0]) . serialize($params));
                    }
                    if(xapp_get_option(self::CACHE, $this)->has($hash))
                    {
                        return xapp_get_option(self::CACHE, $this)->get($hash);
                    }
                }
                if(!xapp_get_option(self::PARAMS_AS_ARRAY, $this))
                {
                    $params = xapp_array_to_object($params);
                }
                xapp_event('xapp.rpc.server.beforeCall', array($this, array(&$result, $call[1], $call[2], $params)));
                if($this->hasClass() && $class->implementsInterface('Xapp_Rpc_Interface_Callable'))
                {
                    $return = $this->getClass()->onBeforeCall($this, array(&$result, $call[1], $call[2], &$params));
                }
                if($return !== false && $result === null)
                {
                    $this->response()->result($result = call_user_func_array($callable, (array)$params));
                }
                if($return === false && $this->hasClass() && $class->implementsInterface('Xapp_Rpc_Interface_Callable'))
                {
                    $return = $this->getClass()->onAbort($this, array(&$result, $call[1], $call[2], &$params));
                }else{
                    $return = null;
                }
                if($return !== null)
                {
                    $result = $return;
                }
                if($this->hasClass() && $class->implementsInterface('Xapp_Rpc_Interface_Callable'))
                {
                    $return = $this->getClass()->onAfterCall($this, array(&$result));
                }
                if($return !== null)
                {
                    $result = $return;
                }
                xapp_event('xapp.rpc.server.afterCall', array($this, array(&$result)));
                if(!is_null($hash))
                {
                    if(!xapp_get_option(self::CACHE, $this)->has($hash))
                    {
                        xapp_get_option(self::CACHE, $this)->set($hash, $result);
                    }
                }

                $callable = null;
                $return = null;
                $params = null;
                $class = null;
                $hash = null;
                return $result;
            }else{
                throw new Xapp_Rpc_Server_Exception(_("unable to invoke function since first argument is not a callable"), 1421102);
            }
        }
        catch(Exception $e)
        {
            if($this->hasClass() && $class->implementsInterface('Xapp_Rpc_Interface_Callable'))
            {
                $error = $this->getClass()->onError($this, $e);
                if($error instanceof Exception)
                {
                    $e = $error;
                    $error = null;
                }
            }
            if(!($e instanceof Xapp_Rpc_Server_Exception))
            {
                $data = array();
                $debug = xapp_get_option(self::DEBUG, $this);
                if((is_bool($debug) && $debug === true) || (is_int($debug) && $debug === 1))
                {
                    $data['message'] = $e->getMessage();
                    $data['code'] = $e->getCode();
                    $data['class'] = get_class($e);
                    $data['file'] = $e->getFile();
                    $data['line'] = $e->getLine();
                    if($e instanceof ErrorException)
                    {
                        $data['severity'] = $e->getSeverity();
                    }
                    if($e instanceof Xapp_Rpc_Fault && $e->hasData())
                    {
                        $data['data'] = $e->getData();
                    }
                }else if(is_int($debug) && $debug === 2){
                    $data['message'] = $e->getMessage();
                    $data['code'] = $e->getCode();
                    if($e instanceof ErrorException)
                    {
                        $data['severity'] = $e->getSeverity();
                    }
                    if($e instanceof Xapp_Rpc_Fault && $e->hasData())
                    {
                        $data['data'] = $e->getData();
                    }
                }
                if(xapp_is_option(self::APPLICATION_ERROR, $this))
                {
                    if($e instanceof Xapp_Rpc_Fault && $e->hasFault())
                    {
                        $f = $e->getFault();
                    }else{
                        $f = -32500;
                    }
                    if(($code = (int)$e->getCode()) !== 0)
                    {
                        Xapp_Rpc_Fault::t(xapp_sprintf("application error: %d", array($code)), array(1421101, $f), XAPP_ERROR_IGNORE, $data);
                    }else{
                        Xapp_Rpc_Fault::t("application error", array(1421101, $f), XAPP_ERROR_IGNORE, $data);
                    }
                }else{
                    if((bool)$debug)
                    {
                        Xapp_Rpc_Fault::t($e->getMessage(), $e->getCode(), XAPP_ERROR_IGNORE, $data);
                    }else{
                        throw $e;
                    }
                }
            }else{
                throw $e;
            }
        }
        return null;
    }


    /**
     * pre handle function gets called before the concrete server implementation
     * server handlers are called in succession
     *
     * @error 14212
     * @return void
     */
    protected function preHandle()
    {

    }


    /**
     * handles the request, executes service and flushes result to output stream.
     * nothing will happen unless this function is called! if the server was called
     * with GET and not GET parameters are set will flush smd directly
     *
     * @error 14213
     * @return void
     * @throws Xapp_Rpc_Fault
     */
    final public function handle()
    {
        xapp_debug('rpc server handler started', 'rpc');
        xapp_event('xapp.rpc.server.handle', array(&$this));

	    if($this->request()->isGet() && $this->request()->getParam('view') ==='rpc')
        {
	        $this->response()->body($this->smd()->compile());
            $this->response()->flush();
        }else{
		    if($this->hasCalls())
            {
	            foreach($this->_calls as $call)
                {
	                $this->execute($call);
                }
                $this->flush();
            }else{
                Xapp_Rpc_Fault::t("request is empty or does not contain any rpc action", array(1421301, -32600), XAPP_ERROR_IGNORE);
            }
        }

        xapp_debug('rpc server handler stopped', 'rpc');
    }


    /**
     * post handle function gets called after the concrete server implementation
     * server handlers are called in succession
     *
     * @error 14214
     * @return void
     */
    protected function postHandle()
    {

    }


    /**
     * map rpc faults by looking if faultMap exists a property that contains a mapping of
     * generic rpc fault codes to concrete server implementation fault codes
     *
     * @error 14215
     * @param int $code expects the fault code to map
     * @return int
     */
    protected function getFault($code)
    {
        if(xapp_property_exists($this, 'faultMap') && array_key_exists((int)$code, $this->faultMap))
        {
            return (int)$this->faultMap[(int)$code];
        }else{
            return (int)$code;
        }
    }


    /**
     * server teardown method will shutdown concrete server implementation and call post
     * service handler after successful shutdown. this method also fires teardown event after response has been flushed
     * by calling xapp event with event name xapp.rpc.server.teardown and also will look for "onTeardown" method in
     * called class and pass server instance if found
     *
     * @error 14219
     * @return void
     */
    final public function teardown()
    {
        xapp_event('xapp.rpc.server.teardown', array(&$this));

        if($this->hasClass() && method_exists($this->getClass(), 'onTeardown'))
        {
            $this->getClass()->onTeardown($this);
        }

        $this->shutdown();
        $this->postHandle();
        exit;
    }


    /**
     * prevent cloning
     *
     * @error 14216
     * @return void
     */
    protected function __clone(){}


    /**
     * instead of calling handle method do echo $server to execute server
     *
     * @error 14217
     * @return void
     */
    public function __toString()
    {
        $this->handle();
    }
}