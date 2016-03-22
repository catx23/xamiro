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
 * `Media\Process` is the media processing manager class and provides
 * a configurable factory method.
 */
class Process {

	protected static $_config;

	public static function config(array $config = []) {
		if (!$config) {
			return static::$_config;
		}
		static::$_config = $config;
	}

	/**
	 * This factory method takes a source or an instance of an adapter,
	 * guesses the type of media maps it to a media processing class
	 * and instantiates it.
	 *
	 * @param array $config Valid values are:
	 *        - `'source'`: An absolute path, a file or an open handle or
	 *                      a MIME type if `'adapter'` is an instance.
	 *        - `'adapter'`: A name or instance of a media adapter (i.e. `'Gd'`).
	 * @return \mm\Media\Process\Generic An instance of a subclass of `\mm\Media\Process\Generic` or
	 *          if type could not be mapped an instance of the that class itself.
	 */
	public static function factory(array $config = []) {
		$default = ['source' => null, 'adapter' => null];
		extract($config + $default);

		if (!$source) {
			throw new BadMethodCallException("No source given.");
		}
		$name = Type::guessName($source);
		$class = "\mm\Media\Process\\" . ucfirst($name);

		if (!$adapter) {
			if (!isset(static::$_config[$name])) {
				throw new Exception("No adapter configured for media name `{$name}`.");
			}
			$adapter = static::$_config[$name];
		}
		return new $class(compact('source', 'adapter'));
	}
}

?>