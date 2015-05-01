<?php

/*******************************************************************************
 * Uses global cutpoints to log calls when entering and/or exiting methods.
 ******************************************************************************/
abstract class XApp_TracerMixin {
    public static $trace = false;
    
    function BEFORE_ALL() {
        if (self::$trace) {
            $args = func_get_args();
            $method = $args[0];
            $args = array_slice($args, 1);
            error_log("Entering $method with arguments " . print_r($args, true));
        }
    }

    function AFTER_ALL() {
        if (self::$trace) {
            $args = func_get_args();
            $num_args = func_num_args();
            $method = $args[0];
            $retval = $args[$num_args-1];
            error_log("Leaving $method with return value " . print_r($retval, true));
        }
    }
}

/*******************************************************************************
 * Enables the dynamic addition of new methods or expander classes with
 *    extra functionality after the class has been defined.
 ******************************************************************************/
class XApp_ExpandableClassException extends Exception {}

abstract class XApp_ExpandableClassMixin {
    static protected $dynamic_methods = array();
    static protected $dynamic_mixins = array();

    /**
     * Helper method to generate an argument string to eval()
     * 
     * @param <type> $num_args
     * @return <type>
     */
    protected static function makeArgStr($num_args) {
        $m_args = array();
        for ($i = 0; $i < $num_args; $i++) {
            $m_args[] = "\$args[$i]";
        }
        return implode(', ', $m_args);
    }

    protected static function getClassHierarchy($base_object) {
        $ancestry = array();
        $klass = is_object($base_object) ? get_class($base_object) : $base_object;
        $ancestry[] = $klass;
        while (($klass = get_parent_class($klass)) !== false) {
            $ancestry[] = $klass;
        }
        return $ancestry;
    }

    /***************************************************************************
     * Dynamic method registration
     **************************************************************************/
    /**
     * Check if a method has been dynamically added to a class
     * @param <type> $klass
     * @param <type> $method
     * @return <type>
     */
    protected static function isMethodRegistered($klass, $method) {
        if (!array_key_exists($klass, self::$dynamic_methods)) {
            self::$dynamic_methods[$klass] = array();
        }
        return isset(self::$dynamic_methods[$klass][$method]);
    }

    /**
     * Dynamically add a method from a class. Methods should be functions with
     * a signature of:
     * function NEW_ClassName__MethodName($obj, ...) { ... }
     *    where $obj is the receiver for the $this pointer.
     * @param <type> $klass
     * @param <type> $method
     */
    public static function registerMethod($klass, $method) {
        //The name of the function to be called
        if (!self::isMethodRegistered($klass, $method)) {
            $dynamic_method_name = "NEW_".$klass."__".$method;
            self::$dynamic_methods[$klass][$method] = $dynamic_method_name;
          //  xapp_dump(self::$dynamic_methods);
        }
    }

    /**
     * Dynamically remove a method from a class
     * @param <type> $klass
     * @param <type> $method
     */
    public static function unregisterMethod($klass, $method) {
        if (self::isMethodRegistered($klass, $method)) {
            unset(self::$dynamic_methods[$klass][$method]);
        }
    }

    /***************************************************************************
     * Dynamic mixin registration
     **************************************************************************/
    /**
     * Get all of the expanders in the system
     * 
     * @return type 
     */
    public static function getExpanderRegistry() {
        return self::$dynamic_mixins;
    }
    
    /**
     * Get the expanders for a given class
     * 
     * @param <type> $klass
     * @return <type>
     */
    public static function getExpanders($klass) {
        if (!array_key_exists($klass, self::$dynamic_mixins)) {
            self::$dynamic_mixins[$klass] = array();
        }
        return self::$dynamic_mixins[$klass];
    }

    /**
     * Check if an expander mixin has been dynamically added to a class
     *
     * @param <type> $klass
     * @param <type> $expander
     * @return <type>
     */
    public static function isExpanderRegistered($klass, $expander) {
        return in_array($expander, self::getExpanders($klass));
    }

    /**
     * Dynamically add a mixin to a class.
     *
     * @param <type> $klass
     * @param <type> $expander
     */
    public static function registerExpander($klass, $expander) {
        //The name of the function to be called
        if (!self::isExpanderRegistered($klass, $expander)) {
            self::$dynamic_mixins[$klass][] = $expander;
        }
    }

    /**
     * Dynamically remove a mixin from a class
     *
     * @param <type> $klass
     * @param <type> $expander
     */
    public static function unregisterExpanders($klass, $expander) {
        if (self::isExpanderRegistered($klass, $expander)) {
            self::$dynamic_mixins[$klass] =
                array_values(array_diff(self::$dynamic_mixins[$klass], array($expander)));
        }
    }

    /**
     * Get all dynamically added methods
     * 
     * @param <type> $object_or_klass
     * @param <type> $method
     */
    public static function get_dynamic_class_methods($object_or_klass) {
        $methods = array();
        $klasses = self::getClassHierarchy($object_or_klass);
        foreach ($klasses as $klass) {
            //Add the dynamically added methods
            $dyn_methods = isset(self::$dynamic_methods[$klass]) ? array_keys(self::$dynamic_methods[$klass]) : array();
            $methods = array_merge($methods, $dyn_methods);

            //Add the expander methods
            $expanders = self::getExpanders($klass);
            foreach ($expanders as $expander) {
                //Get the methods of the expander class
                $exp_methods = get_class_methods($expander);
                $methods = array_merge($methods, $exp_methods);
            }
        }

        return $methods;
    }

    /**
     * Find where a dynamic method for a class is registered
     * 
     * @param <type> $object_or_klass
     * @param <type> $method
     * @return <type> 
     */
    public static function find_dynamic_class_method($object_or_klass, $method) {
        $klasses = self::getClassHierarchy($object_or_klass);
        foreach ($klasses as $klass) {
            //Return the dynamic method if found
            if (self::isMethodRegistered($klass, $method)) {
                return array(null, "NEW_".$klass."__".$method);
            }

            //Get the expanders for the class
            $expanders = self::getExpanders($klass);
            foreach ($expanders as $expander) {
                //Get the methods of the expander class
                $methods = get_class_methods($expander);
                //Found the method!
                if (in_array($method, $methods)) {
                    return array($expander, $method);
                }
            }
        }

        return null;
    }

    /**
     * Checks if a class has the method dynamically added
     *
     * @param <type> $object_or_klass
     * @param <type> $method
     * @return <type>
     */
    public static function dynamic_method_exists($object_or_klass, $method) {
        return is_array(self::find_dynamic_class_method($object_or_klass, $method));
    }

    /**
     * Here look for expander classes that may define the method called on the object
     *    When using this mixin, the combinator for this method should be rerouted to
     *    the resulting class's __call method
     * 
     * @param <type> $method
     * @param <type> $args
     * @return <type>
     */
    public function ___call($method, $args) {
        //Get the list of classes we need to check for expander registration
        $klasses = self::getClassHierarchy($this);

        foreach ($klasses as $klass) {
            //Check if this is a dynamically added method, if so call the method
            if (self::isMethodRegistered($klass, $method)) {
                //A dynamically added method
                array_unshift($args, $this);
                $dynamic_method_name = "NEW_".$klass."__".$method;
                eval("\$result =& $dynamic_method_name(".self::makeArgStr(count($args)).");");
                return $result;
            }

            //Get the expanders for the class
            $expanders = self::getExpanders($klass);
            foreach ($expanders as $expander) {
                //Get the methods of the expander class
                $methods = get_class_methods($expander);
                if (is_null($methods) && !class_exists($expander)) {
                    throw new Exception("Expander for `$klass` - `$expander` - is not a valid class. Please check the expander registration.");
                }
                //Found the method! Call it and return the result!
                if (in_array($method, $methods)) {
                    eval("\$result =& $expander::$method(".self::makeArgStr(count($args)).");");
                    return $result;
                }
            }
        }

        //The method was not found...Trigger an exception.
        throw new XApp_ExpandableClassException('Call to undefined method '.get_class($this).'::'.$method);
    }
}

/**
 * Just a pseudonym for the XApp_ExpandableClassMixin that we can inherit from
 */
abstract class XApp_ExpandableClass extends XApp_ExpandableClassMixin {
    public function __call($method, $args) {
        return parent::___call($method, $args);
    }
}