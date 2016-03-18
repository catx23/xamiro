<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Media\Process\Adapter;

use mm\Mime\Type;
use mm\Media\Process\Adapter\Gd;

class GdTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (!extension_loaded('gd')) {
			$this->markTestSkipped('The `gd` extension is not available.');
		}

		$this->_files = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data';
		$this->_data = dirname(dirname(dirname((dirname(dirname(dirname(__FILE__))))))) .'/data';

		Type::config('magic', [
			'adapter' => 'Freedesktop',
			'file' => "{$this->_data}/magic.db"
		]);
		Type::config('glob', [
			'adapter' => 'Freedesktop',
			'file' => "{$this->_data}/glob.db"
		]);
	}

	public function testDimensions() {
		$source = fopen("{$this->_files}/image_png.png", 'r');
		$subject = new Gd($source);

		$this->assertEquals(70, $subject->width());
		$this->assertEquals(54, $subject->height());

		fclose($source);
	}

	public function testStore() {
		$source = fopen("{$this->_files}/image_png.png", 'r');
		$target = fopen('php://temp', 'w+');

		$subject = new Gd($source);
		$result = $subject->store($target);
		$this->assertTrue($result);

		fclose($source);
		fclose($target);
	}

	public function testConvertImageToImage() {
		$source = fopen("{$this->_files}/image_png.png", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new Gd($source);
		$subject->convert('image/jpeg');
		$result = $subject->store($target);

		$this->assertTrue($result);
		$this->assertEquals('image/jpeg', Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testCrop() {
		$source = fopen("{$this->_files}/image_landscape.png", 'r');
		$subject = new Gd($source);
		// original size is 400x200

		$result = $subject->crop(10, 10, 100, 50);
		$this->assertTrue($result);

		$this->assertEquals(100, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testResize() {
		$source = fopen("{$this->_files}/image_landscape.png", 'r');
		$subject = new Gd($source);
		// original size is 400x200

		$result = $subject->resize(100, 50);
		$this->assertTrue($result);

		$this->assertEquals(100, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testCropAndResize() {
		$source = fopen("{$this->_files}/image_landscape.png", 'r');
		$subject = new Gd($source);
		// original size is 400x200

		$result = $subject->cropAndResize(10, 10, 100, 50, 70, 50);
		$this->assertTrue($result);

		$this->assertEquals(70, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testCompressPng() {
		for ($i = 1; $i < 10; $i++) {
			$source = fopen("{$this->_files}/image_png.png", 'r');

			$uncompressed = fopen('php://temp', 'w+');
			$compressed = fopen('php://temp', 'w+');

			$subject = new Gd($source);
			$subject->compress(0);
			$subject->store($uncompressed);

			$subject->compress($i);
			$subject->store($compressed);

			$uncompressedMeta = fstat($uncompressed);
			$compressedMeta = fstat($compressed);

			$this->assertLessThan(
				$uncompressedMeta['size'], $compressedMeta['size'], "Compr. `{$i}`."
			);

			fclose($source);
			fclose($uncompressed);
			fclose($compressed);
		}
	}

	public function testCompressJpeg() {
		for ($i = 1; $i < 10; $i++) {
			$source = fopen("{$this->_files}/image_jpg.jpg", 'r');

			$uncompressed = fopen('php://temp', 'w+');
			$compressed = fopen('php://temp', 'w+');

			$subject = new Gd($source);
			$subject->compress(0);
			$subject->store($uncompressed);

			$subject->compress($i);
			$subject->store($compressed);

			$uncompressedMeta = fstat($uncompressed);
			$compressedMeta = fstat($compressed);

			$this->assertLessThan(
				$uncompressedMeta['size'], $compressedMeta['size'], "Compr. `{$i}`."
			);

			fclose($source);
			fclose($uncompressed);
			fclose($compressed);
		}
	}
}

?>