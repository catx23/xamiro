<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Mime\Type\Magic\Adapter;

use mm\Mime\Type\Magic\Adapter\Freedesktop;

class FreedesktopTest extends \PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testToArray() {
		$file = $this->_files . '/magic_freedesktop_snippet.db';
		$this->subject = new Freedesktop(compact('file'));

		$result = $this->subject->to('array');
		$this->assertEquals(24, count($result));
	}

	public function testAnalyzeFail() {
		$file = $this->_files . '/magic_freedesktop_snippet.db';
		$this->subject = new Freedesktop(compact('file'));

		$handle = fopen('php://memory', 'r');
		$result = $this->subject->analyze($handle);
		fclose($handle);
		$this->assertNull($result);
	}
}

?>