<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\integration\Mime\Type\Magic\Adapter;

use mm\Mime\Type\Magic\Adapter\Fileinfo;

class FileinfoSystemTest extends \PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		if (extension_loaded('fileinfo')) {
			$this->subject = new Fileinfo();
		} else {
			$this->markTestSkipped('The `fileinfo` extension is not available.');
		}
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
	}

	public function testAnalyze() {
		$files = [
			'image_gif.gif' => 'image/gif; charset=binary',
			'application_pdf.pdf' => 'application/pdf; charset=binary',
			'text_html_snippet.html' => 'text/html; charset=us-ascii',
			'image_jpeg_snippet.jpg' => 'image/jpeg; charset=binary',
			'video_theora_notag.ogv' => 'video/ogg; charset=binary',
			'audio_vorbis_notag.ogg' => 'audio/ogg; charset=binary',
			'video_webm_snippet.webm' => 'video/webm; charset=binary'
		];

		foreach ($files as $file => $mimeTypes) {
			$handle = fopen($this->_files . '/' . $file, 'r');
			$this->assertContains($this->subject->analyze($handle), (array) $mimeTypes, "File `{$file}`.");
			fclose($handle);
		}
	}
}

?>