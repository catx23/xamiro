<?php
/**
 * mm: the PHP media library
 *
 * Copyright (c) 2007-2014 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace mm\Media\Info;

/**
 * `Image` handles all image files.
 */
class Image extends \mm\Media\Info\Generic {

	use \mm\Media\Info\RatioTrait;

	/**
	 * Determines megapixels of media.
	 *
	 * @return integer
	 */
	public function megapixel() {
		return (integer) ($this->get('width') * $this->get('height') / 1000000);
	}

	/**
	 * Determines the quality of the media by
	 * taking amount of megapixels into account.
	 *
	 * @return integer A number indicating quality between 1 (worst) and 5 (best),
	 */
	public function quality() {
		$megapixel = $this->megapixel();

		/* Normalized between 1 and 5 where min = 0.5 and max = 10 */
		$megapixelMax = 10;
		$megapixelMin = 0.5;
		$qualityMax = 5;
		$qualityMin = 1;

		if ($megapixel > $megapixelMax) {
			$quality = $qualityMax;
		} elseif ($megapixel < $megapixelMin) {
			$quality = $qualityMin;
		} else {
			$quality =
				(($megapixel - $megapixelMin) / ($megapixelMax - $megapixelMin))
				* ($qualityMax - $qualityMin)
				+ $qualityMin;
		}
		return (integer) round($quality);
	}
}

?>