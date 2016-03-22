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

xapp_import('xapp.Reflection.AnnotationsParser');

/**
 * Reports information about a class.
 *
 * @author     David Grudl
 * @property-read Method $constructor
 * @property-read XApp_Reflection_Extension $extension
 * @property-read XApp_Reflection_ClassType[] $interfaces
 * @property-read Method[] $methods
 * @property-read XApp_Reflection_ClassType $parentClass
 * @property-read Property[] $properties
 * @property-read XApp_Reflection_IAnnotation[][] $annotations
 * @property-read string $description
 * @property-read string $name
 * @property-read bool $internal
 * @property-read bool $userDefined
 * @property-read bool $instantiable
 * @property-read string $fileName
 * @property-read int $startLine
 * @property-read int $endLine
 * @property-read string $docComment
 * @property-read mixed[] $constants
 * @property-read string[] $interfaceNames
 * @property-read bool $interface
 * @property-read bool $abstract
 * @property-read bool $final
 * @property-read int $modifiers
 * @property-read array $staticProperties
 * @property-read array $defaultProperties
 * @property-read bool $iterateable
 * @property-read string $extensionName
 * @property-read string $namespaceName
 * @property-read string $shortName
 */
class XApp_Reflection_ClassType extends ReflectionClass
{


	/**
	 * @param  string|object
	 * @return XApp_Reflection_ClassType
	 */
	public static function from($class)
	{
		return new static($class);
	}


	public function __toString()
	{
		return $this->getName();
	}


	/**
	 * @param  string
	 * @return bool
	 */
	public function is($type)
	{
		return $this->isSubclassOf($type) || strcasecmp($this->getName(), ltrim($type, '\\')) === 0;
	}


	/********************* Reflection layer ****************d*g**/


	/**
	 * @return Method|NULL
	 */
	public function getConstructor()
	{
		return ($ref = parent::getConstructor()) ? Method::from($this->getName(), $ref->getName()) : NULL;
	}


	/**
	 * @return XApp_Reflection_Extension|NULL
	 */
	public function getExtension()
	{
		return ($name = $this->getExtensionName()) ? new XApp_Reflection_Extension($name) : NULL;
	}


	/**
	 * @return XApp_Reflection_ClassType[]
	 */
	public function getInterfaces()
	{
		$res = array();
		foreach (parent::getInterfaceNames() as $val) {
			$res[$val] = new static($val);
		}
		return $res;
	}


	/**
	 * @return Method
	 */
	public function getMethod($name)
	{
		return new Method($this->getName(), $name);
	}


	/**
	 * @return Method[]
	 */
	public function getMethods($filter = -1)
	{
		foreach ($res = parent::getMethods($filter) as $key => $val) {
			$res[$key] = new Method($this->getName(), $val->getName());
		}
		return $res;
	}


	/**
	 * @return XApp_Reflection_ClassType|NULL
	 */
	public function getParentClass()
	{
		return ($ref = parent::getParentClass()) ? new static($ref->getName()) : NULL;
	}


	/**
	 * @return Property[]
	 */
	public function getProperties($filter = -1)
	{
		foreach ($res = parent::getProperties($filter) as $key => $val) {
			$res[$key] = new Property($this->getName(), $val->getName());
		}
		return $res;
	}


	/**
	 * @return Property
	 */
	public function getProperty($name)
	{
		return new Property($this->getName(), $name);
	}


	/********************* Nette\Annotations support ****************d*g**/


	/**
	 * Has class specified annotation?
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


	/********************* XApp_Object behaviour *******************/


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
