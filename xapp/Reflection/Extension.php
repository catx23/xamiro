<?php

/**
 * @author     Guenter Baumgart
 * @author     David Grudl
 * @copyright 2004 David Grudl (http://davidgrudl.com)
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @license : New BSD http://opensource.org/licenses/BSD-3-Clause
 * @package XApp-Commander
 *
 * @original header : This file is part of the Nette Framework (http://nette.org)
 */


/**
 * Reports information about a extension.
 *
 * @author     David Grudl
 */
class XApp_Reflection_Extension extends ReflectionExtension
{

	public function __toString()
	{
		return $this->getName();
	}


	/********************* Reflection layer ****************d*g**/


	public function getClasses()
	{
		$res = array();
		foreach (parent::getClassNames() as $val) {
			$res[$val] = new XApp_Reflection_ClassType($val);
		}
		return $res;
	}


	public function getFunctions()
	{
		foreach ($res = parent::getFunctions() as $key => $val) {
			$res[$key] = new XApp_Reflection_GlobalFunction($key);
		}
		return $res;
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
