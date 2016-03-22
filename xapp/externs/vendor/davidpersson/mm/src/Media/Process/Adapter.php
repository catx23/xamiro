<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\Media\Process;

/**
 * This class must be subclass by all media process adapters.
 */
abstract class Adapter {

	/**
	 * Prepare the adapter and load the source.
	 *
	 * @param resource $handle An open handle to use a the source.
	 * @return void
	 */
	abstract public function __construct($handle);

	/**
	 * Writes the internal object to the provided handle.
	 *
	 * @see mm\Media\Process\Generic::store()
	 * @param resource $handle An open handle to use a the source.
	 * @return boolean|integer
	 */
	abstract public function store($handle);

	/**
	 * Converts the internal object to provided MIME-type.
	 *
	 * @param string $mimeType
	 * @return boolean
	 */
	abstract public function convert($mimeType);

	/**
	 * Allows for direct manipulation.
	 *
	 * @param string|integer $key
	 * @param mixed $value Value `null` should be passed if `$key` is a boolean switch.
	 * @return boolean `true` on success, `false` if something went wrong.
	 */
	abstract public function passthru($key, $value);
}

?>