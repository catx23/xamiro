<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\functional\Media\Process;

use mm\Mime\Type;
use mm\Media\Process;
use mm\Media\Process\Document;
use mm\Media\Process\Video;
use mm\tests\mocks\Media\Process\Adapter\GenericMock;
use mm\tests\mocks\Media\Process\Adapter\GenericNameMock;

class ConversionTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';

		Type::config('magic', [
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/magic.db'
		]);
		Type::config('glob', [
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/glob.db'
		]);
	}

	public function testMediaChangeButSameAdapter() {
		Process::config([
			'image' => new GenericMock(null),
			'document' => new GenericMock(null)
		]);
		$media = new Document([
			'source' => "{$this->_files}/application_pdf.pdf",
			'adapter' => new GenericMock(null)
		]);
		$result = $media->convert('image/jpg');
		$this->assertInstanceOf('\mm\Media\Process\Image', $result);
	}

	public function testMediaChangeDifferentAdapter() {
		Process::config([
			'image' => new GenericMock(null),
			'video' => new GenericNameMock(null)
		]);
		$source = fopen("{$this->_files}/video_theora_notag.ogv", 'r');
		$storeFrom = fopen("{$this->_files}/image_jpg.jpg", 'r');

		$adapter = new GenericNameMock($source);
		$adapter->storeCopyFromStream = $storeFrom;

		$media = new Video(compact('adapter'));
		$result = $media->convert('image/jpg');
		$this->assertInstanceOf('\mm\Media\Process\Image', $result);

		fclose($source);
		fclose($storeFrom);
	}
}

?>