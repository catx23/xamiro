<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Util.Json.Exception');
xapp_import('xapp.Util.Json');

/**
 * Util json store class
 *
 * @package Util
 * @subpackage Util_Json
 * @class Xapp_Util_Json_Store
 * @error 169
 * @author Frank Mueller
 */
class Xapp_Util_Json_Store extends Xapp_Util_Std_Store
{
    /**
     * query the object with path and query parameters from Xapp_Util_Json_Query::find method and use store
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
    public function query($path, Array $query = null, $result_flag = 'first')
    {
        $this->_result =& Xapp_Util_Json_Query::find($this->_object, $path, $query, $result_flag);
        return $this;
    }
    /**
     * class constructor for json store implementation. see Xapp_Util_Std_Store::__constructor for more details
     *
     * @error 16901
     * @see Xapp_Util_Std_Store::__constructor
     * @param null|mixed $mixed expects one of the above value options
     * @param null|mixed $options expects optional class instance options
     * @throws Xapp_Util_Json_Exception
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
                $this->_object = Xapp_Util_Json::convert($mixed);
            }else{
                if(is_file($mixed))
                {
                    $this->_file = $mixed;
                    if(($mixed = file_get_contents($mixed)) !== false)
                    {
                        $this->_object = Xapp_Util_Json::decode($mixed);
                    }else{
                        throw new Xapp_Util_Json_Exception("unable to read from file: $mixed", 1690101);
                    }
                }else{
                    if(is_string($mixed) && Xapp_Util_Json::isJson($mixed))
                    {
                        $this->_object = Xapp_Util_Json::decode($mixed);
                    }else if(is_string($mixed) && strpos($mixed, '.') !== false && is_writeable(dirname($mixed))){
                        $this->_file = $mixed;
                    }else{
                        throw new Xapp_Util_Json_Exception("passed first argument in constructor is not a valid object or file path", 1690102);
                    }
                }
            }
        }
    }


    /**
     * json implementation for decoding, see Xapp_Util_Std_Store::decode for more details
     *
     * @error 16902
     * @see Xapp_Util_Std_Store::decode
     * @param mixed $value expects the value to try to decode
     * @return mixed|string
     */
    public static function decode($value)
    {
        if(is_string($value))
        {
            if(substr(trim($value), 0, 1) === '{' || substr(trim($value), 0, 2) === '[{')
            {
                return Xapp_Util_Json::decode($value);
            }else if(preg_match('/^([adObis]\:|N\;)/', trim($value))){
                return unserialize($value);
            }
        }
        return $value;
    }


    /**
     * json implementation for encoding, see Xapp_Util_Std_Store::encode for more details
     *
     * @error 16903
     * @param mixed $value expects any value to encode
     * @return mixed|string
     */
    public static function encode($value)
    {
        if(is_object($value))
        {
            return Xapp_Util_Json::encode($value);
        }else if(is_array($value)){
            return serialize($value);
        }else{
            return $value;
        }
    }


    /**
     * dump/print stores json object to screen
     *
     * @error 16904
     * @return void
     */
    public function dump()
    {
        Xapp_Util_Json::dump($this->_object);
    }


    /**
     * json implementation of save method, see Xapp_Util_Std_Store::save for more details
     *
     * @error 16905
     * @param bool $pretty expects boolean value whether to store json string pretty or non pretty
     * @return bool|mixed|string
     * @throws Xapp_Util_Json_Exception
     */
    public function save($pretty = true)
    {
        $result = null;

        if($this->_file !== null)
        {
            if((bool)$pretty)
            {
                $result = file_put_contents($this->_file, Xapp_Util_Json::prettify(Xapp_Util_Json::encode($this->_object)), LOCK_EX);
            }else{
                $result = file_put_contents($this->_file, Xapp_Util_Json::encode($this->_object), LOCK_EX);
            }
            if($result !== false)
            {
                $result = null;
                return true;
            }else{
                throw new Xapp_Util_Json_Exception(xapp_sprintf(_("unable to save to file: %s"), $this->_file), 1690501);
            }
        }else{
            return self::encode($this->_object);
        }
    }


    /**
     * json implementation of saveTo method, see Xapp_Util_Std_Store::saveTo for more details
     *
     * @error 16905
     * @param string $file expects absolute file path to store object at
     * @param bool $pretty expects boolean value whether to store json string pretty or non pretty
     * @return bool
     * @throws Xapp_Util_Json_Exception
     */
    public function saveTo($file, $pretty = true)
    {
        $result = null;

        if((bool)$pretty)
        {
            $result = file_put_contents($file, Xapp_Util_Json::prettify(Xapp_Util_Json::encode($this->_object)), LOCK_EX);
        }else{
            $result = file_put_contents($file, Xapp_Util_Json::encode($this->_object), LOCK_EX);
        }
        if($result !== false)
        {
            $result = null;
            return true;
        }else{
            throw new Xapp_Util_Json_Exception(xapp_sprintf(_("unable to save to file: %s"), $file), 1690501);
        }
    }
}