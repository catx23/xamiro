<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Media\Info;

use mm\Media\Info\Audio;

class AudioTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(__FILE__)))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/data';
	}

	public function testQuality() {
		$adapter = $this->getMock(
			'\mm\tests\mocks\Media\Info\Adapter\GenericMock',
			['get'],
			[null]
		);
		$media = new Audio([
			'source' => "{$this->_files}/audio_ogg_snippet.ogg", // not used by adapter
			'adapters' => [$adapter]
		]);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('bitRate'))
			->will($this->returnValue(128000));
		$result = $media->get('quality');
		$this->assertEquals(2, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('bitRate'))
			->will($this->returnValue(128000));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('bitRateMax'))
			->will($this->returnValue(5555555));
		$result = $media->get('quality');
		$this->assertEquals(1, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('bitRate'))
			->will($this->returnValue(100000));
		$result = $media->get('quality');
		$this->assertEquals(2, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('bitRate'))
			->will($this->returnValue(200000));
		$result = $media->get('quality');
		$this->assertEquals(3, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('bitRate'))
			->will($this->returnValue(1));
		$result = $media->get('quality');
		$this->assertEquals(1, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('bitRate'))
			->will($this->returnValue(320000));
		$result = $media->get('quality');
		$this->assertEquals(5, $result);
	}
}

?>