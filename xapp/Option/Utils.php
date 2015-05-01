<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp-Commander
 */
/***
 *  Aliases and utils for xapp_..._option functions
 *
 * */
if(!function_exists('xo_get'))
{
    /**
     * Alias for xapp_get_option
     *
     * @param null $key
     * @param null $mixed
     * @param null $default
     * @return mixed|null
     */
    function xo_get($key = null, &$mixed = null, $default = null) {
        if($mixed==null){
            $mixed = XApp_Options_Utils::optionCallee();
        }
        return xapp_get_option($key,$mixed,$default);
    }
}
if(!function_exists('xo_as_instance'))
{
    /**
     * Turns a xapp option into an instance, expects string
     *
     * @param null $key
     * @param null $mixed
     * @param null $default
     * @return mixed|null
     */
    function xo_as_instance($key = null, &$mixed = null, $default = null,$options) {
        if($mixed==null){
            $mixed = XApp_Options_Utils::optionCallee();
        }
        $opt = xapp_get_option($key,$mixed,$default);
        if(!empty($opt) && is_string($opt) && class_exists($opt)){
            return new $opt($options);
        }
        return null;
    }
}
if(!function_exists('xo_set'))
{
    /**
     * Alias for xapp_set_option
     *
     * @param null $key
     * @param null $value
     * @param null $mixed
     * @param bool $reset
     * @return mixed|null
     */
    function xo_set($key = null, $value = null, &$mixed = null, $reset = false) {
        if($mixed==null){
            $mixed = XApp_Options_Utils::optionCallee();
        }
        return xapp_set_option ($key,$value,$mixed,$reset);
    }
}
if(!function_exists('xo_has'))
{
    /**
     * Alias for xapp_has_option
     *
     * @param null $key
     * @param null $mixed
     * @param bool $strict
     * @return bool
     */
    function xo_has($key = null, &$mixed = null, $strict = false) {
        if($mixed==null){
            $mixed = XApp_Options_Utils::optionCallee();
        }
        return xapp_has_option ($key,$mixed,$strict);
    }
}

if(!function_exists('xo_merge'))
{
    function xo_merge($set, &$mixed=null) {

        // check if it's a json encoded string
        if (is_string($set)) {
            $set = json_decode($set);
        }
        if ($set!==NULL)
        {
            xapp_set_options($set, $mixed);
        }
    }
}

class XApp_Options_Utils
{
    /**
     * key to specify the options dictionary
     *
     * @var string
     */
    const OPTIONS_DICT = 'optionsDict';

    /**
     * key to specify the options mandatory map
     *
     * @var string
     */

    const OPTIONS_DEFAULT = 'optionsDefault';

    /**
     * key to specify the option's default values map
     *
     * @var string
     */
    const OPTIONS_RULE = 'optionsRule';

    /**
     * key to specify the option's pre-processor map
     *
     * @var string
     */
    const OPTIONS_PREPROCESSORS = 'optionsPreProcessors';

    /**
     * callee for options since options can only be called static or from within subclass
     * of this class or via xapp function error handling the object must be either in callee
     * depth 3 or 4
     *
     * @error 10128
     * @return object|string
     */
    public  static function optionCallee()
    {
        $caller = self::callee(null, 4);
        if(!is_object($caller))
        {
            return self::callee(null, 3);
        }else{
            return $caller;
        }
    }

    /**
     * tries to determine which class is calling this function either by passing
     * object or calling it statically.
     *
     * @error 10117
     * @param null|object $object expects optional object instance
     * @param int $depth expects the debug depth
     * @return object returns the caller object
     */
    public static function callee($object = null, $depth = 1)
    {
        $class = get_called_class();
        if($object !== null && is_object($object))
        {
            return $object;
        }else{
            $d = debug_backtrace();
            if(isset($d[(int)$depth]) && isset($d[(int)$depth]['type']) && $d[(int)$depth]['type'] == '->'){
                return $d[(int)$depth]['object'];
            }else if(isset($d[(int)$depth]) && isset($d[(int)$depth]['type']) && $d[(int)$depth]['type'] == '::'){
                return $d[(int)$depth]['class'];
            }
        }
        return $class;
    }

    /**
     * Assigns static properties "$optionsDict,$optionsRule,$optionsPreprocessor" to $destinationObject
     *
     *
     * @param $destinationObject
     * @param $optionsDict
     * @param $optionsRule
     * @param $optionsPreprocessor
     */
    public static function optionfy($destinationObject,$optionsDict,$optionsRule,$optionsPreprocessor) {
        if (isset($destinationObject::${self::OPTIONS_DICT}))
        {
            $destinationObject::${self::OPTIONS_DICT} = $optionsDict;
        }
        if (isset($destinationObject::${self::OPTIONS_RULE}))
        {
            $destinationObject::${self::OPTIONS_RULE} = $optionsRule;
        }
        if (isset($destinationObject::${self::OPTIONS_PREPROCESSORS}))
        {
            $destinationObject::${self::OPTIONS_PREPROCESSORS} = $optionsPreprocessor;
        }
        xapp_has_option();
    }


    /**
     *  Converts function arguments to an array of options
     *
     * @param $arguments_to_get string  :   (optional) selects arguments, using ranges (1-3,3-5) or lists (1,3,5) or (arg1,arg3...)
     * @return array                    :   array of options
     */
    public static function get_function_arguments($arguments_to_get='') {
        // Catch arguments from caller function/method
        $callers = debug_backtrace();
        $caller = $callers[1];

        if (function_exists($caller["function"])) {
            $reflection = new ReflectionFunction($caller["function"]);
            $parameters = $reflection->getParameters();
        }
        elseif (method_exists($caller["class"],$caller["function"])) {
            $reflection = new ReflectionMethod($caller["class"],$caller["function"]);
            $parameters = $reflection->getParameters();
        }

        $argArray = Array();
        $retArray = Array();

        foreach($caller["args"] as $n=>$argument)
        {
            $key = $parameters[$n]->name;
            $argArray[$key] = $argument;
        }

        // parse $arguments_to_get

        // case: empty
        if ($arguments_to_get=='')
        {
            $retArray=$argArray;
        }
        // case: list of arguments arg,arg,...
        elseif (strpos($arguments_to_get,",") !== FALSE)
        {
            $onlykeys = explode(",",$arguments_to_get);
            foreach($onlykeys as $key)
            {
                if (is_numeric($key))
                {
                    $key--;
                    $retArray[$parameters[$key]->name]=$caller["args"][$key];
                }
                else
                {
                    $retArray[$key]=$argArray[$key];
                }
            }
        }
        // case: range of arguments 1-5
        elseif (strpos($arguments_to_get,"-") !== FALSE) {
            $range = explode("-",$arguments_to_get);

            $start = intval($range[0]) - 1;
            $end = intval($range[1]);
            for ($i = $start; $i < $end; $i++)
            {
                $retArray[$parameters[$i]->name]=$caller["args"][$i];
            }

        }
        return $retArray;
    }

    /**
     *  Validates an array of options using options metainformation
     *
     * @param $optionsValues    array  :   options
     * @param $optionsMeta      array  :   options meta: dictionary, rules, defaults and pre-processors
     * @param $errors           array  :   returns errors found during validation
     */
    public static function validate($optionsValues,$optionsMeta,&$errors) {

        // apply defaults
        if (array_key_exists(self::OPTIONS_DEFAULT,$optionsMeta))
        {
            foreach($optionsMeta[self::OPTIONS_DEFAULT] as $key => $default)
            {
                if ( !isset($optionsValues[$key]) )
                {
                    $optionsValues[$key] = $default;
                }
            }
        }

        // apply pre-processors
        if (array_key_exists(self::OPTIONS_PREPROCESSORS,$optionsMeta))
        {
            foreach($optionsMeta[self::OPTIONS_PREPROCESSORS] as $key => $methods)
            {
                if ( isset($optionsValues[$key]) )
                {
                    foreach($methods as $method) {
                        $optionsValues[$key] = call_user_func($method,$optionsValues[$key]);

                    }
                }
            }
        }

        // check types
        if (array_key_exists(self::OPTIONS_DICT,$optionsMeta))
        {
            foreach($optionsMeta[self::OPTIONS_DICT] as $key => $type)
            {
                if ( isset($optionsValues[$key]) )
                {
                    if ( !xapp_is($type,$optionsValues[$key]) )
                    {
                        $errors[] = XAPP_TEXT_FORMATTED("INCORRECT_OPTION_TYPE",Array($optionsValues[$key],$type));
                    }
                }
            }
        }

        // check rules
        if (array_key_exists(self::OPTIONS_RULE,$optionsMeta))
        {
            foreach($optionsMeta[self::OPTIONS_RULE] as $key => $requested)
            {
                if ( !isset($optionsValues[$key]) )
                {
                    $errors[] = XAPP_TEXT_FORMATTED("REQUIRED_OPTION_NOT_PROVIDED",Array($key));
                }
            }
        }

    }
}