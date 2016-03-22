<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Xapp.Error');

/**
 * Event class
 *
 * @package Xapp
 * @class Xapp_Event
 * @error 104
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Event
{
    /**
     * store all event names in array
     *
     * @var array
     */
    protected static $_events = array();


    /**
     * class can not be instantiated
     *
     * @error 10401
     */
    protected function __construct(){}


    /**
     * register event listener (object, static class, or callback) to listen to event type/name.
     * will return the event stack array
     *
     * @error 10402
     * @param string $event expects event name to listen to
     * @param callable $callback expects callable value
     * @param bool $reset expects boolean value if to reset listeners for event name
     * @return array
     * @throws Xapp_Error
     */
    public static function listen($event, $callback, $reset = false)
   	{
        $var = "";

        if(!is_array($callback) && !is_callable($callback, false, $var))
        {
            throw new Xapp_Error(xapp_sprintf(_("invalid callback function: %s supplied for event: %s"), $var, $event), 1040201);
        }
        if(is_array($callback) && !is_callable($callback, true, $var))
        {
            throw new Xapp_Error(xapp_sprintf(_("invalid callback class/method: %s supplied for event: %s"), $var, $event), 1040202);
        }
        if(!isset(self::$_events))
        {
            self::$_events = array();
        }
        if(!isset(self::$_events[$event]) || (bool)$reset)
        {
            self::$_events[$event] = array();
        }
        self::$_events[$event][] = $callback;
        return self::$_events;
   	}


    /**
     * returns all listeners for specific event name or default value if event name is not found
     *
     * @error 10403
     * @param null|string $event expects the event name to lookup
     * @param mixed $default expects default return value
     * @return array|mixed
     */
    public static function listeners($event = null, $default = array())
    {
        if($event !== null)
        {
            return (isset(self::$_events[$event])) ? self::$_events[$event] : xapp_default($default);
        }else{
            return (sizeof(self::$_events) > 0) ? self::$_events : xapp_default($default);
        }
    }


    /**
     * set new listener by overriding (resetting) all previous listeners for event name
     *
     * @error 10404
     * @param null|string $event expects event name to listen to
     * @param null|callable $callback expects callable value
     * @return void
     */
    public static function override($event = null, $callback = null)
    {
        if($event !== null && $callback !== null)
        {
            self::listen($event, $callback, true);
        }
    }


    /**
     * resets the whole event listener storage array, all listeners for one event or
     * listeners for an event at a specific index. the index can be a numerical index or
     * a value "first" for shifting array one down, or "last" to pop of last.
     * NOTE: resetting instead of overriding can accidently lead to mal function of xapp
     *
     * @error 10405
     * @param null|string $event expects event name
     * @param null|int|string $index expects index value as explained above
     * @return void
     * @throws Xapp_Error
     */
    public static function reset($event = null, $index = null)
    {
        if($event !== null)
        {
            if(isset(self::$_events[$event]))
            {
                if($index !== null)
                {
                    if(is_int($index) && array_key_exists($index, self::$_events[$event])){
                        self::$_events[$event][$index] = array();
                    }else if(strtolower((string)$index) === 'first'){
                        array_shift(self::$_events[$event]);
                    }else if(strtolower((string)$index) === 'last'){
                        array_pop(self::$_events[$event]);
                    }else{
                        throw new Xapp_Error(xapp_sprintf(_("unable to reset listener for event name and index: %s"), $index), 1040501);
                    }
                }else{
                    self::$_events[$event] = array();
                }
            }
        }else{
            self::$_events = array();
        }
    }


    /**
     * trigger event by executing all listeners registered to the event name by
     * calling the callable set in listen method and passing the additional parameters
     * to callable using php native function call_user_func_array. when more then one listener
     * are registered triggering an event for that name will bubble up output until the last
     * stacked event handler unless third parameter $halt is set to true. if set to true will break
     * the bubbling at first value which is returnable.
     *
     * @static
     * @error 10406
     * @param null|string $event expects event name to trigger listeners for
     * @param null|array|mixed $params expects additional parameters which can be passed to listeners callable function/object
     * @param bool $halt expects boolean value for event bubbling
     * @return null|mixed the return value of the listener
     */
    public static function trigger($event = null, $params = null, $halt = false)
    {
        $return = null;
        $result = array();

        if($event !== null)
        {
            foreach((array)$event as $e)
            {
                if(isset(self::$_events[$e]))
                {
                    foreach(self::$_events[$e] as $c)
                    {
                        $params = (!is_array($params)) ? array($params) : $params;
                        $return = call_user_func_array($c, $params);
                        if((bool)$halt && !is_null($return))
                        {
                            return $return;
                        }
                        $result[] = $return;
                    }
                }
            }
        }
        return (sizeof($result) > 0) ? $result : null;
    }
}