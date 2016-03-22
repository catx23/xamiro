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
 * This media process adapter interfaces with the `sox` binary through the shell.
 *
 * @link http://sox.sourceforge.net
 */
class SoxShell extends \mm\Media\Process\Adapter {

	protected $_sampleRate;

	protected $_channels;

	protected $_object;

	protected $_objectTemp;

	protected $_objectType;

	protected $_command;

	public function __construct($handle) {
		$this->_command = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'sox.exe' : 'sox';
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

		return true;
	}

	public function store($handle) {
		rewind($handle);
		rewind($this->_object);

		if (stream_copy_to_stream($this->_object, $handle)) {
			return true;
		}
		throw new Exception("Failed to store object into handle.");
	}

	public function convert($mimeType) {
		if (Type::guessName($mimeType) != 'audio') {
			return true; // others care about inter media type conversions
		}
		$sourceType = $this->_objectType;
		$targetType = $this->_type($mimeType);

		$modify = null;

		if ($this->_sampleRate) {
			$modify .= " --rate {$this->_sampleRate}";
		}
		if ($this->_channels) {
			$modify .= " --channels {$this->_channels}";
		}

		rewind($this->_object);
		$error = fopen('php://temp', 'w+b');
		$sourceTemp = $this->_objectTemp;
		$targetTemp = $this->_tempFile();

		// Since SoX 14.3.0 multi threading is enabled which
		// paradoxically can cause huge slowdowns.
		$command  = "{$this->_command} -q --single-threaded";
		$command .= " -t {$sourceType} {$sourceTemp}{$modify} -t {$targetType} {$targetTemp}";

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

		// Workaround for header based formats which require the output stream to be seekable.
		$target = fopen($targetTemp, 'r');
		$buffer = fopen('php://temp', 'w+');
		stream_copy_to_stream($target, $buffer);

		fclose($target);
		unlink($targetTemp);

		unlink($this->_objectTemp);

		$this->_load($buffer);
		return true;
	}

	public function passthru($key, $value) {
		throw new Exception("The adapter has no passthru support.");
	}

	public function channels($value) {
		$this->_channels = $value;
		return true;
	}

	public function sampleRate($value) {
		$this->_sampleRate = $value;
		return true;
	}

	protected function _type($object) {
		$type = Type::guessExtension($object);

		$map = [
			'ogv' => 'ogg',
			'oga' => 'ogg'
		];
		return isset($map[$type]) ? $map[$type] : $type;
	}

	protected function _tempFile() {
		return realpath(sys_get_temp_dir()) . '/' . uniqid('mm_');
	}
}

?>