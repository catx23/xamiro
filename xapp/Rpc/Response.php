<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');


xapp_import('xapp.Rpc.Response.Exception');

/**
 * Rpc response class
 *
 * @package Rpc
 * @class Xapp_Rpc_Response
 * @error 145
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Rpc_Response
{
    /**
     * option to define the response charset
     *
     * @const CHARSET
     */
    const CHARSET               = 'RPC_RESPONSE_CHARSET';

    /**
     * option to set the content type for response
     *
     * @const CONTENT_TYPE
     */
    const CONTENT_TYPE          = 'RPC_RESPONSE_CONTENT_TYPE';

    /**
     * option to set the protocol type for response
     *
     * @const PROTOCOL
     */
    const PROTOCOL              = 'RPC_RESPONSE_PROTOCOL';

    /**
     * option that contains the default status code for response
     *
     * @const STATUS_CODE
     */
    const STATUS_CODE           = 'RPC_RESPONSE_STATUS_CODE';

    /**
     * option can contain custom response header that must be set as array with two values
     * the header key or name and the header values e.g. HEADER => array(array('name', 'value'), ...)
     *
     * @const HEADER
     */
    const HEADER                = 'RPC_RESPONSE_HEADER';


    /**
     * contains all data that need to converted to be flushed out as string.
     * data should always have key => value pairs
     *
     * @var array
     */
    protected $_data = array();

    /**
     * contains the "raw" returned result from invoked method called in request
     *
     * @var array
     */
    protected $_result = array();

    /**
     * the response body or the response string that is flushed to output when flushing response
     *
     * @var null|string
     */
    protected $_body = null;

    /**
     * boolean flag that contains boolean value if body has been send or not
     *
     * @var bool
     */
    protected $_bodySent = false;

    /**
     * array containing further header key => values pairs that are not passed to class in class
     * options but at a later stage with header add method
     *
     * @var array
     */
    protected $_header = array();

    /**
     * boolean flag that contains boolean value if header has been send or not
     *
     * @var bool
     */
    protected $_headerSent = false;

	/***
	 * In case its a 'RAW' RPC server, we skip all header stuff
	 */
	public function skipHeader(){
		$this->_headerSent=true;
	}
    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CHARSET           => XAPP_TYPE_STRING,
        self::CONTENT_TYPE      => XAPP_TYPE_STRING,
        self::PROTOCOL          => XAPP_TYPE_STRING,
        self::STATUS_CODE       => XAPP_TYPE_INT,
        self::HEADER            => XAPP_TYPE_ARRAY
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CHARSET           => 1,
        self::CONTENT_TYPE      => 1,
        self::PROTOCOL          => 1,
        self::STATUS_CODE       => 1,
        self::HEADER            => 0
    );


    /**
     * function to encode data to string
     *
     * @param $data
     * @return mixed
     */
    abstract public function encode($data);


    /**
     * function to check if data is already encoded or not
     *
     * @param $data
     * @return boolean
     */
    abstract protected function encoded($data);


    /**
     * class constructor must be called from child class to initialize response
     *
     * @error 14501
     */
    protected function __construct()
    {

    }


    /**
     * factory method to create new response instance defined by first parameter driver
     * which must contain a string with the driver name, e.g. "json"
     *
     * @error 14502
     * @param string $driver expects the driver name
     * @param null $options expects optional options to pass to class instance
     * @return Xapp_Rpc_Response
     * @throws Xapp_Rpc_Response_Exception
     */
    public static function factory($driver, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(strtolower(trim($driver)));
        if(class_exists($class, true))
        {
            return new $class($options);
        }else{
            throw new Xapp_Rpc_Response_Exception(xapp_sprintf(_("rpc response class: %s does not exist"), $class), 1450201);
        }
    }


    /**
     * setter/getter method for data. if first parameter is set will set data if not
     * will return data. first parameter should be array if not will be casted to array
     *
     * @error 14503
     * @param null|mixed|array $data expects data to set
     * @return array
     */
    public function data($data = null)
    {
        if($data !== null)
        {
            $this->_data = (array)$data;
        }
        return $this->_data;
    }


    /**
     * setter/getter method for result return by invoked method/function in request. if the first paramter
     * is set will set result if not will get the result which defaults to null
     * 
     * @error 14518
     * @param null|mixed|array $result
     * @return array
     */
    public function result($result = null)
    {
        if($result !== null)
        {
            $this->_result = (array)$result;
        }
        return $this->_result;
    }


    /**
     * setter/getter method for response body the string that will be flushed to output when response
     * is flushed. if first parameter is set will set value to body property. if first parameter is not
     * a string will be encoded to string by encode function implemented in concrete implementation. if
     * first parameter is empty will return body string
     *
     * @error 14504
     * @param null|string|mixed $body expects body value when setting
     * @return string
     */
    public function body($body = null)
    {
        if($body !== null)
        {
            if(!is_string($body))
            {
                $body = $this->encode($body);
            }
            $this->_body = trim((string)$body);
        }
        return (string)$this->_body;
    }


    /**
     * setter/getter method to set or get headers. to get headers call method without any parameters
     * returning all registered header values as array. to set header either pass raw header to first
     * argument leaving second argument unset or pass header key => value pair as first and second
     * argument. the raw header must be passed as one string like "Pragma: public" otherwise pass
     * key to first argument "Pragma" and value "public" to second argument. you can also pass multiple
     * headers using the first two arguments passing an array of keys in first and array of values in
     * second argument. size of these arrays must match or will throw exception. pass replacement
     * value and header code in third and fourth parameter as if you would use phps internal header().
     * function will return always all registerd headers as array. when calling method without any arguments
     * will first time merge all header from class options to header array unsetting the class option
     *
     * @error 14505
     * @param null|string|array $mixed expects raw header string, header key as string or array of keys
     * @param null|mixed|array $value expects null for raw header, mixed for header value or array of values
     * @param null|bool $replace expects optional boolean value on whether to replace header
     * @param null|int $code expects optional response code
     * @return array
     * @throws Xapp_Rpc_Response_Exception
     */
    public function header($mixed = null, $value = null, $replace = null, $code = null)
    {
        if($mixed !== null)
        {
            //raw header in first parameter
            if(is_string($mixed) && $value === null){
                $mixed = explode(':', $mixed);
                if(sizeof($mixed) === 2)
                {
                    $mixed = array(array(trim($mixed[0]), trim($mixed[1])));
                }else{
                    throw new Xapp_Rpc_Response_Exception(_("response raw header passed is not valid"), 1450501);
                }
            //header with key => value pair in first and second parameter
            }else if(is_string($mixed) && !is_array($value)){
                $mixed = array(array(trim($mixed), trim($value)));
            //headers with key => value pairs as array in first and second parameter
            }else if(is_array($mixed) && is_array($value)){
                $mixed = array($mixed, $value);
            //header not recognized
            }else{
                throw new Xapp_Rpc_Response_Exception(_("response header passed is not recognized"), 1450502);
            }
            for($i = 0; $i < sizeof($mixed); $i++)
            {
                if(isset($mixed[$i][0]) && isset($mixed[$i][1]))
                {
                    if(preg_match("/^content\-type\:\s+([^\s]+)\s+charset\=(.+)$/i", $mixed[$i][0] . ': ' . $mixed[$i][1], $m))
                    {
                        xapp_set_option(self::CONTENT_TYPE, strtolower(trim($m[1], '; ')), $this);
                        xapp_set_option(self::CHARSET, strtolower(trim($m[2], '; ')), $this);
                    }
                    $obj = new XO();
                    $obj->name = $mixed[$i][0];
                    $obj->value = $mixed[$i][1];
                    $obj->replace = null;
                    $obj->code = null;
                    if($replace !== null)
                    {
                        $obj->replace = (bool)$replace;
                    }
                    if(isset($mixed[$i][2]))
                    {
                        $obj->replace = (bool)$mixed[$i][2];
                    }
                    if($code !== null)
                    {
                        $obj->code = (int)$code;
                    }
                    if(isset($mixed[$i][3]))
                    {
                        $obj->code = (int)$mixed[$i][3];
                    }
                    $this->_header[] = $obj;
                }else{
                    throw new Xapp_Rpc_Response_Exception(_("response header miss match when passing keys and values as arrays"), 1450503);
                }
            }
        }else{
            if(xapp_is_option(self::HEADER, $this))
            {
                $header = xapp_get_option(self::HEADER, $this);
                if(!is_array($header[0]))
                {
                    $header = array($header);
                }
                xapp_unset_option(self::HEADER, $this);
                $this->header($header);
            }
        }
        return $this->_header;
    }


    /**
     * set data to data array by key => value pair
     *
     * @error 14506
     * @param string $key expects name of data parameter
     * @param null|mixed $value expects value of data parameter
     * @return Xapp_Rpc_Response
     */
    public function set($key, $value = null)
    {
        xapp_array_set($this->_data, $key, $value);
        return $this;
    }


    /**
     * get data from data array by key returning default value from second argument if key was not found
     *
     * @error 14507
     * @param string $key expects the name of data parameter
     * @param null $default expects the default return value
     * @return array|mixed|null
     */
    public function get($key, $default = null)
    {
        return xapp_array_get($this->_data, $key, $default);
    }


    /**
     * checks if parameter key exists in data array
     *
     * @error 14508
     * @param string $key expects name of parameter
     * @return bool
     */
    public function has($key)
    {
        return xapp_array_isset($this->_data, $key);
    }


    /**
     * checks if parameter key exists in data array in strict mode
     *
     * @error 14509
     * @param string $key expects name of parameter
     * @return bool
     */
    public function is($key)
    {
        return xapp_array_isset($this->_data, $key, true);
    }


    /**
     * flushes headers and body. nothing will happen unless this method is called
     *
     * @error 14510
     * @return void
     */
    final public function flush()
    {
        xapp_event('xapp.rpc.response.flush', array(&$this));

        if(empty($this->_body) && !empty($this->_data))
        {
            $this->body($this->data());
        }
        $this->flushHeader();
        $this->flushBody();
    }


    /**
     * flush headers by setting default header content type, length and charset and
     * all registered custom headers in class options or set via the header method
     *
     * @error 14511
     * @return void
     */
    protected function flushHeader()
    {
        if(!$this->_headerSent)
        {
            $charset        = xapp_get_option(self::CHARSET, $this);
            $contentType    = xapp_get_option(self::CONTENT_TYPE, $this);
            $statusCode     = xapp_get_option(self::STATUS_CODE, $this);

            header("Content-Type: $contentType; charset=$charset", true, (int)$statusCode);
            if(ob_get_contents() === '')
            {
                header("Content-Length: ".strlen($this->body()));
            }
            foreach($this->_header as $h)
            {
                header(trim($h->name, ' :') . ': ' .$h->value, $h->replace, $h->code);
            }
            $this->_headerSent = true;
        }
    }


    /**
     * flushes body of response terminating script afterwards
     *
     * @error 14512
     * @return void
     */
    protected function flushBody()
    {
        if(!$this->_bodySent)
        {
            echo $this->body();
            $this->_bodySent = true;
        }
    }


    /**
     * overload class properties will look for property name in data array and return value
     * if not found will throw exception
     *
     * @error 14513
     * @param string $name expects data parameter key
     * @return array|mixed|null
     * @throws Xapp_Rpc_Response_Exception
     */
    public function __get($name)
    {
        if($this->has($name))
        {
            return $this->get($name);
        }else{
            throw new Xapp_Rpc_Response_Exception(_("overloading property only allowed for data parameters"), 1451301);
        }
    }


    /**
     * overload class property with isset() function will check if parameter name exist in data array and
     * return boolean value
     *
     * @error 14514
     * @param string $name expects data parameter key
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }


    /**
     * overload class property with unset function will work only if parameter exists in data array if not
     * will throw exception
     *
     * @error 14515
     * @param string $name expects data parameter key
     * @throws Xapp_Rpc_Response_Exception
     */
    public function __unset($name)
    {
        if($this->has($name))
        {
            xapp_array_unset($this->_data, $name);
        }else{
            throw new Xapp_Rpc_Response_Exception(_("overloading and unsetting property only allowed for data parameters"), 1451501);
        }
    }


    /**
     * overload class method is only allowed to get values from data array by parameter name else will
     * throw exception
     *
     * @error 14516
     * @param string $name expects data parameter key
     * @param null|array $arguments expects arguments from overloading
     * @return array|mixed|null
     * @throws Xapp_Rpc_Response_Exception
     */
    public function __call($name, Array $arguments = array())
    {
        if(empty($arguments) && $this->has($name))
        {
            return $this->get($name);
        }else{
            throw new Xapp_Rpc_Response_Exception(_("overloading method only allowed to get values from data array"), 1451601);
        }
    }


    /**
     * on string conversion return response body
     *
     * @error 14517
     * @return string
     */
    public function __toString()
    {
        return (string)$this->_body;
    }
}