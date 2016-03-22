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
 * Reports information about a classes variable.
 *
 * @author     David Grudl
 * @property-read XApp_Reflection_ClassType $declaringClass
 * @property-read XApp_Reflection_IAnnotation[][] $annotations
 * @property-read string $description
 * @property-read string $name
 * @property  mixed $value
 * @property-read bool $public
 * @property-read bool $private
 * @property-read bool $protected
 * @property-read bool $static
 * @property-read bool $default
 * @property-read int $modifiers
 * @property-read string $docComment
 * @property-write bool $accessible
 */
class Property extends ReflectionProperty
{

	public function __toString()
	{
		return parent::getDeclaringClass()->getName() . '::$' . $this->getName();
	}


	/********************* Reflection layer ****************d*g**/


	/**
	 * @return XApp_Reflection_ClassType
	 */
	public function getDeclaringClass()
	{
		return new XApp_Reflection_ClassType(parent::getDeclaringClass()->getName());
	}


	/********************* Nette\Annotations support ****************d*g**/


	/**
	 * Has property specified annotation?
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
