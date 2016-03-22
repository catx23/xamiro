<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2012 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\integration\Mime;

use mm\Mime\Type;

class TypeSystemTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(__FILE__))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(__FILE__)))) .'/data';

		Type::config('magic', [
			'adapter' => 'Fileinfo'
		]);
		Type::config('glob', [
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/glob.db'
		]);
	}

	protected function tearDown() {
		Type::reset();
	}

	public function testGuessTypeResource() {
		$files = [
			'image_png.png' => 'image/png'
		];
		foreach ($files as $file => $mimeType) {
			$this->assertEquals(
				$mimeType,
				Type::guessType($handle = fopen("{$this->_files}/{$file}", 'r')),
				"File `{$file}`."
			);
			fclose($handle);
		}
	}

	public function testGuessTypeFile() {
		$files = [
			'video_h264_qt_tag.mp4' => 'video/mp4'
		];
		foreach ($files as $file => $mimeType) {
			$this->assertEquals(
				$mimeType,
				Type::guessType("{$this->_files}/{$file}"),
				"File `{$file}`."
			);
		}
	}

	public function testGuessNameFile() {
		$map = [
			'video_h264_qt_tag.mp4' => 'video'
		];
		foreach ($map as $file => $name) {
			$this->assertEquals(
				$name,
				Type::guessName($this->_files . '/' . $file),
				"File `{$file}`."
			);
		}
	}
}

?>