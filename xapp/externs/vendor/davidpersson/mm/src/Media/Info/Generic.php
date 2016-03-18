<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\Media\Info;

use InvalidArgumentException;

/**
 * `Generic` is the base class for all media information types. It provides
 * methods used by all type classes.
 */
class Generic {

	use \mm\Media\NameTrait;

	protected $_adapters = [];

	/**
	 * Constructor
	 *
	 * @param array $config Configuration values (at least `source` and one adapter must be provided):
	 *              - `source`:  An absolute path to a file.
	 *              - `adapters`: Names (i.e. `['BasicGd', 'GetId3']`) or instances of
	 *                            adapters to use when constructing the instance.
	 * @return void
	 */
	public function __construct(array $config = []) {
		$default = ['source' => null, 'adapters' => []];
		extract($config + $default);

		if (!$source) {
			throw new InvalidArgumentException("No source given.");
		}
		if (!is_string($source) || !is_file($source)) {
			throw new InvalidArgumentException("Given source is not a file.");
		}
		if (!$adapters) {
			throw new InvalidArgumentException("No adapters given by the `adapters` setting.");
		}

		foreach ($adapters as &$adapter) {
			if (is_object($adapter)) {
				continue;
			}
			if ($adapter) {
				$class = "\mm\Media\Info\\Adapter\\{$adapter}";
				$adapter = new $class($source);
			}
		}
		$this->_adapters = $adapters;
	}

	/**
	 * Magic method, enabling retrieving metadata by calling the metadata field's
	 * name as a method upon the object.
	 *
	 * @param string $method Passed as the field name to the `get()` method.
	 * @param array $args Not used.
	 * @return mixed The result of `get($method)`
	 */
	public function __call($method, $args) {
		foreach ($this->_adapters as $adapter) {
			if ($result = $adapter->get($method, $args)) {
				return $result;
			}
		}
	}

	/**
	 * Retrieves all possible information from media info type
	 * classes and adapters.
	 *
	 * @return array
	 */
	public function all() {
		$methods = array_diff(
			get_class_methods($this),
			get_class_methods('\mm\Media\Info\Generic')
		);
		$results = [];

		foreach ($methods as $method) {
			$results[$method] = $this->{$method}();
		}
		foreach ($this->_adapters as $adapter) {
			$results += $adapter->all();
		}
		return $results;
	}

	/**
	 * Retrieves information for a given (field) name.
	 *
	 * Common fields, supported by all adapters:
	 *   - `'quality'`
	 *
	 * Common fields, supported by most audio and video adapters:
	 *   - `'artist'`
	 *   - `'title'`
	 *   - `'album'`
	 *   - `'year'`: The year as an integer
	 *   - `'track'`: The number of the track as an integer
	 *   - `'duration'`: The duration in seconds
	 *   - `'samplingRate'`: Sampling rate as an integer (http://en.wikipedia.org/wiki/Sampling_rate)
	 *   - `'bitRate'`: Bit rate as an integer (http://en.wikipedia.org/wiki/Bit_rate)
	 *
	 * Common fields, supported by most image and video adapters:
	 *   - `'width'`: In pixels
	 *   - `'height'`: In pixels
	 *   - `'megapixel'`
	 *   - `'ratio'`
	 *   - `'knowRatio'`
	 *   - `'colors'`
	 *
	 * Common fields, supported by some text and document adapters:
	 *   - `'characters'`
	 *   - `'fleschScore'`:
	 *   - `'lexicalDensity'`: In percent (40-50 is easy to read, 60-70 is hard to read)
	 *   - `'sentences'`
	 *   - `'syllables'`
	 *   - `'words'`
	 *
	 * @param string $name Retrieve data just for the given name.
	 * @param array $args Arguments passed to adapter or media method.
	 * @return mixed A scalar value.
	 */
	public function get($name, $args = []) {
		if (method_exists($this, $name)) {
			return $args ? call_user_func_array([$this, $name], $args) : $this->{$name}();
		}
		foreach ($this->_adapters as $adapter) {
			if ($result = $adapter->get($name, $args)) {
				return $result;
			}
		}
	}
}

?>