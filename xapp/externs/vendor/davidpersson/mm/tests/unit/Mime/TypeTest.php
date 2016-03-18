<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\unit\Mime;

use mm\Mime\Type;

class TypeTest extends \PHPUnit_Framework_TestCase {

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(__FILE__))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(__FILE__)))) .'/data';

		Type::config('magic', [
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/magic.db'
		]);
		Type::config('glob', [
			'adapter' => 'Freedesktop',
			'file' => $this->_data . '/glob.db'
		]);
	}

	protected function tearDown() {
		Type::reset();
	}

	public function testSimplify() {
		$this->assertEquals(
			'application/pdf',
			Type::simplify('application/x-pdf')
		);
		$this->assertEquals(
			'inode/directory',
			Type::simplify('x-inode/x-directory')
		);
		$this->assertEquals(
			'application/octet-stream',
			Type::simplify('application/octet-stream; encoding=compress')
		);
		$this->assertEquals(
			'application/test',
			Type::simplify('application/x-test; encoding=compress')
		);
		$this->assertEquals(
			'text/plain',
			Type::simplify('text/plain; charset=iso-8859-1')
		);
		$this->assertEquals(
			'text/plain',
			Type::simplify('text/plain charset=us-ascii')
		);
	}

	public function testGuessTypeFile() {
		$files = [
			'image_gif.gif' => 'image/gif',
			'application_pdf.pdf' => 'application/pdf',
			'postscript_snippet.ps' => 'application/postscript',
			'tar_snippet.tar' => 'application/x-tar',
			'wave_snippet.wav' => 'audio/x-wav',
			'text_html_snippet.html' => 'text/html',
			'code_php.php' => 'application/x-php',
			'image_png.png' => 'image/png',
			'video_flash_snippet.flv' => 'video/x-flv',
			'audio_apple_snippet.aiff' => 'audio/x-aiff',
			'flash_snippet.swf' => 'application/x-shockwave-flash',
			'video_snippet.mp4' => 'video/mp4',
			'audio_mpeg_snippet.m4a' => 'audio/mp4',
			'video_quicktime_snippet.mov' => 'video/quicktime'
		];
		foreach ($files as $file => $mimeType) {
			$this->assertEquals(
				$mimeType,
				Type::guessType("{$this->_files}/{$file}"),
				"File `{$file}`."
			);
		}
	}

	public function testGuessTypeFilename() {
		$files = [
			'test.gif' => 'image/gif',
			'test.pdf' => 'application/pdf',
			'test.ps' => 'application/postscript',
			'test.tar' => 'application/x-tar',
			'test.wav' => 'audio/x-wav',
			'test.html' => 'text/html',
			'test.php' => 'application/x-php',
			'test.png' => 'image/png',
			'test.flv' => 'video/x-flv',
			'test.aiff' => 'audio/x-aiff',
			'test.swf' => 'application/x-shockwave-flash',
			'test.mp4' => 'video/mp4',
			'test.m4v' => 'video/mp4',
			'test.m4a' => 'audio/mp4',
			'test.ogg' => 'audio/ogg',
			'test.oga' => 'audio/ogg',
			'test.ogv' => 'video/ogg'
		];
		foreach ($files as $file => $mimeType) {
			$this->assertEquals(
				$mimeType,
				Type::guessType($file),
				"Filename `{$file}`."
			);
		}
	}

	public function testGuessTypeParanoid() {
		$this->assertEquals(
			'image/png',
			Type::guessType("{$this->_files}/image_png.jpg", ['paranoid' => true])
		);
		$this->assertEquals(
			'image/jpeg',
			Type::guessType("{$this->_files}/image_png.jpg", ['paranoid' => false])
		);
	}

	public function testGuessTypeFallback() {
		$files = [
			'generic_binary' => 'application/octet-stream',
			'generic_text' => 'text/plain'
		];
		foreach ($files as $file => $mimeType) {
			$this->assertEquals(
				$mimeType,
				Type::guessType("{$this->_files}/{$file}"),
				"File `{$file}`."
			);
		}
	}

	public function testGuessTypePreferredTypes() {
		$result = Type::guessType('test.ogg');
		$this->assertEquals('audio/ogg', $result);
	}

	public function testGuessExtensionFail() {
		$this->assertNull(Type::guessExtension('i-m-not-a-mime-type'));
		$this->assertNull(Type::guessExtension('/tmp/i-do-not-exist'));
	}

	public function testGuessExtensionFilename() {
		$this->assertEquals('txt', Type::guessExtension('/tmp/i-do-not-exist.txt'));
	}

	public function testGuessExtensionMimeType() {
		$this->assertEquals('jpg', Type::guessExtension('image/jpeg'));
		$this->assertEquals('xhtml', Type::guessExtension('application/xhtml+xml'));
		$this->assertEquals('bin', Type::guessExtension('application/octet-stream'));
		$this->assertEquals('wav', Type::guessExtension('audio/x-wav'));
		$this->assertEquals('oga', Type::guessExtension('audio/ogg'));
		$this->assertEquals('m4a', Type::guessExtension('audio/mp4'));
		$this->assertEquals('ogv', Type::guessExtension('video/ogg'));
		$this->assertEquals('mp4', Type::guessExtension('video/mp4'));
		$this->assertEquals('mov', Type::guessExtension('video/quicktime'));
	}

	public function testGuessExtensionResource() {
		$handleA = fopen("{$this->_files}/application_pdf.pdf", 'r');
		$handleB = fopen('php://temp', 'r+');

		stream_copy_to_stream($handleA, $handleB);

		$this->assertEquals('pdf', Type::guessExtension($handleA));
		$this->assertEquals('pdf', Type::guessExtension($handleB));

		fclose($handleA);
		fclose($handleB);
	}

	public function testGuessNameMimeType() {
		$map = [
			'video/webm' => 'video',
			'video/x-msvideo' => 'video',
			'video/mp4' => 'video',
			'application/ogg' => 'audio',
			'application/octet-stream' => 'generic',
			'text/unknown' => 'generic',
			'image/jpeg' => 'image',
			'image/tiff' => 'image',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'document'
		];
		foreach ($map as $mimeType => $name) {
			$this->assertEquals($name, Type::guessName($mimeType), "MIME type `{$mimeType}`.");
		}
	}

	public function testGuessNameFilename() {
		$map = [
			'test' => 'generic',
			'test.jpg' => 'image',
			'test.tif' => 'image',
			'test.pdf' => 'document',
			'path/to/test.pdf' => 'document'
		];
		foreach ($map as $mimeType => $name) {
			$this->assertEquals($name, Type::guessName($mimeType), "MIME type `{$mimeType}`.");
		}
	}

	public function testGuessNameFile() {
		$map = [
			'video_flash_snippet.flv' => 'video',
			'audio_ogg_snippet.ogg' => 'audio',
			'xml_snippet.xml' => 'generic',
			'image_png.png' => 'image',
		];
		foreach ($map as $file => $name) {
			$this->assertEquals(
				$name,
				Type::guessName($this->_files . '/' . $file),
				"File `{$file}`."
			);
		}
	}

	public function testGuessNameResource() {
		$handleA = fopen("{$this->_files}/application_pdf.pdf", 'r');
		$handleB = fopen('php://temp', 'r+');

		stream_copy_to_stream($handleA, $handleB);

		$this->assertEquals('document', Type::guessName($handleA));
		$this->assertEquals('document', Type::guessName($handleB));

		fclose($handleA);
		fclose($handleB);
	}
}

?>