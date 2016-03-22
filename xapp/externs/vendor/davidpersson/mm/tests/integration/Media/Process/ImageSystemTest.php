<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\integration\Media\Process;

use mm\Mime\Type;
use mm\Media\Process\Image;
use Imagick as ImagickCore;

class ImageSystemTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';

		Type::config('magic', [
			'adapter' => 'Fileinfo'
		]);
		Type::config('glob', [
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/glob.db'
		]);
	}

	public function testTrim() {
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The `imagick` extension is not available.');
		}
		$media = new Image([
			'source' => "{$this->_files}/image_letterboxed.png",
			'adapter' => 'Imagick'
		]);
		$media->trim();
		$temporary = fopen('php://temp', 'w+');
		$media->store($temporary);
		rewind($temporary);

		$media = new ImagickCore();
		$media->readImageFile($temporary);

		$expected = 400;
		$result = $media->getImageWidth();
		$this->assertEquals($expected, $result);

		$expected = 200;
		$result = $media->getImageHeight();
		$this->assertEquals($expected, $result);
	}
}

?>