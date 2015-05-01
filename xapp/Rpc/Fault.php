<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');


/**
 * Rpc fault class
 *
 * @package Rpc
 * @class Xapp_Rpc_Fault
 * @error 143
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Rpc_Fault extends ErrorException
{
    /**
     * contains the rpc fault code
     *
     * @var null|int
     */
    protected $_fault = null;

    /**
     * contains additional data
     *
     * @var array
     */
    protected $_data = array();


    /**
     * error exception class constructor
     *
     * @error 14301
     * @param string $message excepts error message
     * @param int $code expects error code
     * @param int $severity expects severity flag
     */
    public function __construct($message, $code = 0, $severity = XAPP_ERROR_ERROR)
    {
        parent::__construct($message, $code, $severity);
    }


    /**
     * static function to create fault instance not throwing it!
     *
     * @error 14302
     * @param string $message excepts error message
     * @param int $code expects error code
     * @param int $severity expects severity flag
     * @param null|array $data expects optional additional data as array
     * @return Xapp_Rpc_Fault
     */
    public static function create($message, $code, $severity = XAPP_ERROR_IGNORE, Array $data = null)
    {
        $self = new self($message, $code, $severity);
        if($data !== null)
        {
            $self->_data = $data;
        }
        return $self;
    }


    /**
     * static function to create fault instance and directly throwing it. this is the preferred way to
     * throw a fault exception. the second parameter \$code can be a single integer error code or an array
     * of two codes like array(internal code, external code). this is for conformity reasons so xapp error
     * code are maintained throughout all modules. if you want to use rpc conform error codes in output
     * pass the codes as array.
     *
     * @error 14303
     * @param string $message excepts error message
     * @param int|array $code expects error code
     * @param int $severity expects severity flag
     * @param null|array $data expects optional additional data as array
     * @throws Xapp_Rpc_Fault
     */
    public static function t($message, $code, $severity = XAPP_ERROR_IGNORE, Array $data = null)
    {
        $fault = null;

        if(is_array($code))
        {
            if(array_key_exists(1, $code))
            {
                $fault = (int)$code[1];
            }
            $code = (array_key_exists(0, $code)) ? (int)$code[0] : 0;
        }
        if((int)$code === 1421101)
        {
            $self = new self($message, $fault, $severity);
        }else{
            $self = new self($message, $code, $severity);
        }
        xapp_error($self, $code, $severity);
        if($data !== null)
        {
            $self->_data = $data;
        }
        if($fault !== null)
        {
            $self->setFault($fault);
        }

        throw $self;
    }


    /**
     * set fault code
     *
     * @error 14304
     * @param int $fault expects the fault code
     * @return Xapp_Rpc_Fault
     */
    public function setFault($fault)
    {
        $this->_fault = (int)$fault;
        return $this;
    }


    /**
     * get fault code
     *
     * @error 14305
     * @return int|null
     */
    public function getFault()
    {
        return $this->_fault;
    }


    /**
     * check if fault code is set in instance or not
     *
     * @error 14306
     * @return bool
     */
    public function hasFault()
    {
        return ($this->_fault !== null) ? true : false;
    }


    /**
     * set additional data in key => value pairs. the first parameter must not be set  if wanting to add none named
     * parameters to data array. key => value pairs can be set in "." notation for multidimensional arrays
     *
     * @error 14307
     * @param null|string $key expects optional data key name
     * @param null|mixed $value expects value to set
     * @return Xapp_Rpc_Fault
     */
    public function setData($key = null, $value = null)
    {
        if($key !== null)
        {
            xapp_array_set($this->_data, (string)$key, $value);
        }else{
            $this->_data[] = $value;
        }
        return $this;
    }


    /**
     * get data from additional data array either by passing first parameter key returning the data for that key or
     * without first parameter returning the complete data array. if key is not found in data array will return default
     * second parameter. the key can be passed in "." notation to get data in multidimensional array
     *
     * @error 14308
     * @param null|int|string $key expects the optional key to get data for
     * @param null $default expects optional default return value
     * @return array|null
     */
    public function getData($key = null, $default = null)
    {
        if($key !== null)
        {
            return ($this->hasData($key)) ? xapp_array_get($this->_data, $key) : $default;
        }else{
            return $this->_data;
        }
    }


    /**
     * check if data exists in additional data array. if first parameter is null will check if anything has been set yet
     * or if data array is empty. if first parameter is set will check if key exist in additional data array. use second
     * parameter to check for strict value - a value that is not null, not empty and not false. the key can be passed in
     * "." notation to check data in multidimensional array
     *
     * @error 14309
     * @param null|int|string $key expects the optional key to get data for
     * @param bool $strict expects boolean value to check for strict mode
     * @return bool
     */
    public function hasData($key = null, $strict = false)
    {
        if($key !== null)
        {
            return (xapp_array_isset($this->_data, $key, (bool)$strict)) ? true : false;
        }else{
            return (!empty($this->_data)) ? true : false;
        }
    }


    /**
     * unset/remove data key from data array if first argument key is not null. will reset the whole data array if first
     * argument is null. the key can be passed in "." notation to unset data in multidimensional array
     *
     * @error 14311
     * @param null|int|string $key expects the key to delete data value for
     */
    public function unsetData($key = null)
    {
        if($key !== null)
        {
            if($this->hasData($key))
            {
                xapp_array_unset($this->_data, $key);
            }
        }else{
            $this->_data = array();
        }
    }


    /**
     * throws exception and sends exception to xapp error logging chain
     *
     * @error 14310
     * @throws Xapp_Rpc_Fault
     */
    public function execute()
    {
        xapp_error($this, $this->getCode(), $this->getSeverity());
        throw $this;
    }
}