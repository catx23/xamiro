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

use mm\Media\Info;
use mm\Mime\Type;
use mm\tests\mocks\Media\Info\Adapter\GenericMock;

class InfoTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(__FILE__))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(__FILE__)))) .'/data';

		Info::config([
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
		$result = Info::factory(['source' => "{$this->_files}/image_jpg.jpg"]);
		$this->assertTrue(is_a($result, '\mm\Media\Info\Image'));

		$result = Info::factory(['source' => "{$this->_files}/image_png.png"]);
		$this->assertTrue(is_a($result, '\mm\Media\Info\Image'));

		$result = Info::factory(['source' => "{$this->_files}/application_pdf.pdf"]);
		$this->assertTrue(is_a($result, '\mm\Media\Info\Document'));

		$result = Info::factory(['source' => "{$this->_files}/audio_ogg_snippet.ogg"]);
		$this->assertInstanceOf('\mm\Media\Info\Audio', $result);
	}

	public function testMediaFactorySourceFailStream() {
		$this->setExpectedException('InvalidArgumentException');

		Info::factory([
			'source' => fopen("{$this->_files}/image_jpg.jpg", 'r')
		]);
	}
}

?>