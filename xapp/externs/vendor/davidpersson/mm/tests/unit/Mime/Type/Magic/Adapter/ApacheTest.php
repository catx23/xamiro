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

use mm\Mime\Type\Magic\Adapter\Apache;

class ApacheTest extends \PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';
	}

	public function testToArray() {
		$file = $this->_files . '/magic_apache_snippet.db';
		$this->subject = new Apache(compact('file'));

		$result = $this->subject->to('array');
		$this->assertEquals(38, count($result));
	}

	public function testAnalyzeFail() {
		$file = $this->_files . '/magic_apache_snippet.db';
		$this->subject = new Apache(compact('file'));

		$handle = fopen('php://memory', 'r');
		$result = $this->subject->analyze($handle);
		fclose($handle);
		$this->assertNull($result);
	}

	public function testWithSnippetDb() {
		$file = $this->_files . '/magic_apache_snippet.db';
		$this->subject = new Apache(compact('file'));

		/* @todo Commented fail but are present in data */
		$files = [
			'image_gif.gif' => 'image/gif',
			'application_pdf.pdf' => 'application/pdf',
			'postscript_snippet.ps' => 'application/postscript',
			'wave_snippet.wav' => 'audio/x-wav',
			// 'gzip_snippet.gz' => 'application/x-gzip',
			'text_html_snippet.html' => 'text/html',
			'image_jpeg_snippet.jpg' => 'image/jpeg',
			'text_rtf_snippet.rtf' => 'application/rtf',
			// 'ms_word_snippet.doc' => 'application/msword',
			// 'audio_mpeg_snippet.mp3' => 'audio/mpeg',
			// 'text_plain_snippet.txt' => 'text/plain'
		];

		foreach ($files as $file => $mimeTypes) {
			$handle = fopen($this->_files . '/' . $file, 'r');
			$this->assertContains($this->subject->analyze($handle), (array) $mimeTypes, "File `{$file}`.");
			fclose($handle);
		}
	}
}

?>