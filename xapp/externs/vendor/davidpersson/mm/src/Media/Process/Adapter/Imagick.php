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
use OutOfBoundsException;
use Imagick as ImagickCore;

/**
 * This media process adapter allows for interfacing with ImageMagick through
 * the `imagick` pecl extension (which must be loaded in order to use this adapter).
 *
 * @link http://php.net/imagick
 * @link http://www.imagemagick.org
 */
class Imagick extends \mm\Media\Process\Adapter {

	protected $_object;

	protected $_formatMap = [
		'application/pdf' => 'pdf',
		'image/jpeg' => 'jpeg',
		'image/gif' => 'gif',
		'image/png' => 'png',
		'image/tiff' => 'tiff',
		'image/wbmp' => 'wbmp',
		'image/ms-bmp' => 'bmp',
		'image/pcx' => 'pcx',
		'image/ico' => 'ico',
		'image/xbm' => 'xbm',
		'image/psd' => 'psd'
	];

	public function __construct($handle) {
		rewind($handle);
		$this->_object = new ImagickCore();
		$this->_object->readImageFile($handle);

		// For sequences reset iterator to get to first one first.
		if ($this->_object->getNumberImages() > 1) {
			$this->_object->setFirstIterator();
		}
		$mimeType = Type::guessType($handle);

		if (!isset($this->_formatMap[$mimeType])) {
			throw new OutOfBoundsException("MIME type `{$mimeType}` cannot be mapped to a format.");
		}
		// We need to explictly `setFormat()` here, otherwise `getFormat()` returns `null`.
		$this->_object->setFormat($this->_formatMap[$mimeType]);
	}

	public function __destruct() {
		if ($this->_object) {
			$this->_object->clear();
		}
	}

	public function store($handle) {
		if ($this->_isAnimated()) {
			return $this->_object->writeImagesFile($handle);
		}
		return $this->_object->writeImageFile($handle);
	}

	public function convert($mimeType) {
		if (Type::guessName($mimeType) != 'image') {
			return true;
		}
		if (!isset($this->_formatMap[$mimeType])) {
			throw new OutOfBoundsException("MIME type `{$mimeType}` cannot be mapped to a format.");
		}
		return $this->_object->setFormat($this->_formatMap[$mimeType]);
	}

	public function passthru($key, $value) {
		$method = $key;
		$args = (array) $value;

		if (!method_exists($this->_object, $method)) {
			$message = "Cannot passthru to nonexistent method `{$method}` on internal object";
			throw new Exception($message);
		}
		return (boolean) call_user_func_array([$this->_object, $method], $args);
	}

	public function trim($fuzz) {
		return $this->_object->trimImage($fuzz)
			&& $this->_object->setImagePage(0, 0, 0, 0);
	}

	// @link http://studio.imagemagick.org/pipermail/magick-users/2002-August/004435.html
	public function compress($value) {
		switch ($this->_object->getFormat()) {
			case 'tiff':
				return $this->_object->setImageCompression(ImagickCore::COMPRESSION_LZW);
			case 'png':
				$filter = ($value * 10) % 10;
				$level = (integer) $value;

				return $this->_object->setImageCompression(ImagickCore::COMPRESSION_ZIP)
					&& $this->_object->setImageCompressionQuality($level * 10 + $filter);
			case 'jpeg':
				return $this->_object->setImageCompression(ImagickCore::COMPRESSION_JPEG)
					&& $this->_object->setImageCompressionQuality((integer) (100 - ($value * 10)));
			default:
				throw new Exception("Cannot compress this format.");
		}
	}

	public function profile($type, $data = null) {
		if (!$data) {
			$profiles = $this->_object->getImageProfiles('*', false);

			if (!in_array($type, $profiles)) {
				return false;
			}
			return $this->_object->getImageProfile($type);
		}

		try {
			return $this->_object->profileImage($type, $data);
		} catch (Exception $e) {
			$corruptProfileMessage = 'color profile operates on another colorspace `icc';
			// $corruptProfileCode = 465;

			if (strpos($e->getMessage(), $corruptProfileMessage) !== false) {
				return $this->strip($type) && $this->profile($type, $data);
			}
			throw $e;
		}
	}

	public function strip($type) {
		return $this->_object->profileImage($type, null);
	}

	public function depth($value) {
		return $this->_object->setImageDepth($value);
	}

	public function interlace($value) {
		if (!$value) {
			return $this->_object->setInterlaceScheme(ImagickCore::INTERLACE_NO);
		}
		$constant = '\Imagick::INTERLACE_' . strtoupper($this->_object->getFormat());

		if (!defined($constant)) {
			throw new Exception("Cannot use interlace scheme; constant `{$constant}` not defined.");
		}
		return $this->_object->setInterlaceScheme(constant($constant));
	}

	public function background($rgb) {
		$color = "rgb({$rgb[0]},{$rgb[1]},{$rgb[2]})";

		$colorized = new ImagickCore();
		$colorized->newImage($this->width(), $this->height(), $color);
		$colorized->compositeImage($this->_object, ImagickCore::COMPOSITE_OVER, 0, 0);

		$this->_object = $colorized;
		return true;
	}

	public function crop($left, $top, $width, $height) {
		$left   = (integer) $left;
		$top    = (integer) $top;
		$width  = (integer) $width;
		$height = (integer) $height;

		return $this->_object->cropImage($width, $height, $left, $top);
	}

	public function resize($width, $height) {
		$width  = (integer) $width;
		$height = (integer) $height;

		return $this->_object->resizeImage($width, $height, ImagickCore::FILTER_LANCZOS, 1);
	}

	public function cropAndResize($cropLeft, $cropTop, $cropWidth, $cropHeight, $resizeWidth, $resizeHeight) {
		return $this->crop($cropLeft, $cropTop, $cropWidth, $cropHeight)
			&& $this->resize($resizeWidth, $resizeHeight);
	}

	public function width() {
		return $this->_object->getImageWidth();
	}

	public function height() {
		return $this->_object->getImageHeight();
	}

	public function quantumRange() {
		$result = $this->_object->getQuantumRange();
		return $result['quantumRangeLong'];
	}

	/**
	 * Helper method to detect animated images. These most often are GIFs. PDFs
	 * will never be detected as animated.
	 *
	 * @return boolean
	 */
	protected function _isAnimated() {
		return $this->_object->getNumberImages() > 1 && $this->_object->getFormat() !== 'pdf';
	}
}

?>