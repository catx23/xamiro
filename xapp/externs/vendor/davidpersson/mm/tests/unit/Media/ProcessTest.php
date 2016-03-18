<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Media;

use mm\Mime\Type;
use mm\Media\Process;
use mm\tests\mocks\Media\Process\Adapter\GenericMock;

class ProcessTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(__FILE__))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(__FILE__)))) .'/data';

		Process::config([
			'image' => new GenericMock(null),
			'audio' => new GenericMock(null),
			'document' => new GenericMock(null),
			'video' => new GenericMock(null)
		]);
		Type::config('magic', [
			'adapter' => 'Freedesktop',
			'file' => "{$this->_data}/magic.db"
		]);
		Type::config('glob', [
			'adapter' => 'Freedesktop',
			'file' => "{$this->_data}/glob.db"
		]);
	}

	public function testMediaFactorySourceFile() {
		$result = Process::factory(['source' => "{$this->_files}/image_jpg.jpg"]);
		$this->assertInstanceOf('\mm\Media\Process\Image', $result);

		$result = Process::factory(['source' => "{$this->_files}/image_png.png"]);
		$this->assertInstanceOf('\mm\Media\Process\Image', $result);

		$result = Process::factory(['source' => "{$this->_files}/application_pdf.pdf"]);
		$this->assertInstanceOf('\mm\Media\Process\Document', $result);

		$result = Process::factory(['source' => "{$this->_files}/audio_ogg_snippet.ogg"]);
		$this->assertInstanceOf('\mm\Media\Process\Audio', $result);
	}

	public function testMediaFactorySourceStream() {
		$result = Process::factory([
			'source' => fopen("{$this->_files}/image_jpg.jpg", 'r')
		]);
		$this->assertInstanceOf('\mm\Media\Process\Image', $result);
	}

	public function testMediaFactoryTransplantAdapter() {
		$result = Process::factory([
			'adapter' => new GenericMock(null),
			'source' => 'image/jpeg'
		]);
		$this->assertInstanceOf('\mm\Media\Process\Image', $result);
	}
}

?>