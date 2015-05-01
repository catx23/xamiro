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
 * Reports information about a method.
 *
 * @author     David Grudl
 * @property-read array $defaultParameters
 * @property-read XApp_Reflection_ClassType $declaringClass
 * @property-read XApp_Reflection_Method $prototype
 * @property-read XApp_Reflection_Extension $extension
 * @property-read XApp_Reflection_Parameter[] $parameters
 * @property-read XApp_Reflection_IAnnotation[][] $annotations
 * @property-read string $description
 * @property-read bool $public
 * @property-read bool $private
 * @property-read bool $protected
 * @property-read bool $abstract
 * @property-read bool $final
 * @property-read bool $static
 * @property-read bool $constructor
 * @property-read bool $destructor
 * @property-read int $modifiers
 * @property-write bool $accessible
 * @property-read bool $closure
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
class XApp_Reflection_Method extends ReflectionMethod
{

	/**
	 * @param  string|object
	 * @param  string
	 * @return XApp_Reflection_Method
	 */
	public static function from($class, $method)
	{
		return new static(is_object($class) ? get_class($class) : $class, $method);
	}


	/**
	 * @deprecated
	 */
	public function toCallback()
	{
		return new XApp_Utils_Callback(parent::getDeclaringClass()->getName(), $this->getName());
	}


	public function __toString()
	{
		return parent::getDeclaringClass()->getName() . '::' . $this->getName() . '()';
	}


	/********************* Reflection layer ****************d*g**/


	/**
	 * @return XApp_Reflection_ClassType
	 */
	public function getDeclaringClass()
	{
		return new XApp_Reflection_ClassType(parent::getDeclaringClass()->getName());
	}


	/**
	 * @return XApp_Reflection_Method
	 */
	public function getPrototype()
	{
		$prototype = parent::getPrototype();
		return new XApp_Reflection_Method($prototype->getDeclaringClass()->getName(), $prototype->getName());
	}


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
		$me = array(parent::getDeclaringClass()->getName(), $this->getName());
		foreach ($res = parent::getParameters() as $key => $val) {
			$res[$key] = new XApp_Reflection_Parameter($me, $val->getName());
		}
		return $res;
	}


	/********************* Nette\Annotations support ****************d*g**/


	/**
	 * Has method specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$res = XApp_Reflection_AnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}


	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return XApp_Reflection_IAnnotation
	 */
	public function getAnnotation($name)
	{
		$res = XApp_Reflection_AnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}


	/**
	 * Returns all annotations.
	 * @return XApp_Reflection_IAnnotation[][]
	 */
	public function getAnnotations()
	{
		return XApp_Reflection_AnnotationsParser::getAll($this);
	}


	/**
	 * Returns value of annotation 'description'.
	 * @return string
	 */
	public function getDescription()
	{
		return $this->getAnnotation('description');
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
