<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\mocks\Media\Process;

class ImageMock extends \mm\Media\Process\Image {

	public function testBoxify($width, $height, $gravity = 'center') {
		return parent::_boxify($width, $height, $gravity);
	}

	public function testNormalizeDimensions($width, $height, $recalculateBy = 'ratio') {
		return parent::_normalizeDimensions($width, $height, $recalculateBy);
	}
}

?>