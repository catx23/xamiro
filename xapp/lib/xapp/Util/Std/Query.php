<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

abstract class Xapp_Util_Std_Query
{
    const QUERY_ORDER       = 'QUERY_ORDER';

    const DELIMITER         = 'DELIMITER';


    private static $_operators = array('\=', '\=\=', '\!\=', '\!\=\=', '\>', '\>\=', '\<', '\<\=', '\%', '\!\%', '\<\>', '\!\<\>');

    public static $optionsDict = array
    (
        self::DELIMITER     => XAPP_TYPE_STRING,
        self::QUERY_ORDER   => XAPP_TYPE_INT
    );

    public static $optionsRule = array
    (
        self::DELIMITER     => 1,
        self::QUERY_ORDER   => 1
    );

    public static $options = array
    (
        self::DELIMITER     => '.',
        self::QUERY_ORDER   => 0
    );


    protected function __construct(){}


    public static function options($options = null)
    {
        if($options !== null)
        {
            return xapp_set_options($options, get_class());
        }else{
            return xapp_get_options(get_class());
        }
    }


    public static function get(&$object, $path, $default = false, Array $options = array())
    {
        if(!empty($options))
        {
            $options = array_merge(xapp_get_options(get_class()), $options);
        }else{
            $options = xapp_get_options(get_class());
        }
        $path = trim((string)$path);
        $delimiter = (string)$options[self::DELIMITER];
        if(empty($path) || $path === $delimiter)
        {
            return $object;
        }else{
            foreach(explode($delimiter, $path) as $p)
            {
                if(is_numeric($p) && (intval($p) == floatval($p)))
                {
                    if(!is_array($object) || !array_key_exists($p, $object))
                    {
                        return $default;
                    }else{
                        $object = &$object[$p];
                    }
                }else{
                    if(!isset($object->$p))
                    {
                        return $default;
                    }else{
                        $object = &$object->{$p};
                    }
                }
            }
            return $object;
        }
    }


    /**
     * class must be in std package
     *
     * patterns:
     * id=1,
     * id>1
     * id>=1
     * id<1
     * id<=1
     * id!=1
     * id->1,2,3,4
     * id*regex
     *
     * key in query can also have wildcard like id[*]
     * if path is empty or only "." "/"
     *
     * the options are find => first, find => last, limit = 10,
     *
     * all query elements are AND connected. when the first object is found check other condition. if next condition
     * asserts positive try next and so on. if next assertion fails exit loop and try to find next matching object
     */
    public static function query($object, $path, Array $query = null, Array $options = array())
    {
        $result = array();

        if($query === null)
        {
            return self::get($object, $path);
        }else{
            if(!empty($options))
            {
                $options = array_merge(xapp_get_options(get_class()), $options);
            }else{
                $options = xapp_get_options(get_class());
            }
            if(($object = self::get($object, $path)) !== false)
            {
                if(!is_array($object))
                {
                    $object = array($object);
                }
                if(sizeof($query) > 1)
                {
                    foreach($query as $q)
                    {
                        $result = self::_query($object, $q);
                        if(!empty($result))
                        {
                            $object = $result;
                        }else{
                            return false;
                        }
                    }
                    return ((int)$options[self::QUERY_ORDER] === 0) ? $result[0] : $result[sizeof($result) - 1];
                }else{
                    $result = self::_query($object, $query[0]);
                    if(!empty($result))
                    {
                        return ((int)$options[self::QUERY_ORDER] === 0) ? $result[0] : $result[sizeof($result) - 1];
                    }else{
                        return false;
                    }
                }
            }
        }
        return false;
    }


    private static function _query($object, $query, &$result = array())
    {
        foreach($object as $k => $v)
        {
            if(is_object($v) || is_array($v))
            {
                self::_query($v, $query, $result);
            }else{
                if(self::_assert($k, $v, $query))
                {
                    $result[] = &$object;
                }
            }
        }
        return $result;
    }


    private static function _assert($key, $value, $query)
    {
        if(preg_match("/^($key)(".implode('|', self::$_operators).")(.*)$/i", $query, $m))
        {
            if(sizeof($m) > 0)
            {
                switch((string)$m[2])
                {
                    case '=':
                        return ($value == $m[3]) ? true : false;
                        break;
                    case '==':
                        return ($value === self::typify($m[3])) ? true : false;
                        break;
                    case '!=':
                        return ($value != $m[3]) ? true : false;
                        break;
                    case '!==':
                        return ($value !== self::typify($m[3])) ? true : false;
                        break;
                    case '>':

                        break;
                    default:
                        //error or * or = ?
                }
            }
        }
        return false;
    }


    private static function typify($value)
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