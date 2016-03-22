<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');


xapp_import('xapp.Rpc.Server');
xapp_import('xapp.Rpc.Exception');

/**
 * Rpc abstract base class
 *
 * @package Rpc
 * @class Xapp_Rpc
 * @error 139
 * @author Frank Mueller <set@cooki.me>
 */
abstract class Xapp_Rpc
{
    /**
     * contains all instances created with server factory method
     *
     * @var array
     */
    protected static $_instances = array();


    /**
     * factory method to create a rpc server instance by either passing an server
     * instance in first parameter or string with server driver name, e.g. "json"
     * or null value which will return the last added instance. will return instance
     * of Xapp_Rpc_Server in all cases
     *
     * @error 13901
     * @param null|string|Xapp_Rpc_Server $mixed expects value as explained above
     * @param null|array|object $options expects optional options to pass to server instance when constructing
     * @return Xapp_Rpc_Server
     * @throws Xapp_Rpc_Exception
     */
    final public static function server($mixed = null, $options = null)
    {
        if($mixed !== null)
        {
            self::getServer();
            if($mixed instanceof Xapp_Rpc_Server)
            {
                $driver = strtolower(substr($mixed, strrpos($mixed, '_') + 1));
                if(!array_key_exists($driver, self::$_instances))
                {
                    self::$_instances[$driver] = $mixed;
                }
                return self::$_instances[$driver];
            }else{
                $driver = strtolower(trim($mixed));
                if(!array_key_exists($driver, self::$_instances))
                {
                    $class = 'Xapp_Rpc_Server_' . ucfirst($driver);
                    if(class_exists($class, true))
                    {
                        self::$_instances[$driver] = new $class($options);
                    }else{
                        throw new Xapp_Rpc_Exception(xapp_sprintf(_("rpc server class: %s does not exist"), $class), 1390101);
                    }
                }
                return self::$_instances[$driver];
            }
        }else{
            return self::$_instances[sizeof(self::$_instances) - 1];
        }
    }


    /**
     * tries to return server instance by the callee of this function from either
     * the instance pool if server was created with Xapp_Rpc::server or by looking
     * if the concrete server implementation has been instantiated as singleton therefore
     * having the $_instance property not null. will return if either callee is not subclass
     * of this or no instance could be found
     *
     * @error 13902
     * @return bool
     */
    final protected static function getServer()
    {
        $class = get_called_class();

        if(is_subclass_of($class, __CLASS__))
        {
            $c = new ReflectionClass($class);
            $p = $c->getDefaultProperties();

            if(($driver = call_user_func(array($class, 'getDriver'))) !== false)
            {
                if(array_key_exists($driver, self::$_instances))
                {
                    return self::$_instances[$driver];
                }
                if(array_key_exists('_instance', $p) && $p['_instance'] !== null)
                {
                    return $p['_instance'];
                }
            }
        }
        return false;
    }


    /**
     * tries to get driver name from callee if callee of this method is subclass of this
     * class. the driver name, e.g. Xapp_Rpc_Server_Json = json can be used to obtain
     * driver singleton instance if set. will return false if driver could not be determined
     *
     * @error 13903
     * @return bool|string
     */
    final protected static function getDriver()
    {
        $class = get_called_class();
        if(is_subclass_of($class, __CLASS__))
        {
            return substr(strtolower($class), strlen(strtolower(__CLASS__ . '_Server')) + 1);
        }
        return false;
    }


    /**
     * delegate exception to server instance and flush exception to output. this is the only
     * way to handle exceptions and have them flushed according to rpc server specs. it is
     * important to call the function with the class name that used as server, e.g, when using
     * jsonp rpc server (Xapp_Rpc_Server_Jsonp) call this function like Xapp_Rpc_Server_Jsonp::dump
     * when catching errors so the callee is the same as server instance. the function will try
     * to get the singleton instance for the server or create a new instance for the server driver.
     * if driver can not be found default driver "json" is used. NOTE: so always call this function
     * with the right callee = class name - calling this function like Xapp_Rpc will return a json
     * error object even though you may be using a different server implementation. Example
     *
     * <code>
     *  catch(Exception $e)
     *  {
     *      Xapp_Rpc_Server_Jsonp::dump($e);
     *  }
     * </code>
     *
     * @error 13904
     * @param Exception $error expects instance of exception
     * @return void
     */
    final public static function dump(Exception $error)
    {
        $class = get_called_class();

        if($class === __CLASS__ || is_subclass_of($class, __CLASS__))
        {
            if(($server = $class::getServer())!== false)
            {
                $server->error($error);
                return;
            }
            if(($driver = $class::getDriver()) === false)
            {
                $driver = 'json';
            }
            $class = __CLASS__ . '_Server_' . ucfirst($driver);
            $server = new $class();
            $server->error($error);
            return;
        }else{
            trigger_error("unable to use rpc dump function since caller does not belong to rpc base class", E_USER_ERROR);
        }
    }


    /**
     * pref regex pattern optimize function for rpc package. does clean up, quote and compile to valid regex pattern
     * from string or array
     *
     * @error 13905
     * @param string|array $mixed expects regex pattern as string or array
     * @return string
     */
    public static function regex($mixed)
    {
        if(!is_array($mixed))
        {
            $mixed = array($mixed);
        }
        foreach($mixed as &$m)
        {
            $m = str_replace(array('\\', '|'), array('\\\\', '\|'), trim((string)$m));
            $m = xapp_regex_delimit($m, '^$');
        }
        return '=^(' . implode('|', $mixed) . ')=i';
    }
}