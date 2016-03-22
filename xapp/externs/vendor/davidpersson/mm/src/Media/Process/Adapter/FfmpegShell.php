<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\Media\Process\Adapter;

use mm\Mime\Type;
use Exception;
use RuntimeException;

/**
 * This media process adapter interfaces with the `ffmpeg` binary through the shell.
 *
 * @link http://ffmpeg.org
 */
class FfmpegShell extends \mm\Media\Process\Adapter {

	protected $_object;

	protected $_objectTemp;

	protected $_objectInfo;

	protected $_objectType;

	protected $_width;

	protected $_height;

	protected $_command;

	protected $_options = [
		'overwrite' => '-y',
		'vsync' => '-vsync 2'
	];

	protected $_targetType;

	public function __construct($handle) {
		$this->_command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'ffmpeg.exe' : 'ffmpeg';
		$this->_load($handle);
	}

	public function __destruct() {
		if ($this->_objectTemp) {
			unlink($this->_objectTemp);
		}
	}

	protected function _load($handle) {
		rewind($handle);

		$this->_object = $handle;
		$this->_objectTemp = $this->_tempFile();

		file_put_contents($this->_objectTemp, $handle);

		$this->_objectType = $this->_type(Type::guessType($handle));
		$this->_targetType = $this->_objectType;

		$this->_info = $this->_info();

		return true;
	}

	public function passthru($key, $value) {
		if ($value === null) {
			$this->_options[$key] = "-{$key}";
		} elseif (is_array($value)) {
			$this->_options[$key] = "-{$key} " . implode(" -{$key} ", (array) $value);
		} else {
			$this->_options[$key] = "-{$key} {$value}";
		}
		return true;
	}

	public function store($handle) {
		$original = get_class_vars(__CLASS__);

		if ($this->_targetType != $this->_objectType || $original['_options'] != $this->_options) {
			$this->_process();
		}
		rewind($handle);
		rewind($this->_object);

		if (stream_copy_to_stream($this->_object, $handle)) {
			return true;
		}
		throw new Exception("Failed to store object into handle.");
	}

	public function convert($mimeType) {
		switch (Type::guessName($mimeType)) {
			case 'image':
				$this->_options = [
					'vcodec' => '-vcodec ' . $this->_type($mimeType),
					'vframes' => '-vframes 1',
					'seek' => '-ss ' . intval($this->duration() / 4),
					'noAudio' => '-an',
				] + $this->_options;

				if ($mimeType == 'image/jpeg') {
					// Get highest quality jpeg as possible; will
					// be compressed later.
					$this->_options['qscale:v'] = '-qscale:v 1';
				}
				$this->_targetType = 'rawvideo';
				break;
			case 'video':
				$this->_targetType = $this->_type($mimeType);
				break;
		}
		return true;
	}

	public function crop($left, $top, $width, $height) {
		throw new Exception("The adapter doesn't support the `crop` action.");
	}

	public function resize($width, $height) {
		return (boolean) $this->_options['resize'] = [
			(integer) $width,
			(integer) $height
		];
	}

	public function width() {
		if ($this->_width) {
			return $this->_width;
		}
		preg_match('/Video\:.*,\s([0-9]+)x/', $this->_info, $matches);

		if (!isset($matches[1])) {
			throw new Exception('Could not parse width.');
		}
		return $matches[1];
	}

	public function height() {
		if ($this->_height) {
			return $this->_height;
		}
		preg_match('/Video\:.*,\s[0-9]+x([0-9]+)/', $this->_info, $matches);

		if (!isset($matches[1])) {
			throw new Exception('Could not parse height.');
		}
		return $matches[1];
	}

	public function duration() {
		preg_match('/Duration\:\s([0-9]{2})\:([0-9]{2})\:([0-9]{2})/', $this->_info, $matches);

		if (!isset($matches[1], $matches[2], $matches[3])) {
			throw new Exception('Could not parse duration.');
		}

		$duration  = $matches[1] * 60 * 60; /* hours */
		$duration += $matches[2] * 60;      /* minutes */
		$duration += $matches[3];           /* seconds */
		/* We do not care about ms. */

		return $duration;
	}

	protected function _info() {
		$command  = "{$this->_command} -f {$this->_objectType} -i {$this->_objectTemp}";

		$descr = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w']
		];

		/* There is no other way to get video information from
		   ffmpeg without exiting with an error condition because
		   it'll complain about a missing ouput file. */

		$process = proc_open($command, $descr, $pipes);

		/* Result is output to stderr. */
		$result = stream_get_contents($pipes[2]);

		fclose($pipes[0]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($process);

		/* Intentionally not checking for return value. */
		return $result;
	}

	protected function _process() {
		$targetTemp = $this->_tempFile();

		$object = "-f {$this->_objectType} -i {$this->_objectTemp}";
		$target = "-f {$this->_targetType} {$targetTemp}";

		if (isset($this->_options['resize'])) {
			list($width, $height) = $this->_options['resize'];

			/* Fix for codecs require sizes to be even. */
			$requireEven = ['mp4'];

			if (in_array($this->_targetType, $requireEven)) {
				$width = $width % 2 ? $width + 1 : $width;
				$height = $height % 2 ? $height + 1 : $height;
			}
			$this->_options['resize'] = "-s {$width}x{$height}";
		}
		$options = $this->_options ? implode(' ', $this->_options) . ' ' : null;
		$command  = "{$this->_command} {$object} {$options}{$target}";

		$descr = [
			0 => ['pipe', 'r'],
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w']
		];
		$process = proc_open($command, $descr, $pipes);

		$output = stream_get_contents($pipes[1]);
		$error  = stream_get_contents($pipes[2]);
		fclose($pipes[0]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$return = proc_close($process);

		if ($return != 0) {
			$message  = "Command `{$command}` returned `{$return}`:";
			$message .= "\nOutput was:\n" . ($output ?: 'n/a');
			$message .= "\nError output was:\n" . ($error ?: 'n/a');
			throw new RuntimeException($message);
		}

		$target = fopen($targetTemp, 'r');
		$buffer = fopen('php://temp', 'w+');
		stream_copy_to_stream($target, $buffer);

		fclose($target);
		unlink($targetTemp);

		$this->_options = [];
		unlink($this->_objectTemp);

		$this->_load($buffer);
		return true;
	}

	protected function _type($object) {
		$type = Type::guessExtension($object);

		$map = [
			'ogv' => 'ogg',
			'oga' => 'ogg',
			'jpg' => 'mjpeg' // There is no jpeg video encoder.
		];
		return isset($map[$type]) ? $map[$type] : $type;
	}

	protected function _tempFile() {
		return realpath(sys_get_temp_dir()) . '/' . uniqid('mm_');
	}
}

?>