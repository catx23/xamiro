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

use mm\Media\Info\Image;

class ImageTest extends \PHPUnit_Framework_TestCase {

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
		$media = new Image([
			'source' => "{$this->_files}/image_png.png", // not used by adapter
			'adapters' => [$adapter]
		]);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(1));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(1));
		$result = $media->get('quality');
		$this->assertEquals(1, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(500));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(700));
		$result = $media->get('quality');
		$this->assertEquals(1, $result);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(1500));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(1700));
		$result = $media->get('quality');
		$this->assertEquals(2, $result);
	}

	public function testRatio() {
		$adapter = $this->getMock(
			'\mm\tests\mocks\Media\Info\Adapter\GenericMock',
			['get'],
			[null]
		);
		$media = new Image([
			'source' => "{$this->_files}/image_png.png", // not used by adapter
			'adapters' => [$adapter]
		]);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(500));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(700));
		$result = $media->get('ratio');
		$this->assertEquals(500 / 700, $result);
	}

	public function testKnownRatio() {
		$adapter = $this->getMock(
			'\mm\tests\mocks\Media\Info\Adapter\GenericMock',
			['get'],
			[null]
		);
		$media = new Image([
			'source' => "{$this->_files}/image_png.png", // not used by adapter
			'adapters' => [$adapter]
		]);

		$adapter->expects($this->at(0))
			->method('get')->with($this->equalTo('width'))
			->will($this->returnValue(500));
		$adapter->expects($this->at(1))
			->method('get')->with($this->equalTo('height'))
			->will($this->returnValue(700));
		$result = $media->get('knownRatio');
		$this->assertEquals('1:√2', $result);
	}
}

?>