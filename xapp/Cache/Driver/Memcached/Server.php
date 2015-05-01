<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');


/**
 * Cache driver memcached server class
 *
 * @package Cache
 * @subpackage Cache_Driver_Memcached
 * @class Xapp_Cache_Driver_Memcached_Server
 * @error 159
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Cache_Driver_Memcached_Server implements ArrayAccess
{
    /**
     * contains the host name of the server
     *
     * @var null|string
     */
    public $host = null;

    /**
     * contains the port value of the server
     *
     * @var null|int
     */
    public $port = null;

    /**
     * contains the weight of the server
     *
     * @var null|int
     */
    public $weight = null;


    /**
     * construct a new memcached server object
     *
     * @error 15901
     * @param string $host expects the host string
     * @param int $port expects the port number
     * @param int $weight expects the optional weight
     */
    public function __construct($host, $port, $weight = 0)
    {
        $this->host = trim((string)$host);
        $this->port = (int)$port;
        $this->weight = (int)$weight;
    }


    /**
     * implementing array access for key exists checking for if property is set and not null
     *
     * @error 15902
     * @param mixed $offset expects the array key
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (property_exists($this, $offset) && $this->$offset !== null) ? true : false;
    }


    /**
     * implementing array access to set key => value to property => value
     *
     * @error 15903
     * @param mixed $offset expects the key name
     * @param mixed $value expects the value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if(property_exists($this, $offset))
        {
            $this->$offset = $value;
        }
    }


    /**
     * implementing array access to get value for key, value for property
     *
     * @error 15904
     * @param mixed $offset expects the key name
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        if(property_exists($this, $offset))
        {
            return $this->$offset;
        }else{
            return null;
        }
    }


    /**
     * implementing array access to unset value for key = property
     *
     * @error 15905
     * @param mixed $offset expects the key name
     * @return void
     */
    public function offsetUnset($offset)
    {
        if(property_exists($this, $offset))
        {
            $this->$offset = null;
        }
    }
}