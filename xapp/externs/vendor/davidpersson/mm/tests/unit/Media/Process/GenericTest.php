<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Media\Process;

use mm\Media\Process\Generic;
use mm\tests\mocks\Media\Process\Adapter\GenericMock;

class GenericTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';
	}

	public function testConstruct() {
		$result = new Generic([
			'source' => "{$this->_files}/image_jpg.jpg",
			'adapter' => new GenericMock(null)
		]);
		$this->assertInternalType('object', $result);

		$result = new Generic([
			'source' => fopen("{$this->_files}/image_jpg.jpg", 'r'),
			'adapter' => new GenericMock(null)
		]);
		$this->assertInternalType('object', $result);

		$result = new Generic([
			'source' => "{$this->_files}/image_jpg.jpg",
			'adapter' => new GenericMock('test')
		]);
		$this->assertInternalType('object', $result);

		$result = new Generic([
			'adapter' => new GenericMock('test')
		]);
		$this->assertInternalType('object', $result);
	}

	public function testConstructFailWithNoArgs() {
		$this->setExpectedException('InvalidArgumentException');
		new Generic([]);
	}

	public function testConstructFailWithSourceButNoAdapter() {
		$this->setExpectedException('InvalidArgumentException');
		new Generic(['source' => "{$this->_files}/image_jpg.jpg"]);
	}

	public function testConstructFailWithStringAdapterButNoSource() {
		$this->setExpectedException('InvalidArgumentException');
		new Generic(['adapter' => 'Dummy']);
	}

	public function testName() {
		$result = new Generic([
			'source' => "{$this->_files}/image_jpg.jpg",
			'adapter' => new GenericMock(null)
		]);
		$this->assertEquals($result->name(), 'generic');
	}

	public function testStoreHonorsOverwrite() {
		$target = tempnam(sys_get_temp_dir(), 'mm_');
		touch($target);

		$media = new Generic([
			'source' => fopen('php://temp', 'r'),
			'adapter' => new GenericMock(null)
		]);

		$result = $media->store($target, ['overwrite' => true]);
		$this->assertFileExists($result);

		unlink($target);
		$result = $media->store($target);
		$this->assertFileExists($result);

		unlink($target);
	}

	public function testStoreHonorsOverwriteAndThrowsException() {
		$target = tempnam(sys_get_temp_dir(), 'mm_');
		touch($target);

		$media = new Generic([
			'source' => fopen('php://temp', 'r'),
			'adapter' => new GenericMock(null)
		]);

		$this->setExpectedException('Exception');
		$media->store($target);
	}

	public function testPassthru() {
		$result = new Generic([
			'source' => "{$this->_files}/image_jpg.jpg",
			'adapter' => new GenericMock(null)
		]);
		$this->assertEquals($result->passthru('depth', 8), true);
	}
}

?>