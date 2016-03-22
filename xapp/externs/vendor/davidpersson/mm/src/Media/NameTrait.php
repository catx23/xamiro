<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\Media;

trait NameTrait {

	/**
	 * Checks if the name of the type (i.e. `'generic'` or `'image'`)
	 * equals the provided one.
	 *
	 * @param string $name Name of the type to compare against.
	 * @return boolean
	 */
	public function is($name) {
		return $this->name() == $name;
	}

	/**
	 * Returns the lowercase name of the type.
	 *
	 * @return string I.e. `'generic'` or `'image'`.
	 */
	public function name() {
		$class = explode('\\', get_class($this));
		return strtolower(array_pop($class));
	}
}

?>