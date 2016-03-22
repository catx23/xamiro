<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\mocks\Media\Info\Adapter;

class GenericMock extends \mm\Media\Info\Adapter {

	public function __construct($file) {}

	public function all() {
		return [];
	}

	public function get($name) {}
}

?>