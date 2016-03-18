<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Media\Info\Adapter;

use mm\Media\Info\Adapter\ImageBasic;

class ImageBasicTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (!function_exists('getimagesize')) {
			$this->markTestSkipped('The `getimagesize` function is not available.');
		}

		$this->_files = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data';
		$this->_data = dirname(dirname(dirname((dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testAll() {
		$source = "{$this->_files}/image_png.png";
		$subject = new ImageBasic($source);

		$result = $subject->all();
		$this->assertInternalType('array', $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);
		$this->assertArrayHasKey('bits', $result);

		$this->assertEquals(70, $result['width']);
		$this->assertEquals(54, $result['height']);
		$this->assertEquals(16, $result['bits']);

		$source = "{$this->_files}/image_jpg.jpg";
		$subject = new ImageBasic($source);

		$result = $subject->all();
		$this->assertInternalType('array', $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);
		$this->assertArrayHasKey('channels', $result);
		$this->assertArrayHasKey('bits', $result);
	}

	public function testAllAndGetSymmetry() {
		$source = "{$this->_files}/image_png.png";
		$subject = new ImageBasic($source);

		$results = $subject->all();

		foreach ($results as $name => $value)  {
			$result = $subject->get($name);
			$this->assertEquals($value, $result, "Result for name `{$name}`.");
		}
	}
}

?>