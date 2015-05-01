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
 * Basic annotation implementation.
 *
 * @author     David Grudl
 */
class XApp_Reflection_Annotation extends XApp_Object implements XApp_Reflection_IAnnotation
{

	public function __construct(array $values)
	{
		foreach ($values as $k => $v) {
			$this->$k = $v;
		}
	}


	/**
	 * Returns default annotation.
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

}
