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

use mm\Media\Process\Adapter\FfmpegShell;
use mm\Mime\Type;

class FfmpegShellTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
			exec("ffmpeg.exe -version>> nul 2>&1", $out, $return);
		} else {
			exec("ffmpeg -version &> /dev/null", $out, $return);
		}

		if ($return != 0) {
			$this->markTestSkipped('The `ffmpeg` command is not available.');
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

	public function testStore() {
		$source = fopen('php://temp', 'r+');
		$target = fopen('php://temp', 'w+');

		fwrite($source, 'test');
		$subject = new FfmpegShell($source);
		$subject->store($target);
		$this->assertEquals('test', stream_get_contents($target, -1, 0));

		fclose($source);
		fclose($target);
	}

	public function testConvertToImage() {
		$source = fopen("{$this->_files}/video_theora_comments.ogv", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new FfmpegShell($source);
		$subject->convert('image/png');
		$result = $subject->store($target);

		$this->assertTrue($result);
		$this->assertEquals('image/png', Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testConvertToVideo() {
		$source = fopen("{$this->_files}/video_theora_comments.ogv", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new FfmpegShell($source);
		$subject->convert('video/mpeg');
		$result = $subject->store($target);

		$this->assertTrue($result);
		$this->assertEquals('video/mpeg', Type::guessType($target));

		fclose($source);
		fclose($target);
	}

	public function testPassthru() {
		$source = fopen("{$this->_files}/video_theora_comments.ogv", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new FfmpegShell($source);
		$subject->passthru('s', '50x100');
		$subject->store($target);

		fclose($source);

		$subject = new FfmpegShell($target);
		$this->assertEquals(50, $subject->width());
		$this->assertEquals(100, $subject->height());

		fclose($target);
	}

	public function testDimensions() {
		$source = fopen("{$this->_files}/video_theora_comments.ogv", 'r');
		$subject = new FfmpegShell($source);

		$this->assertEquals(320, $subject->width());
		$this->assertEquals(176, $subject->height());

		fclose($source);
	}
}

?>