<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Rpc.Request.Exception');


/**
 * Rpc request class
 *
 * @package Rpc
 * @class Xapp_Rpc_Request
 * @error 144
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Rpc_Request
{
    /**
     * global variable constant for params
     *
     * @const PARAMS
     */
    const PARAMS            = 'PARAMS';

    /**
     * global variable constant for rpc
     *
     * @const RPC
     */
    const RPC               = 'RPC';

    /**
     * global variable constant for post
     *
     * @const POST
     */
    const POST              = 'POST';

    /**
     * global variable constant for get
     *
     * @const GET
     */
    const GET               = 'GET';

    /**
     * global variable constant for cookie
     *
     * @const COOKIE
     */
    const COOKIE            = 'COOKIE';

    /**
     * global variable constant for server
     *
     * @const SERVER
     */
    const SERVER            = 'SERVER';


    /**
     * contains normalized full url of request
     *
     * @var null|string
     */
    protected $_url = null;

    /**
     * contains raw request data retrieved by php://input
     *
     * @var null|string
     */
    protected $_raw = null;

    /**
     * contains all concrete parameters found in request which can be found in
     * post, get or cookie array
     *
     * @var array
     */
    protected $_params = array();

    /**
     * contains the charset found in header
     *
     * @var null|string
     */
    protected $_charset = null;

    /**
     * contains the request content type found in header
     *
     * @var null|string
     */
    protected $_contentType = null;

    /**
     * contains the request content length if found in header
     *
     * @var null|string
     */
    protected $_contentLength = null;


    /**
     * decodes data
     *
     * @param $data
     * @return mixed
     */
    abstract public function decode($data);


    /**
     * class constructor must be called from child class to initialize request setting
     * global rpc array and calling init function
     *
     * @error 14401
     */
    protected function __construct()
    {
        if(!isset($GLOBALS['_RPC']))
        {
            $GLOBALS['_RPC'] = array();
        }
        $this->init();
    }


    /**
     * factory method to create new request instance defined by first parameter driver
     * which must contain a string with the driver name, e.g. "json"
     *
     * @error 14402
     * @param string $driver expects the driver name
     * @param null|array|object $options expects optional class options
     * @return Xapp_Rpc_Request
     * @throws Xapp_Rpc_Request_Exception
     */
    public static function factory($driver, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(strtolower(trim($driver)));
        if(class_exists($class, true))
        {
           return new $class($options);
        }else{
            throw new Xapp_Rpc_Request_Exception(xapp_sprintf(_("rpc request class: %s not found"), $class), 1440201);
        }
    }


    /**
     * init request if not has been initialized before trying to get charset, content type and
     * content length from headers as well as setting request url
     *
     * @error 14403
     * @return void
     */
    protected function init()
    {
        if($this->isPost())
        {
            if($this->hasPost())
            {
                $this->_params = (array)$this->decode($this->getPost());
            }else{
                $this->_params = (array)$this->decode($this->getRaw());
            }
        }
        if($this->isGet())
        {
            $this->_params = array_merge((array)$this->_params, (array)$this->getGet());
        }
        if($this->_url === null)
        {
            $this->_url = self::url();
            if(isset($_SERVER['CONTENT_TYPE']) && preg_match('/^([^\;]+)\;?\s?(?:charset\=(.+))?$/i', trim($_SERVER['CONTENT_TYPE']), $m))
            {
                $this->_contentType = strtolower(trim($m[1]));
                if(isset($m[2]))
                {
                    $this->_charset = strtolower(trim($m[2]));
                }
            }
            if(isset($_SERVER['CONTENT_LENGTH']) && !empty($_SERVER['CONTENT_LENGTH']))
            {
                $this->_contentLength = (int)$_SERVER['CONTENT_LENGTH'];
            }
        }

    }


    /**
     * get raw php post input string
     *
     * @error 14404
     * @return string
     */
    public function getRaw()
    {
        if($this->_raw === null)
        {
            $this->_raw = trim((string)file_get_contents('php://input'));
        }
        return $this->_raw;
    }


    /**
     * get parameter from parameter array containing the parameters from post and get together
     * and merged. if parameter is not found will return default value passed in second
     * parameter
     *
     * @error 14405
     * @param string $name expects parameter name
     * @param null|mixed $default expects default return value
     * @return array|mixed|null
     */
    public function getParam($name, $default = null)
    {
        return xapp_array_get($this->_params, $name, $default);
    }


    /**
     * returns all merge post and get parameters from request
     *
     * @error 14406
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }


    /**
     * checks if a parameter exist in post get merge parameter array by passing
     * parameter name in first argument. if first argument is empty will check
     * if anything is set in parameter array. if second parameter is set to true
     * will also check if parameter value is a value
     *
     * @error 14407
     * @param null|string $name expects the optional parameter name
     * @param bool $strict expects boolean value on whether to check strict or not
     * @return bool
     */
    public function hasParam($name = null, $strict = false)
    {
        if($name !== null)
        {
            return xapp_array_isset($this->_params, $name, $strict);
        }else{
            return (!empty($this->_params)) ? true : false;
        }
    }


    /**
     * check whether any parameters/data exists in request body and returns boolean
     * value
     *
     * @error 14433
     * @return bool
     */
    public function hasParams()
    {
        return $this->hasParam();
    }


    /**
     * checks if a parameter exist in post get merged param array with strict mode
     *
     * @error 14429
     * @see Xapp_Rpc_Request::hasParam
     * @param null|string $name expects the optional parameter name
     * @return bool
     */
    public function isParam($name)
    {
        return $this->hasParam($name, true);
    }


    /**
     * get value from all global data array associated with request class. the second parameter
     * contains the scope string from where to look for parameter which are:
     *
     * params = the merged post and get parameters
     * rpc = global rpc array created by request
     * post = the php post array
     * get = the php get array
     * cookie = the php cookie array
     * server = the php server array
     *
     * will return default value passed in third argument if parameter is not found
     *
     * @error 14410
     * @param string $name expects the parameter name
     * @param string $from expects
     * @param null $default expects default return value
     * @return null|mixed
     * @throws Xapp_Rpc_Request_Exception
     */
    public function getFrom($name, $from = self::PARAMS, $default = null)
    {
        $from = strtoupper(trim($from));

        switch($from)
        {
            case self::PARAMS:
                $return = xapp_array_get($this->_params, $name, $default);
                break;
            case self::RPC:
                $return = ((isset($GLOBALS['_RPC']) && array_key_exists($name, $GLOBALS['_RPC'])) ? $GLOBALS['_RPC'][$name] : $default);
                break;
            case self::POST:
                $return = ((array_key_exists($name, $_POST)) ? $_POST[$name] : $default);
                break;
            case self::GET:
                $return = ((array_key_exists($name, $_GET)) ? $_GET[$name] : $default);
                break;
            case self::COOKIE:
                $return = ((array_key_exists($name, $_COOKIE)) ? $_COOKIE[$name] : $default);
                break;
            case self::SERVER:
                $return = ((array_key_exists($name, $_SERVER)) ? $_SERVER[$name] : $default);
                break;
            default:
                throw new Xapp_Rpc_Request_Exception(xapp_sprintf(_("request variable scope: %s does not exist"), $from), 1441001);
        }
        return $return;
    }


    /**
     * get value from global variable scopes as defined in Xapp_Rpc_Request::getFrom will
     * look in each scope / global array and if not found in any of them will return default
     * value defined in third parameter. if second parameter is not null but variable scope
     * constant name will redirect to getFrom function to get value
     *
     * @error 14411
     * @see Xapp_Rpc_Request::getFrom
     * @param string $name expects parameter name
     * @param string $from expects
     * @param null $default expects default return value
     * @return null|mixed
     */
    public function get($name, $from = null, $default = null)
    {
        $return = $default;

        if($from !== null)
        {
            return $this->getFrom($name, $from, $default);
        }

        switch(true)
        {
            case xapp_array_isset($this->_params, $name):
                $return = xapp_array_get($this->_params, $name, $default);
                break;
            case (isset($GLOBALS['_RPC']) && array_key_exists($name, $GLOBALS['_RPC'])):
                $return = $GLOBALS['_RPC'][$name];
                break;
            case array_key_exists($name, $_POST):
                $return = $_POST[$name];
                break;
            case array_key_exists($name, $_GET):
                $return = $_GET[$name];
                break;
            case array_key_exists($name, $_COOKIE):
                $return = $_COOKIE[$name];
                break;
            case array_key_exists($name, $_SERVER):
                $return = $_SERVER[$name];
                break;
        }
        return $return;
    }


    /**
     * checks if the parameter passed in first argument exists in global arrays. if the second argument
     * is set will check only in global array with scope passed in this argument. e.g. "POST" will only
     * check in post array if parameter exists. if second parameter is not set will bubble through all
     * global variables until found in one of them or returns false if not found. the third parameter
     * activates the strict mode to check if value is a value
     *
     * @error 14412
     * @param string $name expects the parameter name
     * @param null|string $from expects optional scope value like "POST"
     * @param bool $strict expects boolean value whether to activate strict value check
     * @return bool
     */
    public function has($name, $from = null, $strict = false)
    {
        if($from === null)
        {
            $from = true;
        }
        switch($from)
        {
            case self::PARAMS:
                $return = xapp_array_isset($this->_params, $name, $strict);
                if($return) return true;
                if($from !== true) break;
            case self::RPC:
                if(isset($GLOBALS['_RPC']))
                {
                    $return = ((bool)$strict) ? (bool)(array_key_exists($name, $GLOBALS['_RPC']) && xapp_is_value($GLOBALS['_RPC'][$name])) : array_key_exists($name, $GLOBALS['_RPC']);
                }else{
                    $return = false;
                }
                if($return) return true;
                if($from !== true) break;
            case self::POST:
                $return = ((bool)$strict) ? (bool)(array_key_exists($name, $_POST) && xapp_is_value($_POST[$name])) : array_key_exists($name, $_POST);
                if($return) return true;
                if($from !== true) break;
            case self::GET:
                $return = ((bool)$strict) ? (bool)(array_key_exists($name, $_GET) && xapp_is_value($_GET[$name])) : array_key_exists($name, $_GET);
                if($return) return true;
                if($from !== true) break;
            case self::COOKIE:
                $return = ((bool)$strict) ? (bool)(array_key_exists($name, $_COOKIE) && xapp_is_value($_COOKIE[$name])) : array_key_exists($name, $_COOKIE);
                if($return) return true;
                if($from !== true) break;
            case self::SERVER:
                $return = ((bool)$strict) ? (bool)(array_key_exists($name, $_SERVER) && xapp_is_value($_SERVER[$name])) : array_key_exists($name, $_SERVER);
                if($return) return true;
                if($from !== true) break;
            default:
                return false;
        }
        return false;
    }


    /**
     * does the same as has method but always checking in strict mode
     *
     * @error 14413
     * @see Xapp_Rpc_Request::has
     * @param string $name expects the parameter name
     * @param null|string $from expects optional scope value like "POST"
     * @return bool
     */
    public function is($name, $from = null)
    {
        return $this->has($name, $from, true);
    }


    /**
     * set parameter to global scope array by passing parameter in first
     * argument, value in second and destination string as third parameter. the
     * the third value defines to which global variable to write to e.g. "POST"
     *
     * @error 14414
     * @param string $name expects the parameter name
     * @param null $value exepects the value for parameter
     * @param string $to expects optional scope value like "POST"
     * @return Xapp_Rpc_Request
     * @throws Xapp_Rpc_Request_Exception
     */
    public function set($name, $value = null, $to = self::PARAMS)
    {
        $to = strtoupper(trim($to));

        switch($to)
        {
            case self::PARAMS:
                xapp_array_set($this->_params, $name, $value);
                break;
            case self::RPC:
                $GLOBALS['_RPC'][$name] = $value;
                break;
            case self::POST:
                $_POST[$name] = $value;
                break;
            case self::GET:
                $_GET[$name] = $value;
                break;
            case self::COOKIE:
                $_COOKIE[$name] = $value;
                break;
            default:
                throw new Xapp_Rpc_Request_Exception(xapp_sprintf(_("request variable scope: %s does not exist"), $to), 1441401);
        }
        return $this;
    }


    /**
     * returns whether request is a https ssl secure request or not
     *
     * @error 14414
     * @return bool
     */
    public function isHttps()
    {
        return (self::getScheme() === 'HTTPS') ? true : false;
    }


    /**
     * check whether request is post request
     *
     * @error 14430
     * @return bool
     */
    public function isPost()
    {
        return (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post') ? true : false;
    }


    /**
     * check whether request is get request
     *
     * @error 14431
     * @return bool
     */
    public function isGet()
    {
        return (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'get') ? true : false;
    }


    /**
     * returns php post array
     *
     * @error 14408
     * @return mixed
     */
    public function getPost()
    {
        return $_POST;
    }


    /**
     * returns php get array
     *
     * @error 14409
     * @return mixed
     */
    public function getGet()
    {
        return $_GET;
    }


    /**
     * check if request is post and post variable has any value
     *
     * @error 14434
     * @return bool
     */
    public function hasPost()
    {
        return ($this->isPost() && !empty($_POST)) ? true : false;
    }


    /**
     * check if request is get and get variable has any value
     *
     * @error 14435
     * @return bool
     */
    public function hasGet()
    {
        return ($this->isGet() && !empty($_GET)) ? true : false;
    }


    /**
     * get url scheme from request
     *
     * @error 14414
     * @return string
     */
    public static function getScheme()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            $https = $_SERVER['HTTPS'];
            if(!empty($https) && strtolower(trim($https)) === "on")
            {
                return 'HTTPS';
            }else{
                return 'HTTP';
            }
        }else{
            return 'CLI';
        }
    }


    /**
     * get host name from request. returns null if not found
     *
     * @error 14415
     * @return null|string
     */
    public static function getHost()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            $host = $_SERVER['HTTP_HOST'];
            if(!empty($host))
            {
                return $host;
            }
            $host = $_SERVER['SERVER_NAME'];
            if(!empty($host))
            {
                return $host;
            }
        }
        return null;
    }


    /**
     * get server port from request. returns null if not found
     *
     * @error 14416
     * @return int|null
     */
    public static function getPort()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            $port = $_SERVER['SERVER_PORT'];
            if(!empty($port))
            {
                return (int)$port;
            }
        }
        return null;
    }


    /**
     * get the server referer from request. returns null if not found
     *
     * @error 14417
     * @return null|string
     */
    public static function getReferer()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            if(getenv('HTTP_ORIGIN') && strcasecmp(getenv('HTTP_ORIGIN'), 'unknown'))
            {
                $ref = getenv('HTTP_ORIGIN');
            }
            else if(isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] && strcasecmp($_SERVER['HTTP_ORIGIN'], 'unknown'))
            {
                $ref = $_SERVER['HTTP_ORIGIN'];
            }
            else if(getenv('HTTP_REFERER') && strcasecmp(getenv('HTTP_REFERER'), 'unknown'))
            {
                $ref = getenv('HTTP_REFERER');
            }
            else if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && strcasecmp($_SERVER['HTTP_REFERER'], 'unknown'))
            {
                $ref = $_SERVER['HTTP_REFERER'];
            }else{
                $ref = false;
            }
            if($ref !== false && !empty($ref))
            {
                if(($host = parse_url($ref, PHP_URL_HOST)) !== false)
                {
                    return trim($host);
                }
            }
        }
        return null;
    }


    /**
     * get the server ip from request. returns null if not possible
     *
     * @error 14418
     * @return mixed|null|string
     */
    public static function getServerIp()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            $ip = $_SERVER['SERVER_ADDR'];
            if(!empty($ip))
            {
                return $ip;
            }
            if(!empty($HTTP_SERVER_VARS) && !empty($HTTP_SERVER_VARS['SERVER_ADDR']))
            {
                return $HTTP_SERVER_VARS['SERVER_ADDR'];
            }
            $ip = gethostbyname($_SERVER['SERVER_NAME']);
            if((bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
            {
                return $ip;
            }
            $ip = $_SERVER['LOCAL_ADDR'];
            if(!empty($ip))
            {
                return $ip;
            }
        }
        return null;
    }


    /**
     * get the client ip from request. returns null if not possible
     *
     * @error 14419
     * @return mixed|null
     */
    public static function getClientIp()
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            if(isset($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], "unknown"))
            {
               return $_SERVER['HTTP_CLIENT_IP'];
            }
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], "unknown"))
            {
               return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            if(!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            {
               return $_SERVER['REMOTE_ADDR'];
            }
        }
        return null;
    }


    /**
     * get component from url as you would do with phps native function parse url expecting
     * the component flag in second argument which also cab be -1 returning all components as array.
     * if first parameter is not set will get url from current request. the url will not include
     * everything after the path variable
     *
     * @error 14420
     * @param null|string $url expects the optional url to parse
     * @param null|int $component expects optional php parse_url component flag
     * @return null|string|array
     * @throws Xapp_Rpc_Request_Exception
     */
    public static function url($url = null, $component = null)
    {
        $tmp = array();

        if($url === null)
        {
            if(strtolower(php_sapi_name()) !== 'cli')
            {
                if((int)$_SERVER['SERVER_PORT'] === 443 || (!empty($_SERVER['HTTPS']) && strtolower(trim($_SERVER['HTTPS'])) == 'on'))
                {
                    $tmp[] = 'https://';
                }else{
                    $tmp[] = 'http://';
                }
                if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
                {
                    $tmp[] = $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] . '@';
                }
                if(!empty($_SERVER['SERVER_NAME']))
                {
                    $tmp[] = $_SERVER['SERVER_NAME'];
                }else{
                    $tmp[] = $_SERVER['HTTP_HOST'];
                }
                if(isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] !== 80)
                {
                    $tmp[] = ':' . (int)$_SERVER['SERVER_PORT'];
                }
                $tmp[] = '/' . ltrim($_SERVER['REQUEST_URI'], '/ ');
            }
            $url = implode('', $tmp);
        }

        if($component !== null)
        {
            if(($u = parse_url($url, (int)$component)) !== false)
            {
                return $u;
            }else{
                throw new Xapp_Rpc_Request_Exception(xapp_sprintf(_("url: %s is not a valid url"), $url), 1442001);
            }
        }else{
            return $url;
        }
    }


    /**
     * return charset if set if not returns null
     *
     * @error 14421
     * @return null|string
     */
    public function getCharset()
    {
        return $this->_charset;
    }


    /**
     * return content type if set if not returns null
     *
     * @error 14422
     * @return null|string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }


    /**
     * return content length if set if not returns null
     *
     * @error 14423
     * @return null|string
     */
    public function getContentLength()
    {
        return $this->_contentLength;
    }


    /**
     * tries to get a parameter value from all global arrays throwing exception if not found
     *
     * @error 14424
     * @param string $name expects the parameter name
     * @return mixed|null
     * @throws Xapp_Rpc_Request_Exception
     */
    public function __get($name)
    {
        if($this->has($name))
        {
            return $this->get($name);
        }else{
            throw new Xapp_Rpc_Request_Exception(_("overloading properties only allowed for registered global variables"), 1442401);
        }
    }


    /**
     * overload class setter set parameter to parameter array which contains parameter from $_POST
     * and $_GET. it is not possible to set parameters to other scopes via overloading
     *
     * @error 14425
     * @param string $name expects the parameter name
     * @param null $value expects the parameter value
     * @return Xapp_Rpc_Request
     */
    public function __set($name, $value = null)
    {
        return $this->set($name, $value, self::PARAMS);
    }


    /**
     * overload by testing if parameter is set looking for property name in parameter array from $_POST
     * and $_GET array. it is not possible to test for parameters of other scopes via overloading
     *
     * @error 14426
     * @param string $name expects the parameter name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name, self::PARAMS);
    }


    /**
     * overload by setting/getting parameter from global scope variables/arrays. in getter mode will
     * check in all global variables - in set mode will set parameter to parameter array which is the
     * array from $_POST and $_GET parameters. will throw exception in getter mode if parameter does not
     * exist
     *
     * @error 14427
     * @param string $name expects the parameter name
     * @param array $arguments expects parameter value in argument array at index 0
     * @return mixed|null|Xapp_Rpc_Request
     * @throws Xapp_Rpc_Request_Exception
     */
    public function __call($name, Array $arguments = array())
    {
        if(empty($arguments))
        {
            if($this->has($name))
            {
                throw new Xapp_Rpc_Request_Exception(_("overloading methods only allowed for registered global variables"), 1442701);
            }else{
                return $this->get($name);
            }
        }else{
            return $this->set($name, $arguments[0], self::PARAMS);
        }
    }


    /**
     * overload static method only allowed for parameters to be found in global $_SERVER or $_RPC array.
     * call like Xapp_Rpc_Request::HTTP_HOST(). if not found will return null if trying to set variable
     * throws exception
     *
     * @error 14432
     * @param string $name expects the method name alias global var parameter name
     * @param array $arguments expects setter arguments
     * @return null|mixed
     * @throws Xapp_Rpc_Request_Exception
     */
    public static function __callStatic($name, Array $arguments = array())
    {
        if(empty($arguments))
        {
            $name = strtoupper(trim($name));
            if(array_key_exists($name, $_SERVER))
            {
                return $_SERVER[$name];
            }else if(isset($GLOBALS['_RPC']) && array_key_exists($name, $GLOBALS['_RPC'])){
                return $GLOBALS['_RPC'][$name];
            }else{
                return null;
            }
        }else{
            throw new Xapp_Rpc_Request_Exception(("overloading static method only allowed to access global array parameters from _SERVER and _RPC"), 1443201);
        }
    }


    /**
     * on string casting return raw input
     *
     * @error 14428
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getRaw();
    }
}