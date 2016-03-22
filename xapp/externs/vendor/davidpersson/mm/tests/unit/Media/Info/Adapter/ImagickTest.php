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

use mm\Media\Info\Adapter\Imagick;

class ImagickTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The `imagick` extension is not available.');
		}

		$this->_files = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data';
		$this->_data = dirname(dirname(dirname((dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testAll() {
		$source = "{$this->_files}/image_png.png";
		$subject = new Imagick($source);

		$result = $subject->all();
		$this->assertInternalType('array', $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);

		$this->assertEquals(70, $result['width']);
		$this->assertEquals(54, $result['height']);

		$source = "{$this->_files}/image_jpg.jpg";
		$subject = new Imagick($source);

		$result = $subject->all();
		$this->assertInternalType('array', $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);
	}

	public function testAllPdf() {
		if (!$this->_hasGhostscript()) {
			$this->markTestSkipped('The `imagick` extension lacks ghostscript support.');
		}

		$source = "{$this->_files}/application_pdf.pdf";
		$subject = new Imagick($source);

		$result = $subject->all();
		$this->assertInternalType('array', $result);

		$this->assertArrayHasKey('width', $result);
		$this->assertArrayHasKey('height', $result);
	}

	public function testAllAndGetSymmetry() {
		$source = "{$this->_files}/image_png.png";
		$subject = new Imagick($source);

		$results = $subject->all();

		foreach ($results as $name => $value)  {
			$result = $subject->get($name);
			$this->assertEquals($value, $result, "Result for name `{$name}`.");
		}
	}

	protected function _hasGhostscript() {
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
			exec("gswin32c.exe -v>> nul 2>&1", $out, $return);
		} else {
			exec("gs -v &> /dev/null", $out, $return);
		}
		return $return == 0;
	}
}

?>