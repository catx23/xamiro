<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\Media\Info\Adapter;

use Imagick as ImagickCore;

/**
 * This media info adapter allows for interfacing with ImageMagick through
 * the `imagick` pecl extension (which must be loaded in order to use this adapter).
 *
 * @link http://php.net/imagick
 * @link http://www.imagemagick.org
 */
class Imagick extends \mm\Media\Info\Adapter {

	protected $_object;

	protected $_map = [
		'width' => 'getImageWidth',
		'height' => 'getImageHeight'
	];

	public function __construct($file) {
		$this->_object = new ImagickCore($file);
	}

	public function __destruct() {
		if ($this->_object) {
			$this->_object->clear();
		}
	}

	public function all() {
		$results = [];

		foreach (array_keys($this->_map) as $name) {
			$results[$name] = $this->get($name);
		}
		return $results;
	}

	public function get($name, $args = []) {
		if (method_exists($this, $name)) {
			$object = $this;
			$method = $name;
		} elseif (isset($this->_map[$name])) {
			$object = $this->_object;
			$method = $this->_map[$name];
		} else {
			return;
		}
		return $args ? call_user_func_array([$object, $method], $args) : $object->{$method}();
	}

	/**
	 * Retrieve colors unique to the object.
	 *
	 * This method operates on a clone of the object as we're going to
	 * manipulate it first and we don't want the underlying object to change,
	 * treating it as read-only.
	 *
	 * @param integer $spread Maximum number of colors to retrieve.
	 * @return array Retrieved colors, each color is represnted by an array
	 *               itself. The elements in that array are R, G, B values
	 *               and indexed numerically in that order i.e. `array(100, 57, 33)`.
	 */
	public function colors($spread = 20) {
		$colors = [];

		$object = clone $this->_object;
		$object->quantizeImage($spread, ImagickCore::COLORSPACE_RGB, 0, false, false);
		$object->uniqueImageColors();

		$rows = $object->getPixelIterator();
		$rows->resetIterator();

		while ($row = $rows->getNextIteratorRow()) {
			foreach ($row as $pixel) {
				$colors[] = array_values($pixel->getColor());
			}
		}
		return $colors;
	}
}

?>