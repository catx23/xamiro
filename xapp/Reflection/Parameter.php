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
 * Reports information about a method's parameter.
 *
 * @author     David Grudl
 * @property-read XApp_Reflection_ClassType $class
 * @property-read string $className
 * @property-read XApp_Reflection_ClassType $declaringClass
 * @property-read Method $declaringFunction
 * @property-read string $name
 * @property-read bool $passedByReference
 * @property-read bool $array
 * @property-read int $position
 * @property-read bool $optional
 * @property-read bool $defaultValueAvailable
 * @property-read mixed $defaultValue
 */
class XApp_Reflection_Parameter extends ReflectionParameter
{
	/** @var mixed */
	private $function;


	public function __construct($function, $parameter)
	{
		parent::__construct($this->function = $function, $parameter);
	}


	/**
	 * @return XApp_Reflection_ClassType
	 */
	public function getClass()
	{
		return ($ref = parent::getClass()) ? new XApp_Reflection_ClassType($ref->getName()) : NULL;
	}


	/**
	 * @return string
	 */
	public function getClassName()
	{
		try {
			return ($ref = parent::getClass()) ? $ref->getName() : NULL;
		} catch (\ReflectionException $e) {
			if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
				return $m[1];
			}
			throw $e;
		}
	}


	/**
	 * @return XApp_Reflection_ClassType
	 */
	public function getDeclaringClass()
	{
		return ($ref = parent::getDeclaringClass()) ? new XApp_Reflection_ClassType($ref->getName()) : NULL;
	}


	/**
	 * @return XApp_Reflection_Method|XApp_Reflection_GlobalFunction
	 */
	public function getDeclaringFunction()
	{
		return is_array($this->function)
			? new Method($this->function[0], $this->function[1])
			: new XApp_Reflection_GlobalFunction($this->function);
	}


	/**
	 * @return bool
	 */
	public function isDefaultValueAvailable()
	{
		if (PHP_VERSION_ID === 50316) { // PHP bug #62988
			try {
				$this->getDefaultValue();
				return TRUE;
			} catch (\ReflectionException $e) {
				return FALSE;
			}
		}
		return parent::isDefaultValueAvailable();
	}


	public function __toString()
	{
		return '$' . parent::getName() . ' in ' . $this->getDeclaringFunction();
	}


	/********************* Nette\Object behaviour ****************d*g**/


	/**
	 * @deprecated
	 */
	public static function getReflection()
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		return new XApp_Reflection_ClassType(get_called_class());
	}


	public function __call($name, $args)
	{
		return XApp_ObjectMixin::call($this, $name, $args);
	}


	public function &__get($name)
	{
		return XApp_ObjectMixin::get($this, $name);
	}


	public function __set($name, $value)
	{
		XApp_ObjectMixin::set($this, $name, $value);
	}


	public function __isset($name)
	{
		return XApp_ObjectMixin::has($this, $name);
	}


	public function __unset($name)
	{
		XApp_ObjectMixin::remove($this, $name);
	}

}
