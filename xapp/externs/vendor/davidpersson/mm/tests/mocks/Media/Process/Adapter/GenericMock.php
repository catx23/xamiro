<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\tests\mocks\Media\Process\Adapter;

class GenericMock extends \mm\Media\Process\Adapter {

	public $storeCopyFromStream;

	public function __construct($handle) {}

	public function store($handle) {
		if ($this->storeCopyFromStream) {
			stream_copy_to_stream($this->storeCopyFromStream, $handle);
		}
		return true;
	}

	public function convert($mimeType) {
		return true;
	}

	public function passthru($key, $value) {
		return true;
	}
}

?>