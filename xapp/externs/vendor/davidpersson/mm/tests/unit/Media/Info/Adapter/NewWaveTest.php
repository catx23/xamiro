<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Media\Info\Adapter;

use mm\Media\Info\Adapter\NewWave;

class NewWaveTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data';
		$this->_data = dirname(dirname(dirname((dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testAll() {
		$source = "{$this->_files}/audio_wave.wav";
		$subject = new NewWave($source);

		$result = $subject->all();
		$this->assertInternalType('array', $result);

		$this->assertArrayHasKey('samples', $result);
	}

	public function testAllAndGetSymmetry() {
		$source = "{$this->_files}/audio_wave.wav";
		$subject = new NewWave($source);

		$results = $subject->all();

		foreach ($results as $name => $value)  {
			$result = $subject->get($name);
			$this->assertEquals($value, $result, "Result for name `{$name}`.");
		}
	}
}

?>