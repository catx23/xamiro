<?php
/**
 * @author     Guenter Baumgart
 * @author     David Grudl
 * @copyright 2004 David Grudl (http://davidgrudl.com)
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @license : http://opensource.org/licenses/BSD-3-Clause
 * @package XApp-Commander
 *
 * @original header : This file is part of the Nette Framework (http://nette.org)
 */

/**
 * XApp_Object is the ultimate ancestor of all instantiable classes.
 *
 * It defines some handful methods and enhances object core of PHP:
 *   - access to undeclared members throws exceptions
 *   - support for conventional properties with getters and setters
 *   - support for event raising functionality
 *   - ability to add new methods to class (extension methods)
 *
 * Properties is a syntactic sugar which allows access public getter and setter
 * methods as normal object variables. A property is defined by a getter method
 * or setter method (no setter method means read-only property).
 * <code>
 * $val = $obj->label;     // equivalent to $val = $obj->getLabel();
 * $obj->label = 'Nette';  // equivalent to $obj->setLabel('Nette');
 * </code>
 * Property names are case-sensitive, and they are written in the camelCaps
 * or PascalCaps.
 *
 * Event functionality is provided by declaration of property named 'on{Something}'
 * Multiple handlers are allowed.
 * <code>
 * public $onClick;                // declaration in class
 * $this->onClick[] = 'callback';  // attaching event handler
 * if (!empty($this->onClick)) ... // are there any handlers?
 * $this->onClick($sender, $arg);  // raises the event with arguments
 * </code>
 *
 * Adding method to class (i.e. to all instances) works similar to JavaScript
 * prototype property. The syntax for adding a new method is:
 * <code>
 * MyClass::extensionMethod('newMethod', function(MyClass $obj, $arg, ...) { ... });
 * $obj = new MyClass;
 * $obj->newMethod($x);
 * </code>
 *
 * @author     David Grudl
 *
 * @property-read Nette\Reflection\ClassType $reflection
 */
xapp_import("xapp.Commons.ObjectMixin");

abstract class XApp_Object
{

	/**
	 * Access to reflection.
	 * @return XApp_Reflection_ClassType
	 */
	public static function getReflection()
	{
		return new XApp_Reflection_ClassType(get_called_class());
	}


	/**
	 * Call to undefined method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public function __call($name, $args)
	{
		error_log('name' . $name);
		return XApp_ObjectMixin::call($this, $name, $args);
	}


	/**
	 * Call to undefined static method.
	 * @param  string  method name (in lower case!)
	 * @param  array   arguments
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public static function __callStatic($name, $args)
	{
		return XApp_ObjectMixin::callStatic(get_called_class(), $name, $args);
	}


	/**
	 * Adding method to class.
	 * @param  string  method name
	 * @param  callable
	 * @return mixed
	 */
	public static function extensionMethod($name, $callback = NULL)
	{
		if (strpos($name, '::') === FALSE) {
			$class = get_called_class();
		} else {
			list($class, $name) = explode('::', $name);
			$rc = new ReflectionClass($class);
			$class = $rc->getName();
		}
		if ($callback === NULL) {
			return XApp_ObjectMixin::getExtensionMethod($class, $name);
		} else {
            XApp_ObjectMixin::setExtensionMethod($class, $name, $callback);
		}
	}


	/**
	 * Returns property value. Do not call directly.
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name)
	{
		return XApp_ObjectMixin::get($this, $name);
	}


	/**
	 * Sets value of a property. Do not call directly.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value)
	{
        XApp_ObjectMixin::set($this, $name, $value);
	}


	/**
	 * Is property defined?
	 * @param  string  property name
	 * @return bool
	 */
	public function __isset($name)
	{
		return XApp_ObjectMixin::has($this, $name);
	}


	/**
	 * Access to undeclared property.
	 * @param  string  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	public function __unset($name)
	{
        XApp_ObjectMixin::remove($this, $name);
	}

}
