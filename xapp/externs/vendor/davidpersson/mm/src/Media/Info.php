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

use mm\Mime\Type;
use Exception;
use BadMethodCallException;

/**
 * `Info` is the media information manager class and provides a configurable
 * factory method. In contrast to `Process` the `Info` type classes operate
 * with multiple adapters. This is possible due to the fact that the source'
 * state is not changed by the adapters (they're read only).
 */
class Info {

	protected static $_config;

	public static function config(array $config = []) {
		if (!$config) {
			return static::$_config;
		}
		static::$_config = $config;
	}

	/**
	 * This factory method takes a source or an instance of an adapter,
	 * guesses the type of media maps it to a media information class
	 * and instantiates it.
	 *
	 * @param array $config Valid values are:
	 *        - `'source'`: An absolute path to a file.
	 *        - `'adapters'`: Names or instances of media adapters (i.e. `['Gd']`).
	 * @return \mm\Media\Info\Generic An instance of a subclass of `\mm\Media\Info\Generic` or
	 *         if type could not be mapped an instance of the that class itself.
	 */
	public static function factory(array $config = []) {
		$default = ['source' => null, 'adapters' => []];
		extract($config + $default);

		if (!$source) {
			throw new BadMethodCallException("No source given.");
		}
		$name = Type::guessName($source);
		$class = "\mm\Media\Info\\" . ucfirst($name);

		if (!$adapters) {
			if (!isset(static::$_config[$name])) {
				throw new Exception("No adapters configured for media name `{$name}`.");
			}
			$adapters = static::$_config[$name];
		}
		return new $class(compact('source', 'adapters'));
	}
}

?>