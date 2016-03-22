<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Mime\Type\Magic\Adapter;

use mm\Mime\Type\Magic\Adapter\Fileinfo;

class FileinfoTest extends \PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (extension_loaded('fileinfo')) {
			$this->subject = new Fileinfo();
		} else {
			$this->markTestSkipped('The `fileinfo` extension is not available.');
		}
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
	}

	public function testToArray() {
		$this->setExpectedException('BadMethodCallException');
		$result = $this->subject->to('array');
	}

	public function testAnalyzeFail() {
		$handle = fopen('php://memory', 'r');
		$result = $this->subject->analyze($handle);
		fclose($handle);
		$this->assertNull($result);
	}

	public function testAnalyzeSeekedAnonymous() {
		$source = fopen($this->_files . '/image_png.png', 'r');
		$handle = fopen('php://temp', 'r+');
		stream_copy_to_stream($source, $handle);

		fclose($source);
		fseek($handle, -1, SEEK_END);

		$expected  = 'image/png; charset=binary';

		$result = $this->subject->analyze($handle);
		$this->assertEquals($expected, $result);

		fclose($handle);
	}
}

?>