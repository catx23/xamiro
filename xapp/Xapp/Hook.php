<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Xapp.Error');

/**
 * Hook class
 *
 *
 *
 * @package Xapp
 * @class Xapp_Hook
 * @error 104

 */
class Xapp_Hook
{

    /**
     *  Minimal priority
     */
    const MIN_PRIORITY = 10;

    /**
     * @var array : Registered hooks: Array[hook_name][priority][]=>Array("class","method")
     *
     */
    static private $registered = array();


    /**
     * class can not be instantiated
     *
     * @error 10401
     */
    protected function __construct(){}


    /**
     * @brief Attach a method to a hook with optional priority.
     *
     *
     *
     * @param $hook_name
     * @param $class_or_function    :   class reference or function to be run
     * @param $method               :   (optional) if class is set, method to be run
     * @param string $priority      :   (optional) priority from 0 to 10 (0 higher, 10 lower)
     */
    public static function connect($hook_name,$class_or_function,$method='',$priority='',$userData=null) {
        // Assign default priority, check is not lower than MIN_PRIORITY
        if ($priority=='') $priority = self::MIN_PRIORITY;
        elseif ($priority > self::MIN_PRIORITY) $priority = self::MIN_PRIORITY;

        // Create hook array element if not exists
        if( !array_key_exists( $hook_name, self::$registered )) {
            self::$registered[$hook_name] = array();
        }
        // Create hook=>priority array element if not exists
        if( !array_key_exists( $priority, self::$registered[$hook_name] )) {
            self::$registered[$hook_name][$priority] = array();
        }
        self::$registered[$hook_name][$priority][] = self::createHookNode($class_or_function,$method,$userData);

    }

    /**
     * @brief Unattach a method from a hook
     *
     * @param $hook_name
     * @param $class                :   class reference to be removed (disconnect doesn't work for anonymous functions
     * @param $method               :   method to be removed
     * @return bool                 :   method found and removed
     */
    public static function disconnect($hook_name,$class,$method='') {
        $hookNode=self::createHookNode($class,$method);

        // If it's function, not possible to disconnect
        if (isset($hookNode["function"]))
        {
            return false;
        }
        elseif (array_key_exists( $hook_name, self::$registered ))
        {
            foreach(self::$registered[$hook_name] as $priority_number=>$priority_slot)
            {
                foreach($priority_slot as $n => $call) {
                    if (($call["class"] == $hookNode["class"]) && ($call["method"] == $hookNode["method"])) {
                        // Removes connection from array
                        unset(self::$registered[$hook_name][$priority_number][$n]);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     *
     * Returns an array with the connection data
     *
     * @param $class_or_function
     * @param string $method
     * @return array
     */
    private static function createHookNode($class_or_function,$method='',$userData=null) {
        return array(
            "function" => ($method==''?$class_or_function:null),
            "class" => ($method!=''?$class_or_function:null),
            "method" => $method,
            "userData" =>$userData
        );
    }

    /**
     * @brief Clears all connected methods from a hook
     *
     * @param $hook_name
     */
    public static function reset($hook_name) {
        if(array_key_exists( $hook_name, self::$registered ))
            self::$registered[$hook_name]=Array();
    }

    /**
     * @brief Calls a hook. All the connected methods will be run in priority order (0 first, 10 last)
     *
     *
     * @param $hook_name
     * @param array $params     :   Params to be passed to every method
     * @return array            :   all the returned values for each method Array("class.method"=>returned value)
     */
    public static function trigger($hook_name,$params=Array()) {
            for($check_priority=0;$check_priority <= self::MIN_PRIORITY;$check_priority++){
                if (array_key_exists($hook_name,self::$registered)
                    && array_key_exists($check_priority,self::$registered[$hook_name])){
                    foreach(self::$registered[$hook_name][$check_priority] as $hookNode){
                        $params=self::trigger_method($hookNode,$params);
                    }
                }
            }
        return $params;
    }


    /**
     *  Triggers a single call/node
     *
     *
     * @param $hookNode
     * @param $params
     * @return mixed
     */
    private  static function trigger_method($hookNode,$params) {
        $params = (!is_array($params)) ? array($params) : $params;

        //mixin user data, if any
        if(isset($hookNode["userData"])){
            $params['userData'] =$hookNode["userData"];
        }

        //mixin callee, if any
        $params['callee'] = $hookNode["class"];


        if (isset($hookNode["function"]))
        {
            return call_user_func($hookNode["function"],$params);
        }
        elseif (isset($hookNode["class"]))
        {
            return call_user_func_array(Array($hookNode["class"],$hookNode["method"]), Array($params));
        }
    }



}