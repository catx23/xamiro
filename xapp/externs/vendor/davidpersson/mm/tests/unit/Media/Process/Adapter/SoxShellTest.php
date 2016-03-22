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
use mm\Media\Process\Adapter\SoxShell;


class SoxShellTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'sox.exe' : 'sox';
		exec("{$command} --version", $out, $return);

		if ($return != 0) {
			$this->markTestSkipped('The `sox` command is not available.');
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
		$subject = new SoxShell($source);
		$subject->store($target);
		$this->assertEquals('test', stream_get_contents($target, -1, 0));

		fclose($source);
		fclose($target);
	}

	public function testConvert() {
		$source = fopen("{$this->_files}/audio_vorbis_comments.ogg", 'r');
		$target = fopen('php://temp', 'wb');

		$subject = new SoxShell($source);
		$subject->convert('audio/x-wav');
		$result = $subject->store($target);

		$this->assertTrue($result);
		$this->assertEquals('audio/x-wav', Type::guessType($target));

		fclose($source);
		fclose($target);
	}
}

?>