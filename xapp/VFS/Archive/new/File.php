<?php
/**
 * A File handling class
 *
 * @since  1.0
 */
class xapp_File2
{
	/**
	 * Strips the last extension off of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file name without the extension
	 *
	 * @since   1.0
	 */
	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes the file name safe to use
	 *
	 * @param   string  $file        The name of the file [not full path]
	 * @param   array   $stripChars  Array of regex (by default will remove any leading periods)
	 *
	 * @return  string  The sanitised string
	 *
	 * @since   1.0
	 */
	public static function makeSafe($file, array $stripChars = array('#^\.#'))
	{
		$regex = array_merge(array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#'), $stripChars);

		$file = preg_replace($regex, '', $file);

		// Remove any trailing dots, as those aren't ever valid file names.
		$file = rtrim($file, '.');

		return $file;
	}

	/**
	 * Copies a file
	 *
	 * @param   string   $src          The path to the source file
	 * @param   string   $dest         The path to the destination file
	 * @param   string   $path         An optional base path to prefix to the file names
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 * @throws  \UnexpectedValueException
	 */
	public static function copy($src, $dest, $path = null, $use_streams = false)
	{
		// Prepend a base path if it exists
		if ($path)
		{
			$src = Path::clean($path . '/' . $src);
			$dest = Path::clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			throw new Exception(__METHOD__ . ': Cannot find or read file: ' . $src);
		}

		if ($use_streams)
		{
			$stream = Stream::getStream();

			if (!$stream->copy($src, $dest))
			{
				throw new Exception(sprintf('%1$s(%2$s, %3$s): %4$s', __METHOD__, $src, $dest, $stream->getError()));
			}

			return true;
		}
		else
		{
			if (!@ copy($src, $dest))
			{
				throw new Exception(__METHOD__ . ': Copy failed.');
			}

			return true;
		}
	}

	/**
	 * Delete a file or array of files
	 *
	 * @param   mixed  $file  The file name or an array of file names
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 */
	public static function delete($file)
	{
		$files = (array) $file;

		foreach ($files as $file)
		{
			$file = Path::clean($file);

			// Try making the file writable first. If it's read-only, it can't be deleted
			// on Windows, even if the parent folder is writable
			@chmod($file, 0777);

			// In case of restricted permissions we zap it one way or the other
			// as long as the owner is either the webserver or the ftp
			if (!@ unlink($file))
			{
				$filename = basename($file);

				throw new FilesystemException(__METHOD__ . ': Failed deleting ' . $filename);
			}
		}

		return true;
	}

	/**
	 * Moves a file
	 *
	 * @param   string   $src          The path to the source file
	 * @param   string   $dest         The path to the destination file
	 * @param   string   $path         An optional base path to prefix to the file names
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 */
	public static function move($src, $dest, $path = '', $use_streams = false)
	{
		if ($path)
		{
			$src = Path::clean($path . '/' . $src);
			$dest = Path::clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			return 'Cannot find source file.';
		}

		if ($use_streams)
		{
			$stream = Stream::getStream();

			if (!$stream->move($src, $dest))
			{
				throw new FilesystemException(__METHOD__ . ': ' . $stream->getError());
			}

			return true;
		}
		else
		{
			if (!@ rename($src, $dest))
			{
				throw new FilesystemException(__METHOD__ . ': Rename failed.');
			}

			return true;
		}
	}

	/**
	 * Write contents to a file
	 *
	 * @param   string   $file         The full file path
	 * @param   string   &$buffer      The buffer to write
	 * @param   boolean  $use_streams  Use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 */
	public static function write($file, &$buffer, $use_streams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(dirname($file)))
		{
			xapp_Folder2::create(dirname($file));
		}

		if ($use_streams)
		{
			$stream = Stream::getStream();

			// Beef up the chunk size to a meg
			$stream->set('chunksize', (1024 * 1024));

			if (!$stream->writeFile($file, $buffer))
			{
				throw new FilesystemException(sprintf('%1$s(%2$s): %3$s', __METHOD__, $file, $stream->getError()));
			}

			return true;
		}
		else
		{
			$file = xapp_Path2::clean($file);
			$ret = is_int(file_put_contents($file, $buffer)) ? true : false;

			return $ret;
		}
	}

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param   string   $src          The name of the php (temporary) uploaded file
	 * @param   string   $dest         The path (including filename) to move the uploaded file to
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 * @throws  FilesystemException
	 */
	public static function upload($src, $dest, $use_streams = false)
	{
		// Ensure that the path is valid and clean
		$dest = Path::clean($dest);

		// Create the destination directory if it does not exist
		$baseDir = dirname($dest);

		if (!file_exists($baseDir))
		{
			Folder::create($baseDir);
		}

		if ($use_streams)
		{
			$stream = Stream::getStream();

			if (!$stream->upload($src, $dest))
			{
				throw new FilesystemException(__METHOD__ . ': ' . $stream->getError());
			}

			return true;
		}
		else
		{
			if (is_writeable($baseDir) && move_uploaded_file($src, $dest))
			{
				// Short circuit to prevent file permission errors
				if (Path::setPermissions($dest))
				{
					return true;
				}
				else
				{
					throw new FilesystemException(__METHOD__ . ': Failed to change file permissions.');
				}
			}
			else
			{
				throw new FilesystemException(__METHOD__ . ': Failed to move file.');
			}

			return false;
		}
	}
}
