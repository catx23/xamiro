<?php

interface xapp_ExtractableInterface
{
	/**
	 * Extract a compressed file to a given path
	 *
	 * @param   string  $archive      Path to archive to extract
	 * @param   string  $destination  Path to extract archive to
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   1.0
	 */
	public function extract($archive, $destination);

	/**
	 * Tests whether this adapter can unpack files on this computer.
	 *
	 * @return  boolean  True if supported
	 *
	 * @since   1.0
	 */
	public static function isSupported();
}
