<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');


xapp_import('xapp.Rpc.Gateway.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Server');
xapp_import('xapp.Rpc.Request');
xapp_import('xapp.Rpc.Response');

/**
 * Rpc gateway class
 *
 * @package Rpc
 * @class Xapp_Rpc_Gateway
 * @error 140
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Gateway implements Xapp_Singleton_Interface
{
    /**
     * defines whether readable error messages are omitted in error object or not
     *
     * @const OMIT_ERROR
     */
    const OMIT_ERROR                    = 'RPC_GATEWAY_OMIT_ERROR';

    /**
     * array of ip´s that are allowed to request denying all others
     *
     * @const ALLOW_IP
     */
    const ALLOW_IP                      = 'RPC_GATEWAY_ALLOW_IP';

    /**
     * array of ip´s that are always blocked from service
     *
     * @const DENY_IP
     */
    const DENY_IP                       = 'RPC_GATEWAY_DENY_IP';

    /**
     * array of username and password set as array key 0 and 1 to activate basic auth
     *
     * @const BASIC_AUTH
     */
    const BASIC_AUTH                    = 'RPC_GATEWAY_BASIC_AUTH';

    /**
     * disable gateway itself not servicing any requests
     *
     * @const DISABLE
     */
    const DISABLE                       = 'RPC_GATEWAY_DISABLE';

    /**
     * array of services to disable. array must contain either full service name
     * or valid preg regex pattern without pattern delimiters. the regex string will
     * be placed inside the pattern like: '=^(' . implode('|', $services) . ')=i';
     *
     * @const DISABLE_SERVICE
     */
    const DISABLE_SERVICE               = 'RPC_GATEWAY_DISABLE_SERVICE';

    /**
     * array of host names, without scheme, to allow and block all others. the host
     * name must be the same that will be found in request headers like foo.com
     *
     * @const ALLOW_HOST
     */
    const ALLOW_HOST                    = 'RPC_GATEWAY_ALLOW_HOST';

    /**
     * array of host names to always block from service. host name must be without
     * scheme, e.g. foo.com
     *
     * @const DENY_HOST
     */
    const DENY_HOST                     = 'RPC_GATEWAY_DENY_HOST';

    /**
     * boolean value to define whether to deny service when not called through HTTPS
     *
     * @const FORCE_HTTPS
     */
    const FORCE_HTTPS                   = 'RPC_GATEWAY_FORCE_HTTPS';

    /**
     * array of user agents to allow service to an block all others. values must be
     * regex conform expressions or simple values. if you want to make sure you want
     * to block exact name pass as ^name$ or use wildcard patterns .* or value as it
     * is which will equal to a /value/i regex expression. NOTE: if you use plain
     * value like google for example all other agent names like googlebot,
     * googlesearch, .. are also blocked
     *
     * @const ALLOW_USER_AGENT
     */
    const ALLOW_USER_AGENT              = 'RPC_GATEWAY_ALLOW_USER_AGENT';

    /**
     * array of user agents to always deny service of - see explanations for
     * ALLOW_USER_AGENT
     *
     * @const DENY_USER_AGENT
     */
    const DENY_USER_AGENT               = 'RPC_GATEWAY_DENY_USER_AGENT';

    /**
     * array of refereres to allow and deny all others. values must be
     * regex conform expressions or simple values. if you want to make sure you want
     * to block exact name pass as ^name$ or use wildcard patterns .* or value as it
     * is which will equal to a /value/i regex expression.
     *
     * @const ALLOW_REFERER
     */
    const ALLOW_REFERER                 = 'RPC_GATEWAY_ALLOW_REFERER';

    /**
     * activate signed requests expecting signature hash in request which. the signature parameter must always
     * be set/placed in first level of request parameter array
     *
     * @const SIGNED_REQUEST
     */
    const SIGNED_REQUEST                = 'RPC_GATEWAY_SIGNED_REQUEST';

    /**
     * defines the signed request method to get user identifier from the request object. allowed values are:
     * - host = will check for host/server name from server header object
     * - ip = will check for client ip value from server header object
     * - user = will check for user parameter value in request post object - see SIGNED_REQUEST_USER_PARAM
     *
     * @const SIGNED_REQUEST_METHOD
     */
    const SIGNED_REQUEST_METHOD         = 'RPC_GATEWAY_SIGNED_REQUEST_METHOD';

    /**
     * set optional array of preg regex patterns services to exclude from being only signed request services.
     * the regex pattern should be valid an not contain pattern delimiters. the regex string will be placed inside
     * the pattern like: '=^(' . implode('|', $services) . ')=i';
     *
     * @const SIGNED_REQUEST_EXCLUDES
     */
    const SIGNED_REQUEST_EXCLUDES       = 'RPC_GATEWAY_SIGNED_REQUEST_EXCLUDES';

    /**
     * set parameter name for signed request user identification - the user name or id
     * that is necessary to retrieve api or service key for
     *
     * @const SIGNED_REQUEST_USER_PARAM
     */
    const SIGNED_REQUEST_USER_PARAM     = 'RPC_GATEWAY_SIGNED_REQUEST_USER_PARAM';

    /**
     * set parameter name for signed request signature parameter - the parameter
     * where the signature for the request is to be found
     *
     * @const SIGNED_REQUEST_SIGN_PARAM
     */
    const SIGNED_REQUEST_SIGN_PARAM     = 'RPC_GATEWAY_SIGNED_REQUEST_SIGN_PARAM';

    /**
     *
     * @const SIGNED_REQUEST_SIGN_PARAM
     */
    const SALT_KEY     = 'RPC_GATEWAY_SALT';

    /**
     * define you own callback to validate signed request by receiving data and api/gateway
     * key. the callback function must return boolean value. the callback function will receive
     * 3 parameters = 1) request object, 2) the get/post parameter merged 3) the api key if set in gateway instance, if not
     * must be retrieved manually from where ever it is stored.
     *
     * <code>
     *  function myCallback($request, $params, $key = null)
     *  {
     *      return true;
     *  }
     * </code>
     *
     * @const SIGNED_REQUEST_CALLBACK
     */
    const SIGNED_REQUEST_CALLBACK       = 'RPC_GATEWAY_SIGNED_REQUEST_CALLBACK';

	/**
	 * Enable validation
	 *
	 * @const VALIDATE
	 */
	const VALIDATE                      = 'RPC_VALIDATE';


    /**
     * contains singleton instance for this class
     *
     * @var null|Xapp_Rpc_Gateway
     */
    protected static $_instance = null;

    /**
     * key => value array of api user id => api keys necessary when using signed
     * request with xapp rpc gateway
     *
     * @var array
     */
    private static $_keys = array();

    /**
     * secret salt for creating api keys
     *
     * @var string
     */
    private static $_salt = 'k?Ur$0aE#9j1+7ui';

    /**
     * contains the server instance the gateway is run upon
     *
     * @var null|Xapp_Rpc_Server
     */
    protected $_server = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::OMIT_ERROR                => XAPP_TYPE_BOOL,
        self::ALLOW_IP                  => XAPP_TYPE_ARRAY,
        self::DENY_IP                   => XAPP_TYPE_ARRAY,
        self::BASIC_AUTH                => XAPP_TYPE_ARRAY,
        self::DISABLE                   => XAPP_TYPE_BOOL,
        self::DISABLE_SERVICE           => XAPP_TYPE_ARRAY,
        self::ALLOW_HOST                => XAPP_TYPE_ARRAY,
        self::DENY_HOST                 => XAPP_TYPE_ARRAY,
        self::FORCE_HTTPS               => XAPP_TYPE_BOOL,
        self::ALLOW_USER_AGENT          => XAPP_TYPE_ARRAY,
        self::DENY_USER_AGENT           => XAPP_TYPE_ARRAY,
        self::ALLOW_REFERER             => XAPP_TYPE_ARRAY,
        self::SIGNED_REQUEST            => XAPP_TYPE_BOOL,
        self::SIGNED_REQUEST_METHOD     => XAPP_TYPE_STRING,
        self::SIGNED_REQUEST_EXCLUDES   => XAPP_TYPE_ARRAY,
        self::SIGNED_REQUEST_USER_PARAM => XAPP_TYPE_STRING,
        self::SIGNED_REQUEST_SIGN_PARAM => XAPP_TYPE_STRING,
        self::SIGNED_REQUEST_CALLBACK   => XAPP_TYPE_CALLABLE,
	    self::VALIDATE                  => XAPP_TYPE_BOOL,
        self::SALT_KEY                  => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::OMIT_ERROR                => 1,
        self::ALLOW_IP                  => 0,
        self::DENY_IP                   => 0,
        self::BASIC_AUTH                => 0,
        self::DISABLE                   => 0,
        self::DISABLE_SERVICE           => 0,
        self::ALLOW_HOST                => 0,
        self::DENY_HOST                 => 0,
        self::FORCE_HTTPS               => 0,
        self::ALLOW_USER_AGENT          => 0,
        self::DENY_USER_AGENT           => 0,
        self::ALLOW_REFERER             => 0,
        self::SIGNED_REQUEST            => 0,
        self::SIGNED_REQUEST_METHOD     => 1,
        self::SIGNED_REQUEST_EXCLUDES   => 0,
        self::SIGNED_REQUEST_USER_PARAM => 1,
        self::SIGNED_REQUEST_SIGN_PARAM => 1,
        self::SIGNED_REQUEST_CALLBACK   => 0,
        self::VALIDATE                  => 0,
        self::SALT_KEY                  => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
	    self::VALIDATE                  => true,
	    self::OMIT_ERROR                => false,
        self::SIGNED_REQUEST_METHOD     => 'host',
        self::SIGNED_REQUEST_USER_PARAM => 'usr',
        self::SIGNED_REQUEST_SIGN_PARAM => 'sig'
    );


    /**
     * class constructor needs rpc server as first parameter either as instance
     * or driver string, e.g. "json", if empty will create default server with
     * "json" driver. the third parameter expects a absolute file pointer or string
     * of php.ini style which can also be used to define all class options and control
     * the behaviour of the gateway
     *
     * @error 14001
     * @param null|string|Xapp_Rpc_Server $server expects on of the above explained values
     * @param null|array|object $options expects optional options
     * @param null|string $conf expects file pointer or string of gateway config
     */
    public function __construct($server = null, $options = null, $conf = null)
    {
        if($server instanceof Xapp_Rpc_Server)
        {
            $this->_server = $server;
        }else if(is_null($server)){
            $this->_server = Xapp_Rpc_Server::factory('json');
        }else{
            $this->_server = Xapp_Rpc_Server::factory((string)$server);
        }
        if($options !== null)
        {
            xapp_init_options($options, $this);
        }
        if($conf !== null)
        {
            $this->conf($conf);
        }
        $this->init();
    }

	/**
	 * Salt setter
	 * @param $salt
	 */
	public static function setSalt($salt){
        self::$_salt = $salt;
	}

	/**
	 * Salt getter
	 */
	public static function getSalt(){
		return self::$_salt;
	}

    /**
     * created singleton instance of this class. see explanation in constructor method
     *
     * @error 14002
     * @see Xapp_Rpc_Gateway::__construct()
     * @param null|string|Xapp_Rpc_Server $server expects on of the above explained values
     * @param null|array|object $options expects optional options
     * @param null|string $conf expects file pointer or string of gateway config
     * @return Xapp_Rpc_Gateway
     */
    public static function instance($server = null, $options = null, $conf = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($server, $options, $conf);
        }
        return self::$_instance;
    }


    /**
     * short cut function to create singleton instance of gateway. see explanation in instance method
     *
     * @error 14003
     * @see Xapp_Rpc_Gateway::instance
     * @param null|string|Xapp_Rpc_Server $server expects on of the above explained values
     * @param null|array|object $options expects optional options
     * @param null|string $conf expects file pointer or string of gateway config
     * @return Xapp_Rpc_Gateway
     */
    public static function create($server = null, $options = null, $conf = null)
    {
        return self::instance($server, $options, $conf);
    }


    /**
     * init gateway. if option OMIT_ERROR is set to true the value will be set to request instance
     * of server so the value is known when handling request
     *
     * @error 14004
     * @return void
     */
    protected function init()
    {
        if(xapp_is_option(self::OMIT_ERROR, $this))
        {
            $this->server()->request()->set('OMIT_ERROR', true, 'RPC');
        }
    }


    /**
     * server instance setter/getter method. if first parameter is set will
     * overwrite server instance with passed instance. NOTE: its not advised
     * to set the server instance other then in the constructor - if not server
     * instance must contain all options which otherwise would have been automatically
     * set by gateway class
     *
     * @error 14005
     * @param Xapp_Rpc_Server $server expects optional server instance
     * @return null|Xapp_Rpc_Server
     */
    public function server(Xapp_Rpc_Server $server = null)
    {
        if($server !== null)
        {
            $this->_server = $server;
        }
        return $this->_server;
    }


    /**
     * request setter/getter method. NOTE: its not advised to set request manual
     * at this stage. pass request instance as server option if you need custom request
     * options for your gateway configuration. setting request instance at this stages implies
     * that all necessary options are set
     *
     * @error 14006
     * @param Xapp_Rpc_Request $request expects optional request instance
     * @return null|Xapp_Rpc_Request
     */
    public function request(Xapp_Rpc_Request $request = null)
    {
        if($request !== null)
        {
            $this->server()->request($request);
        }
        return $this->server()->request();
    }


    /**
     * response setter/getter method. NOTE: its not advised to set response manual
     * at this stage. pass response instance as server option if you need custom response
     * options for your gateway configuration. setting response instance at this stages implies
     * that all necessary options are set
     *
     * @error 14007
     * @param Xapp_Rpc_Response $response expects optional response instance
     * @return null|Xapp_Rpc_Response
     */
    public function response(Xapp_Rpc_Response $response = null)
    {
        if($response !== null)
        {
            $this->server()->response($response);
        }
        return $this->server()->response();
    }


    /**
     * add api keys to key chain expecting user id and api key value for user id.
     * the id is not casted but should by an integer value, key is expected to be a string
     * value. create keys with internal key creator or external
     *
     * @error 14008
     * @param mixed $id expects the user id
     * @param string $key expects the api key string
     * @return Xapp_Rpc_Gateway
     */
    public function addKey($id, $key)
    {
        if(!array_key_exists($id, self::$_keys))
        {
            self::$_keys[$id] = trim((string)$key);
        }
        return $this;
    }


    /**
     * batch add keys to key chain - see Xapp_Rpc_Gateway::addKey for more
     *
     * @error 14009
     * @see Xapp_Rpc_Gateway::addKey
     * @param array $keys expects array of key => value pairs (id => key)
     * @return Xapp_Rpc_Gateway
     */
    public function addKeys(Array $keys)
    {
        foreach($keys as $k => $v)
        {
            $this->addKey($k, $v);
        }
        return $this;
    }


    /**
     * get key from internal key chain by user id. if user id is not set
     * value passed in second parameter will be returned
     *
     * @error 14010
     * @param mixed $id expects the user id
     * @param null|mixed $default expects default value to return if is is not found
     * @return null|string
     */
    public function getKey($id, $default = null)
    {
        if($this->hasKey($id))
        {
            return self::$_keys[$id];
        }else{
            return $default;
        }
    }


    /**
     * check if a key is set in internal key chain by user id
     *
     * @error 14011
     * @param mixed $id expects the user id
     * @return bool
     */
    public function hasKey($id)
    {
        return (array_key_exists($id, self::$_keys) && !empty(self::$_keys[$id])) ? true : false;
    }


    /**
     * create api or gateway key with this method passing user id as first parameter and preferably
     * referer value as second parameter. refere could be anything that can be found in request header
     * , e.g. host, referer, user agent etc. with the user id and referer a signed request can be validated
     * as being valid. the function will return a 22 char long key the can be reproduced when passing the
     * exact same parameters
     *
     * @error 14012
     * @param mixed $id expects the user id
     * @param null|string $referer expects variable referer string
     * @param null|string $salt expects optional custom salt
     * @return string
     */
    public static function makeKey($id, $referer = null, $salt = null)
    {
        if($referer !== null)
        {
            $hash = hash('md5', (((!empty($salt)) ? (string)$salt : self::$_salt) . $id . strtolower(trim((string)$referer))));
        }else{
            $hash = hash('md5', (((!empty($salt)) ? (string)$salt : self::$_salt) . $id));
        }
        return preg_replace(array('/=+$/', '/\+/', '/\//'), array('', 'u', '0'), base64_encode(pack('H*', $hash)));
    }


    /**
     * basic data sign method converting an array/object to string to be hashed and signed with key passed in second
     * parameter using the algorithm in third parameter. the same function must be used server and client side.
     * use your own implementation by setting valid callback function in option SIGNED_REQUEST_CALLBACK - this
     * is the preferred behaviour since rpc client may not support internal sign function used here. the callback
     * will receive three values data, user, key where the last will only exist if key has been set to key chain.
     * the callback must return boolean value whether signature verification was successful or not. the function
     * will return false if the encoding and/or hashing produced errors
     *
     * @error 14013
     * @param mixed $data expects data as string or array
     * @param string $key the api/gateway key with which the request was signed
     * @param string $algo expects the hashing algorithm
     * @throws Xapp_Rpc_Gateway_Exception
     * @return string|boolean false
     */
    public static function sign($data, $key, $algo = 'sha1')
    {
        $algo = strtolower(trim($algo));
        if(!is_array($data))
        {
            $data = (array)$data;
        }
        if(function_exists('hash_algos') && !in_array($algo, hash_algos()))
        {
            throw new Xapp_Rpc_Gateway_Exception(_("passed hashing algorithm is not recognized"), 1401301);
        }


        /*
        $_data = json_encode($data);
        $_data = str_replace('[]','{}',$_data);
        $_data = str_replace('\\/', '/',$_data);
        error_log($_data);
        */


/*
 *
        $_data = json_encode($data);
        xapp_clog('sign ' . $_data .  ' with ' . $key . '  to ' . hash_hmac((string)$algo, $_data, (string)$key));
        //$_data = str_replace('\"params\":[]', '\"params\":{}',$_data);
        $_data = str_replace('[]','{}',$_data);
        //error_log('sign ' . $_data .  ' with ' . $key . '  to ' . hash_hmac((string)$algo, $_data, (string)$key));
        xapp_clog('sign2 ' . $_data .  ' with ' . $key . '  to ' . hash_hmac((string)$algo, $_data, (string)$key));
        $_data = str_replace('\\/', '/',$_data);
        */


        //fecking PHP
        if(isset($data['params'])){
            if(is_array($data['params']) && Count($data['params'])==0){
                $data['params'] = new stdClass();
            }
        }

        if(!function_exists('xapp_rpc_sign'))
        {
            function xapp_rpc_sign($data, $key, $algo = 'sha1')
            {
                $data = json_encode($data);
                $data = str_replace('\\/', '/',$data);
                if($data !== false)
                {
                    return hash_hmac((string)$algo, $data, (string)$key);
                }else{
                    return false;
                }
            }
        }
        return xapp_rpc_sign($data, $key, $algo);
    }


    /**
     * read and process gateway config from string or file. the options are read from config file
     * which are in php or apache ini style. all options parsed and recognized will overwrite options
     * already set. NOTE: options in config file that need to be arrays must be separated by comma. all
     * values will be casted to whatever the option has been defined with in options dictionary
     * example of config:
     *
     * <code>
     *  #disable services
     *  disable_service foo.test,class.*
     *  #allow only these ips
     *  allow_ip 127.0.0.1
     * </code>
     *
     * @error 14014
     * @param string $conf expects the config as file pointer or string
     * @return void
     * @throws Xapp_Rpc_Gateway_Exception
     */
    public function conf($conf)
    {
        $tmp = array();

        if(is_file($conf))
        {
            if(($conf = file_get_contents($conf)) === false)
            {
                throw new Xapp_Rpc_Gateway_Exception(_("unable to open rpc conf file"), 1401401);
            }
        }
        $conf = trim($conf, "\r\n");
        $conf = explode("\n", $conf);

        foreach($conf as $c)
        {
            $c = preg_replace("/\s+/i", " ", trim($c));
            if(substr($c, 0, 1) !== " " && substr($c, 0, 1) !== "#")
            {
                if(($p = strpos($c, " ")) !== false)
                {
                    $key = strtolower(substr($c, 0, $p));
                    $val = substr($c, ($p + 1), strlen($c));
                    if(isset($tmp[$key]))
                    {
                        if(!is_array($tmp[$key]))
                        {
                            $tmp = $tmp[$key];
                            $tmp[$key] = array();
                            $tmp[$key][] = $tmp;
                            $tmp[$key][] = $val;
                        }else{
                            $tmp[$key][] = $val;
                        }
                    }else{
                        $tmp[$key] = $val;
                    }
                }
            }
        }

        foreach($tmp as $k => $v)
        {
            $k = "self::" . strtoupper($k);
            if(defined($k))
            {
                $n = constant($k);
                $type = self::$optionsDict[$n];
                switch($type)
                {
                    case XAPP_TYPE_ARRAY:
                        $v = explode(',', trim($v, ', '));
                        break;
                    case XAPP_TYPE_BOOL:
                        $v = (bool)$v;
                        break;
                    case XAPP_TYPE_INTEGER:
                        $v = (int)$v;
                        break;
                    case XAPP_TYPE_INT:
                        $v = (int)$v;
                        break;
                    case XAPP_TYPE_FLOAT:
                        $v = (float)$v;
                        break;
                }
                if(array_key_exists($n, $this->options) && is_array($this->options[$n]))
                {
                    $this->options[$n] =  array_merge($this->options[$n], $v);
                }else{
                    $this->options[$n] = $v;
                }
            }
        }
    }


    /**
     * validates all options that have been defined throwing rpc gateway faults if any of the
     * options fail to validate. see constant descriptions for what each constant does
     *
     * @error 14015
     * @param string $option expects the option name
     * @param null|mixed $value expects the options value
     * @throws Xapp_Rpc_Gateway_Exception
     * @throws Xapp_Rpc_Fault
     */
    protected function validate($option, $value = null)
    {
        $user = null;
        $option = strtoupper($option);

        switch($option)
        {
            case self::BASIC_AUTH:
                if($this->request()->has('PHP_AUTH_USER', 'SERVER') && $this->request()->has('PHP_AUTH_PW', 'SERVER') && isset($value[0]) && isset($value[1])){
                    if($this->server()->request()->getFrom('PHP_AUTH_USER', 'SERVER') !== $value[0] || $this->server()->request()->getFrom('PHP_AUTH_PW', 'SERVER') !== $value[1])
                    {
                        Xapp_Rpc_Fault::t("basic auth error - user or password not correct", array(1401501, -32001));
                    }
                }else{
                    Xapp_Rpc_Fault::t("basic auth error - credentials not set", array(1401502, -32002));
                }
                break;
            case self::ALLOW_IP:
                if(Xapp_Rpc_Request::getClientIp() !== null && !in_array(Xapp_Rpc_Request::getClientIp(), $value))
                {
                    Xapp_Rpc_Fault::t("request denied from service", array(1401503, -32003));
                }
                break;
            case self::DENY_IP:
                if(Xapp_Rpc_Request::getClientIp() !== null && in_array(Xapp_Rpc_Request::getClientIp(), $value))
                {
                    Xapp_Rpc_Fault::t("request denied from service", array(1401503, -32003));
                }
                break;
            case self::DISABLE:
                if((bool)$value)
                {
                    Xapp_Rpc_Fault::t("gateway is disabled", array(1401504, -32004));
                }
                break;
            case self::DISABLE_SERVICE:
                if($this->server()->hasServices())
                {
                    foreach($this->server()->getServices() as $service)
                    {
                        if(preg_match(Xapp_Rpc::regex($value), $service))
                        {
                            Xapp_Rpc_Fault::t("requested service: $service is disabled", array(1401505, -32005));
                        }
                    }
                }
                break;
            case self::ALLOW_HOST:
                if(Xapp_Rpc_Request::getHost() !== null  && !in_array(Xapp_Rpc_Request::getHost(), $value))
                {
                    Xapp_Rpc_Fault::t("host denied from service", array(1401506, -32006));
                }
                break;
            case self::DENY_HOST:
                if(Xapp_Rpc_Request::getHost() !== null  && in_array(Xapp_Rpc_Request::getHost(), $value))
                {
                    Xapp_Rpc_Fault::t("host denied from service", array(1401506, -32006));
                }
                break;
            case self::FORCE_HTTPS:
                if((bool)$value && Xapp_Rpc_Request::getScheme() !== 'HTTPS')
                {
                    Xapp_Rpc_Fault::t("request from none http secure host denied", array(1401507, -32007));
                }
                break;
            case self::ALLOW_USER_AGENT:
                if($this->request()->has('HTTP_USER_AGENT', 'SERVER') && !preg_match('/('.implode('|', trim($value, '()')).')/i', $this->request()->getFrom('HTTP_USER_AGENT', 'SERVER')))
                {
                    Xapp_Rpc_Fault::t("client denied from service", array(1401508, -32008));
                }
                break;
            case self::DENY_USER_AGENT:
                if($this->request()->has('HTTP_USER_AGENT', 'SERVER') && preg_match('/('.implode('|', trim($value, '()')).')/i', $this->request()->getFrom('HTTP_USER_AGENT', 'SERVER')))
                {
                    Xapp_Rpc_Fault::t("client denied from service", array(1401508, -32008));
                }
                break;
            case self::ALLOW_REFERER:
                if(Xapp_Rpc_Request::getReferer() !== null && !preg_match('/('.implode('|', trim($value, '()')).')/i', Xapp_Rpc_Request::getReferer()))
                {
                    Xapp_Rpc_Fault::t("referer denied from service", array(1401509, -32009));
                }
                break;

            case self::SIGNED_REQUEST:

                if((bool)$value)
                {
                    $tmp = array();
                    if(xapp_is_option(self::SIGNED_REQUEST_EXCLUDES, $this))
                    {

                        foreach($this->server()->getServices() as $service)
                        {
                            if(!preg_match(Xapp_Rpc::regex(xapp_get_option(self::SIGNED_REQUEST_EXCLUDES, $this)), $service))
                            {
                                $tmp[] = $service;
                            }
                        }
                    }

                    $do = true;
                    if(sizeof($tmp) > 0 || $do)
                    {
                        $sign = $this->request()->getParam(xapp_get_option(self::SIGNED_REQUEST_SIGN_PARAM, $this), false);
                        if($sign==null){
                            $sign  = $this->request()->getFrom(xapp_get_option(self::SIGNED_REQUEST_SIGN_PARAM, $this),Xapp_Rpc_Request::GET);
                        }
                        $method = strtolower(xapp_get_option(self::SIGNED_REQUEST_METHOD, $this));
                        switch($method)
                        {
                            case 'host':
                                $user = $this->request()->getHost();
                                break;
                            case 'ip':
                                $user = $this->request()->getClientIp();
                                break;
                            case 'user':
                                $user = $this->request()->getParam(xapp_get_option(self::SIGNED_REQUEST_USER_PARAM, $this), false);
                                if($user==null){
                                    $user  = $this->request()->getFrom(xapp_get_option(self::SIGNED_REQUEST_USER_PARAM, $this),Xapp_Rpc_Request::GET);
                                }
                                break;
                            default:
                                throw new Xapp_Rpc_Gateway_Exception(_("unsupported signed request user identification method"), 1401514);
                        }

                        if($user === false || $user === null)
                        {
                            Xapp_Rpc_Fault::t(vsprintf("signed request value for: %s not found in request", array(xapp_get_option(self::SIGNED_REQUEST_USER_PARAM, $this))), array(1401512, -32011));
                        }
                        if($sign === false || $sign === null)
                        {
                            Xapp_Rpc_Fault::t(vsprintf("signed request value for: %s not found in request", array(xapp_get_option(self::SIGNED_REQUEST_SIGN_PARAM, $this))), array(1401513, -32011));
                        }

                        $key = $this->getKey($user, null);

                        //xapp_clog('key ' . $key . ' user ' . $user);
                        $params = $this->request()->getParams();

                        if(array_key_exists('xdmTarget',$params)){
		                    unset($params['xdmTarget']);
	                    }

	                    if(array_key_exists('view',$params)){
		                    unset($params['view']);
	                    }
	                    if(array_key_exists('xfToken',$params)){
		                    unset($params['xfToken']);
	                    }
	                    if(array_key_exists('time',$params)){
		                    unset($params['time']);
	                    }
	                    if(array_key_exists('xdm_e',$params)){
		                    unset($params['xdm_e']);
	                    }
	                    if(array_key_exists('user',$params)){
		                    unset($params['user']);
	                    }
	                    if(array_key_exists('xdm_c',$params)){
		                    unset($params['xdm_c']);
	                    }
	                    if(array_key_exists('xdm_p',$params)){
		                    unset($params['xdm_p']);
	                    }
                        if(array_key_exists('theme',$params)){
                            unset($params['theme']);
                        }
                        if(array_key_exists('debug',$params)){
                            unset($params['debug']);
                        }
                        if(array_key_exists('width',$params)){
                            unset($params['width']);
                        }
                        if(array_key_exists('send',$params)){
                            unset($params['send']);
                        }
                        if(array_key_exists('attachment',$params)){
                            unset($params['attachment']);
                        }

                        if(xapp_is_option(self::SIGNED_REQUEST_CALLBACK, $this))
                        {
                            if(!(bool)call_user_func_array(xapp_get_option(self::SIGNED_REQUEST_CALLBACK, $this), array($this->request(), $params, $key)))
                            {
                                Xapp_Rpc_Fault::t("verifying signed request failed", array(1401510, -32010));
                            }
                        }else{

                            if($key !== null)
                            {
                                if(isset($params[xapp_get_option(self::SIGNED_REQUEST_SIGN_PARAM, $this)]))
                                {
                                    unset($params[xapp_get_option(self::SIGNED_REQUEST_SIGN_PARAM, $this)]);
                                }
                                if($sign !== self::sign($params, $key))
                                {
                                    Xapp_Rpc_Fault::t("verifying signed request failed", array(1401510, -32010));
                                }
                            }else{
                                throw new Xapp_Rpc_Gateway_Exception(_("user identification key must be set when using internal signed request verification"), 1401511);
                            }
                        }
                    }
                }
                break;

            default:

        }
    }


    /**
     * run gateway validating all options and handle request by server first by calling server setup method
     * then handle and finally tearing down the server instance. nothing will happen unless this function is called!
     *
     * @error 14016
     * @return void
     */
    final public function run()
    {
        xapp_debug('rpc gateway running', 'rpc');
        xapp_event('xapp.rpc.gateway.run', array(&$this));

        $this->server()->setup();
        if($this->server()->hasCalls())
        {
	        if(xapp_get_option(self::VALIDATE,$this)) {
		        foreach (xapp_get_options($this) as $k => $v) {
			        $this->validate($k, $v);
		        }
	        }
        }
        $this->server()->handle();
        $this->server()->teardown();

        xapp_debug('rpc gateway shutting down', 'rpc');
    }


    /**
     * on sleep kill key chain
     *
     * @error 14017
     * @return void
     */
    public function __sleep()
    {
        self::$_keys = array();
    }


    /**
     * on clone kill key chain
     *
     * @error 14018
     * @return void
     */
    public function __clone()
    {
        self::$_keys = array();
    }
}