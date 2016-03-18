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
 * Reports information about a function.
 *
 * @author     David Grudl
 * @property-read array $defaultParameters
 * @property-read bool $closure
 * @property-read XApp_Reflection_Extension $extension
 * @property-read XApp_Reflection_Parameter[] $parameters
 * @property-read bool $disabled
 * @property-read bool $deprecated
 * @property-read bool $internal
 * @property-read bool $userDefined
 * @property-read string $docComment
 * @property-read int $endLine
 * @property-read string $extensionName
 * @property-read string $fileName
 * @property-read string $name
 * @property-read string $namespaceName
 * @property-read int $numberOfParameters
 * @property-read int $numberOfRequiredParameters
 * @property-read string $shortName
 * @property-read int $startLine
 * @property-read array $staticVariables
 */
class XApp_Reflection_GlobalFunction extends ReflectionFunction
{
	/** @var string|Closure */
	private $value;


	public function __construct($name)
	{
		parent::__construct($this->value = $name);
	}


	/**
	 * @deprecated
	 */
	public function toCallback()
	{
		return new XApp_Utils_Callback($this->value);
	}


	public function __toString()
	{
		return $this->getName() . '()';
	}


	public function getClosure()
	{
		return $this->isClosure() ? $this->value : NULL;
	}


	/********************* Reflection layer ****************d*g**/


	/**
	 * @return XApp_Reflection_Extension
	 */
	public function getExtension()
	{
		return ($name = $this->getExtensionName()) ? new XApp_Reflection_Extension($name) : NULL;
	}


	/**
	 * @return XApp_Reflection_Parameter[]
	 */
	public function getParameters()
	{
		foreach ($res = parent::getParameters() as $key => $val) {
			$res[$key] = new XApp_Reflection_Parameter($this->value, $val->getName());
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
