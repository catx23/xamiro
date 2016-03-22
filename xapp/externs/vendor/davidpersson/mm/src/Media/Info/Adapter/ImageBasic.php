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

/**
 * This adapter makes us of image related PHP functions which
 * act directly on a given stream or file. It aims to deliver
 * some few but most common values (i.e. width and height).
 */
class ImageBasic extends \mm\Media\Info\Adapter {

	protected $_object;

	public function __construct($file) {
		$this->_object = $file;
	}

	public function all() {
		$data = getimagesize($this->_object);

		$result = [
			'width' => $data[0],
			'height' => $data[1]
		];
		if (isset($data['channels'])) {
			$result['channels'] = $data['channels'];
		}
		if (isset($data['bits'])) {
			$result['bits'] = $data['bits'];
		}
		return $result;
	}

	public function get($name) {
		$data = $this->all();

		if (isset($data[$name])) {
			return $data[$name];
		}
	}
}

?>