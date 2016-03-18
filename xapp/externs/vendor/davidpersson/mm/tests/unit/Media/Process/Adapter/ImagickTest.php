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
use mm\Media\Process\Adapter\Imagick;

class ImagickTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (!extension_loaded('imagick')) {
			$this->markTestSkipped('The `imagick` extension is not available.');
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
		$subject = new Imagick($source);

		$this->assertEquals(70, $subject->width());
		$this->assertEquals(54, $subject->height());

		fclose($source);
	}

	public function testDimensionsPdf() {
		if (!$this->_hasGhostscript()) {
			$this->markTestSkipped('The `imagick` extension lacks ghostscript support.');
		}

		$source = fopen("{$this->_files}/application_pdf.pdf", 'r');
		$subject = new Imagick($source);

		$this->assertEquals(595, $subject->width());
		$this->assertEquals(842, $subject->height());

		fclose($source);
	}

	public function testStore() {
		$source = fopen("{$this->_files}/image_png.png", 'r');
		$target = fopen('php://temp', 'w+');

		$subject = new Imagick($source);
		$result = $subject->store($target);
		$this->assertTrue($result);

		fclose($source);
		fclose($target);
	}

	public function testConvertImageToImage() {
		$source = fopen("{$this->_files}/image_png.png", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new Imagick($source);
		$subject->convert('image/jpeg');
		$result = $subject->store($target);

		$this->assertTrue($result);
		$this->assertEquals('image/jpeg', Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testConvertDocumentToImage() {
		if (!$this->_hasGhostscript()) {
			$this->markTestSkipped('The `imagick` extension lacks ghostscript support.');
		}

		$source = fopen("{$this->_files}/application_pdf.pdf", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new Imagick($source);
		$subject->convert('image/jpeg');
		$result = $subject->store($target);

		$this->assertTrue($result);
		$this->assertEquals('image/jpeg', Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testConvertMultipageDocumentToImage() {
		if (!$this->_hasGhostscript()) {
			$this->markTestSkipped('The `imagick` extension lacks ghostscript support.');
		}

		$source = fopen("{$this->_files}/application_pdf_multipage.pdf", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new Imagick($source);
		$subject->convert('image/jpeg');
		$result = $subject->store($target);

		$this->assertTrue($result);
		$this->assertEquals('image/jpeg', Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testPassthru() {
		$source = fopen("{$this->_files}/image_png.png", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new Imagick($source);
		$subject->passthru('setFormat', 'jpeg');
		$result = $subject->store($target);

		$this->assertTrue($result);
		$this->assertEquals('image/jpeg', Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testCrop() {
		$source = fopen("{$this->_files}/image_landscape.png", 'r');
		$subject = new Imagick($source);
		// original size is 400x200

		$result = $subject->crop(10, 10, 100, 50);
		$this->assertTrue($result);

		$this->assertEquals(100, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testResize() {
		$source = fopen("{$this->_files}/image_landscape.png", 'r');
		$subject = new Imagick($source);
		// original size is 400x200

		$result = $subject->resize(100, 50);
		$this->assertTrue($result);

		$this->assertEquals(100, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testCropAndResize() {
		$source = fopen("{$this->_files}/image_landscape.png", 'r');
		$subject = new Imagick($source);
		// original size is 400x200

		$result = $subject->cropAndResize(10, 10, 100, 50, 70, 50);
		$this->assertTrue($result);

		$this->assertEquals(70, $subject->width());
		$this->assertEquals(50, $subject->height());
	}

	public function testProfile() {
		$source = fopen("{$this->_files}/image_landscape.png", 'r');
		$subject = new Imagick($source);

		$profile = file_get_contents("{$this->_data}/sRGB_IEC61966-2-1_black_scaled.icc");
		$result = $subject->profile('icc', $profile);
		$this->assertTrue($result);

		$result = $subject->profile('icc');
		$this->assertEquals($profile, $result);
	}

	public function testStrip() {
		$source = fopen("{$this->_files}/image_landscape.png", 'r');
		$subject = new Imagick($source);

		$profile = file_get_contents("{$this->_data}/sRGB_IEC61966-2-1_black_scaled.icc");
		$subject->profile('icc', $profile);

		$result = $subject->strip('icc');
		$this->assertTrue($result);

		$result = $subject->profile('icc');
		$this->assertFalse($result);
	}

	public function testDepth() {
		$source = fopen("{$this->_files}/image_png.png", 'r'); // this one has 16 bit
		$subject = new Imagick($source);

		$reduced = fopen('php://temp', 'w+');

		$result = $subject->depth(8);
		$subject->store($reduced);

		$sourceMeta = fstat($source);
		$reducedMeta = fstat($reduced);

		$this->assertTrue($result);
		$this->assertLessThan($sourceMeta['size'], $reducedMeta['size']);

		fclose($source);
		fclose($reduced);
	}

	public function testCompressPng() {
		 // Test just first 4 because after that strangely the size goes up again
		for ($i = 1; $i <= 4; $i++) {
			$source = fopen("{$this->_files}/image_png.png", 'r');

			$uncompressed = fopen('php://temp', 'w+');
			$compressed = fopen('php://temp', 'w+');

			$subject = new Imagick($source);
			$subject->compress(0);
			$subject->store($uncompressed);

			$subject->compress($i + 0.5); // Use adaptive filter
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

			$subject = new Imagick($source);

			$subject->compress(0);
			$subject->store($uncompressed);

			$subject->compress($i);
			$subject->store($compressed);

			$uncompressedMeta = fstat($uncompressed);
			$compressedMeta = fstat($compressed);

			if (!$uncompressedMeta['size']) {
				return $this->markTestSkipped('Imagick compression is not working correctly.');
			}

			$this->assertLessThan(
				$uncompressedMeta['size'], $compressedMeta['size'], "Compr. `{$i}`."
			);

			fclose($source);
			fclose($uncompressed);
			fclose($compressed);
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