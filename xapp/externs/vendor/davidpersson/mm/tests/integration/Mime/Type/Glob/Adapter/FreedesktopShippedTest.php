<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\integration\Mime\Type\Glob\Adapter;

use mm\Mime\Type\Glob\Adapter\Freedesktop;

class FreedesktopShippedTest extends \PHPUnit_Framework_TestCase {

	public $subject;

	protected $_files;
	protected $_data;

	protected function setUp() {
		$this->_files = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/data';
		$this->_data = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) .'/data';

		$file = $this->_data . '/glob.db';
		$this->subject = new Freedesktop(compact('file'));
	}

	public function testAnalyze() {
		$files = [
			'file.avi' => 'video/x-msvideo',
			'file.gif' => 'image/gif',
			'file.pdf' => 'application/pdf',
			'file.ps' => 'application/postscript',
			'file.tar' => 'application/x-tar',
			'file.wav' => 'audio/x-wav',
			'file.3gp' => 'video/3gpp',
			'file.bz2' => 'application/x-bzip',
			'file.mp4' => 'video/mp4',
			'file.gz' => 'application/x-gzip',
			'file.html' => 'text/html',
			'file.jpg' => 'image/jpeg',
			'file.mpeg' => 'video/mpeg',
			'file.ogv' => 'video/ogg',
			'file.ogg' => 'audio/x-vorbis+ogg',
			'file.php' => 'application/x-php',
			'file.png' => 'image/png',
			'file.rtf' => 'application/rtf',
			'file.doc' => 'application/msword',
			'file.xml' => 'application/xml',
			'file.odt' => 'application/vnd.oasis.opendocument.text',
			'file.mp3' => 'audio/mpeg',
			'file.txt' => 'text/plain',
			'file.css' => 'text/css',
			'file.js' => 'application/javascript',
			'file.xhtml' => 'application/xhtml+xml',
			'file.po' => 'text/x-gettext-translation',
			'file.pot' => 'text/x-gettext-translation-template',
			'file.mo' => 'application/x-gettext-translation',
			'file.flv' => 'video/x-flv',
			'file.snd' => 'audio/basic',
			'file.aiff' => 'audio/x-aiff',
			'file.swf' => 'application/x-shockwave-flash',
			'file.m4a' => 'audio/mp4',
			'file.mpc' => 'audio/x-musepack',
			'file.wav' => 'audio/x-wav',
			'file.mov' => 'video/quicktime',
			'file.flac' => 'audio/flac',
			'file.class' => 'application/x-java',
			'file.rm' => 'application/vnd.rn-realmedia',
			'file.webm' => 'video/webm'
		];
		foreach ($files as $file => $mimeTypes) {
			$result = $this->subject->analyze($file);
			$common = array_values(array_intersect($result, (array) $mimeTypes));

			$this->assertEquals((array) $mimeTypes, $common, "File `{$file}`.");
		}
	}

	public function testAnalyzeReverse() {
		$files = [
			'application/x-bzip' => ['bz', 'bz2'],
			'text/css' => ['cssl', 'css'],
			'image/gif' => ['gif'],
			'application/x-gzip' => ['gz'],
			'application/x-java' => ['class'],
			'application/javascript' => ['js'],
			'application/pdf' => ['pdf'],
			'text/x-gettext-translation' => ['po'],
			'application/vnd.ms-powerpoint' => ['pot', 'pps', 'ppt', 'ppz'],
			'text/x-gettext-translation-template' => ['pot'],
			'application/x-gettext-translation' => ['gmo', 'mo'],
			'text/plain' => ['txt', 'asc'],
			'application/msword' => ['doc'],
			'application/vnd.oasis.opendocument.text' => ['odt'],
			'application/x-tar' => ['tar', 'gem', 'gtar'],
			'application/xhtml+xml' => ['xhtml'],
			'application/xml' => ['xsd', 'rng', 'xbl', 'xml'],
			'application/xslt+xml' => ['xsl', 'xslt'],
			'audio/x-wav' => ['wav'],
			'audio/mp4' => ['m4a', 'f4a', 'aac'],
			'video/ogg' => ['ogv'],
			'video/x-theora+ogg' => ['ogg'],
			'video/webm' => ['webm']
		];
		foreach ($files as $mimeType => $exts) {
			$result = $this->subject->analyze($mimeType, true);
			$this->assertEquals($exts, $result, "File `{$mimeType}`.");
		}
	}
}

?>