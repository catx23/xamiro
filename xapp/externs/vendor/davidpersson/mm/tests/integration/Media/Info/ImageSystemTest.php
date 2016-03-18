<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\integration\Media\Info;

use mm\Mime\Type;
use mm\Media\Info\Image;

class ImageSystemTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';
	}

	public function testColors() {
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The `imagick` extension is not available.');
		}
		$media = new Image([
			'source' => "{$this->_files}/image_png.png",
			'adapters' => ['Imagick']
		]);
		$expected = array(
			[
				103,
				103,
				103,
				1
			],
			[
				209,
				210,
				202,
				1
			]
		);
		$result = $media->colors(2);
		$this->assertEquals($expected, $result);
	}
}

?>