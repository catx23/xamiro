<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Mime\Type\Glob\Adapter;

use mm\Mime\Type\Glob\Adapter\Freedesktop;

class FreedesktopTest extends \PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';
}

	public function testToArray() {
		$file = $this->_files . '/glob_freedesktop_snippet.db';
		$this->subject = new Freedesktop(compact('file'));

		$result = $this->subject->to('array');
		$this->assertEquals(55, count($result));
	}

	public function testAnalyzeFail() {
		$file = $this->_files . '/glob_freedesktop_snippet.db';
		$this->subject = new Freedesktop(compact('file'));

		$result = $this->subject->analyze('');
		$this->assertEquals([], $result);
	}

	public function testAnalyze() {
		$file = $this->_files . '/glob_freedesktop_snippet.db';
		$this->subject = new Freedesktop(compact('file'));

		$files = [
			'file.bz2' => ['application/x-bzip'],
			'file.css' => ['text/css'],
			'file.gif' => ['image/gif'],
			'file.gz' => ['application/x-gzip'],
			'file.class' => ['application/x-java'],
			'file.js' => ['application/javascript'],
			'file.pdf' => ['application/pdf'],
			'file.po' => ['text/x-gettext-translation'],
			'file.pot' => [
				'application/vnd.ms-powerpoint', 'text/x-gettext-translation-template'
			],
			'file.mo' => ['application/x-gettext-translation'],
			'file.txt' => ['text/plain'],
			'file.doc' => ['application/msword'],
			'file.odt' => ['application/vnd.oasis.opendocument.text'],
			'file.tar' => ['application/x-tar'],
			'file.xhtml' => ['application/xhtml+xml'],
			'file.xml' => ['application/xml']
		];
		foreach ($files as $file => $mimeTypes) {
			$this->assertEquals($mimeTypes, $this->subject->analyze($file), "File `{$file}`.");
		}
	}

	public function testAnalyzeReverse() {
		$file = $this->_files . '/glob_freedesktop_snippet.db';
		$this->subject = new Freedesktop(compact('file'));

		$files = [
			'application/x-bzip' => ['bz2', 'bz'],
			'text/css' => ['css'],
			'image/gif' => ['gif'],
			'application/x-gzip' => ['gz'],
			'application/x-java' => ['class'],
			'application/javascript' => ['js'],
			'application/pdf' => ['pdf'],
			'text/x-gettext-translation' => ['po'],
			'application/vnd.ms-powerpoint' => ['pot'],
			'text/x-gettext-translation-template' => ['pot'],
			'application/x-gettext-translation' => ['gmo', 'mo'],
			'text/plain' => ['txt'],
			'application/msword' => ['doc'],
			'application/vnd.oasis.opendocument.text' => ['odt'],
			'application/x-tar' => ['tar'],
			'application/xhtml+xml' => ['xhtml'],
			'application/xml' => ['xbl', 'xml']
		];
		foreach ($files as $mimeType => $exts) {
			$result = $this->subject->analyze($mimeType, true);
			$this->assertEquals($exts, $result, "File `{$mimeType}`.");
		}
	}
}

?>