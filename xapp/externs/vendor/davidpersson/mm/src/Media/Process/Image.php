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

use InvalidArgumentException;

/**
 * The `Image` class provides methods for manipulating images through
 * resizing, cropping and other methods. It abstracts _most common_ image
 * manipulations.
 */
class Image extends \mm\Media\Process\Generic {

	use \mm\Media\Process\SizingTrait;

	public function trim($percent = 30) {
		$fuzz = $this->_adapter->quantumRange() * $percent / 100;
		return $this->_adapter->trim($fuzz);
	}

	/**
	 * Selects level of compression (and in for some format the filters) than
	 * compresses the media according to provided value.
	 *
	 * For png images the decimal place denotes the type of filter to be used this means
	 * .0 is none, .1 is "sub", .2 is "up", .3 is "average", .4 is "paeth" and .5 is
	 * "adaptive". The number itself controls the zlib compression level from 1 (fastest)
	 * to 9 (best compression). Compression for png images is lossless.
	 *
	 * For jpeg images the provided value is multiplied with 10 and substracted from 100
	 * (the best jpeg quality). This means i.e. a given value of 1.5 results in a jpeg
	 * quality of 85. Compression for jpeg images is lossy.
	 *
	 * The tiff format with LZW compression (which is used by default) does not allow for
	 * controlling the compression level. Therefore the given value is simply ignored.
	 * Compression for tiff images is lossless.
	 *
	 * @param float $value Zero for no compression at all or a value
	 *        between 0 and 9.9999999(highest compression); defaults to 1.5.
	 * @return boolean
	 */
	public function compress($value = 1.5) {
		if ($value < 0 || $value >= 10) {
			throw new InvalidArgumentException('Compression value is not within range 0..10.');
		}
		return $this->_adapter->compress(floatval($value));
	}

	/**
	 * Strips unwanted data from an image. This operation is therefore always lossful.
	 * Be careful when removing color profiles (icc) and copyright information (iptc/xmp).
	 *
	 * @param string $type One of either `'8bim'`, `'icc'`, `'iptc'`, `'xmp'`, `'app1'`, `'app12'`,
	 *        `'exif'`. Repeat argument to strip multiple types.
	 * @return boolean
	 */
	public function strip($type) {
		foreach (func_get_args() as $type) {
			$this->_adapter->strip($type);
		}
		return true;
	}

	/**
	 * Embeds the provided ICC profile into the image. Allows for forcing a certain profile and
	 * transitioning from one color space to another.
	 *
	 * In case the image already has a color profile  embedded (which is highly recommended) it
	 * is used to convert to the target. In absence of an  embedded profile it is assumed that
	 * the image has the `sRGB IEC61966-2.1` (with blackpoint scaling) profile.
	 *
	 * Please note that most adapters will try to recover from a embedded corrupt profile
	 * by deleting it. Color profiles specified in the EXIF data of the image are not honored.
	 * This method works with ICC profiles only.
	 *
	 * @param string $file Absolute path to a profile file (most often with a `'icc'` extension).
	 * @return boolean
	 * @link http://www.cambridgeincolour.com/tutorials/color-space-conversion.htm
	 */
	public function colorProfile($file) {
		if (!is_file($file)) {
			throw new InvalidArgumentException("Given file `{$file}` does not exist.");
		}

		$target  = file_get_contents($file);
		$current = $this->_adapter->profile('icc');

		if (!$current) {
			$file = dirname(dirname(dirname(dirname(__FILE__)))) . '/data/sRGB_IEC61966-2-1_black_scaled.icc';
			$current = file_get_contents($file);

			$this->_adapter->profile('icc', $current);
		}
		if ($current == $target) {
			return true;
		}
		return $this->_adapter->profile('icc', $target);
	}

	/**
	 * Changes the color depths (of the channels).
	 *
	 * @param integer $value The number of bits in a color sample within a pixel. Usually `8`.
	 *        This is _not_ the total number of bits per pixel but the bits per channel.
	 * @return boolean
	 */
	public function colorDepth($value) {
		return $this->_adapter->depth($value);
	}

	/**
	 * Enables or disables interlacing. Formats like PNG, GIF and JPEG support interlacing.
	 *
	 * @param boolean $value `true` to enable interlacing (progressive rendering),
	 *        or `false` to disable it.
	 * @return boolean
	 */
	public function interlace($value) {
		return $this->_adapter->interlace($value);
	}

	/**
	 * Allows setting a background color as a replacement for the alpha channel.
	 *
	 * @todo Support hex, named and HSL color strings and convert them to RGB.
	 * @param string $color A color string i.e. `'rgb(230,10,22)'`.
	 * @return boolean
	 */
	public function background($color) {
		if (!preg_match('/rgb\(([0-9]+),([0-9]+),([0-9]+)\)/i', $color, $matches)) {
			throw new Exception("Unsupported color string `{$color}`.");
		}
		return $this->_adapter->background([$matches[1], $matches[2], $matches[3]]);
	}
}

?>