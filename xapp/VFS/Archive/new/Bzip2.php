<?php
xapp_import('xapp.VFS.Archive.new.ExtractableInterface');
class xapp_Bzip2 implements xapp_ExtractableInterface
{
	/**
	 * Bzip2 file data buffer
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $data = null;

	/**
	 * Holds the options array.
	 *
	 * @var    mixed  Array or object that implements \ArrayAccess
	 * @since  1.0
	 */
	protected $options = array();

	/**
	 * Create a new Archive object.
	 *
	 * @param   mixed  $options  An array of options or an object that implements \ArrayAccess
	 *
	 * @since   1.0
	 */
	public function __construct($options = array())
	{
		$this->options = $options;
	}

	/**
	 * Extract a Bzip2 compressed file to a given path
	 *
	 * @param   string  $archive      Path to Bzip2 archive to extract
	 * @param   string  $destination  Path to extract archive to
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   1.0
	 * @throws  Exception
	 */
	public function extract($archive, $destination)
	{
		$this->data = null;

		$this->options['use_streams'] = false;
		if (!isset($this->options['use_streams']) || $this->options['use_streams'] == false)
		{
			// Old style: read the whole file and then parse it
			$this->data = file_get_contents($archive);

			if (!$this->data)
			{
				throw new Exception('Unable to read archive');
			}

			$buffer = bzdecompress($this->data);
			unset($this->data);

			if (empty($buffer))
			{
				throw new Exception('Unable to decompress data');
			}

			if (xapp_File2::write($destination, $buffer) === false)
			{
				throw new Exception('Unable to write archive');
			}
		}
		// @codeCoverageIgnoreStart
		else
		{
			// New style! streams!
			$input = Stream::getStream();

			// Use bzip
			$input->set('processingmethod', 'bz');

			if (!$input->open($archive))
			{
				throw new Exception('Unable to read archive (bz2)');
			}

			$output = Stream::getStream();

			if (!$output->open($destination, 'w'))
			{
				$input->close();

				throw new Exception('Unable to write archive (bz2)');
			}

			do
			{
				$this->data = $input->read($input->get('chunksize', 8196));

				if ($this->data)
				{
					if (!$output->write($this->data))
					{
						$input->close();

						throw new Exception('Unable to write archive (bz2)');
					}
				}
			}

			while ($this->data);

			$output->close();
			$input->close();
		}
		// @codeCoverageIgnoreEnd
		return true;
	}

	/**
	 * Tests whether this adapter can unpack files on this computer.
	 *
	 * @return  boolean  True if supported
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return extension_loaded('bz2');
	}
}
