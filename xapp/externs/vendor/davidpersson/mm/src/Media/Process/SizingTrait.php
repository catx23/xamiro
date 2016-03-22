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
 * The `SizingTrait` class provides methods to resize and crop media. Media
 * can only ever be scaled down but never scaled up.
 */
trait SizingTrait {

	/**
	 * Alias for fitInside.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function fit($width, $height) {
		return $this->fitInside($width, $height);
	}

	/**
	 * Resizes media proportionally keeping both sides within given dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function fitInside($width, $height) {
		$rx = $this->_adapter->width() / $width;
		$ry = $this->_adapter->height() / $height;

		$r = $rx > $ry ? $rx : $ry;

		$width = $this->_adapter->width() / $r;
		$height = $this->_adapter->height() / $r;

		list($width, $height) = $this->_normalizeDimensions($width, $height, 'maximum');
		return $this->_adapter->resize($width, $height);
	}

	/**
	 * Resizes media proportionally keeping _smaller_ side within corresponding dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function fitOutside($width, $height) {
		$rx = $this->_adapter->width() / $width;
		$ry = $this->_adapter->height() / $height;

		$r = $rx < $ry ? $rx : $ry;

		$width = $this->_adapter->width() / $r;
		$height = $this->_adapter->height() / $r;

		list($width, $height) = $this->_normalizeDimensions($width, $height, 'ratio');
		return $this->_adapter->resize($width, $height);
	}

	/**
	 * Crops media to provided dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $gravity Currently supported values are `'center'`, `'topleft'`,
	 *        `'topright'`, `'bottomleft'`, `'bottomright'`, defaults to `'center'`.
	 * @return boolean
	 */
	public function crop($width, $height, $gravity = 'center') {
		list($width, $height) = $this->_normalizeDimensions($width, $height, 'maximum');
		list($left, $top) = $this->_boxify($width, $height, $gravity);

		return $this->_adapter->crop($left, $top, $width, $height);
	}

	/**
	 * Alias for zoomFit.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function zoom($width, $height) {
		return $this->zoomFit($width, $height);
	}

	/**
	 * Enlarges media proportionally by factor 2.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @return boolean
	 */
	public function zoomFit($width, $height) {
		$factor = 2;

		$width = $width * $factor;
		$height = $height * $factor;

		return $this->fitOutside($width, $height);
	}

	/**
	 * First crops an area (given by dimensions and enlarged by factor 2)
	 * out of the center of the media, then resizes that cropped
	 * area to given dimensions
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $gravity Currently supported values are `'center'`, `'topleft'`,
	 *        `'topright'`, `'bottomleft'`, `'bottomright'`, defaults to `'center'`.
	 * @return boolean
	 */
	public function zoomCrop($width, $height, $gravity = 'center') {
		$factor = 2;

		list($zoomWidth, $zoomHeight) = $this->_normalizeDimensions($width * $factor, $height * $factor, 'maximum');
		list($zoomLeft, $zoomTop) = $this->_boxify($zoomWidth, $zoomHeight, $gravity);
		list($width, $height) = [$zoomWidth / $factor, $zoomHeight / $factor];

		return $this->_adapter->cropAndResize(
			$zoomLeft, $zoomTop, $zoomWidth, $zoomHeight,
			$width, $height
		);
	}

	/**
	 * First resizes media so that it fills out the given dimensions,
	 * then cuts off overlapping parts.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $gravity Currently supported values are `'center'`, `'topleft'`,
	 *        `'topright'`, `'bottomleft'`, `'bottomright'`, defaults to `'center'`.
	 * @return boolean
	 */
	public function fitCrop($width, $height, $gravity = 'center') {
		$rx = $this->_adapter->width() / $width;
		$ry = $this->_adapter->height() / $height;

		$r = $rx < $ry ? $rx : $ry;

		$resizeWidth = $this->_adapter->width() / $r;
		$resizeHeight = $this->_adapter->height() / $r;

		$this->_adapter->resize($resizeWidth, $resizeHeight);
		list($left, $top) = $this->_boxify($width, $height, $gravity);

		return $this->_adapter->crop($left, $top, $width, $height);
	}

	/**
	 * Normalizes dimensions ensuring they don't exceed actual dimensions
	 * of the media. This forces all operations on the media to never scale
	 * up.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $recalculateBy Recalculate missing values or ones exceeding maximums
	 *               using either `'ratio'` or `'maximum'`.
	 * @return array An array containing width and height.
	 */
	protected function _normalizeDimensions($width, $height, $recalculateBy = 'ratio') {
		if ($width > $this->_adapter->width()) {
			$width = null;
		}
		if ($height > $this->_adapter->height()) {
			$height = null;
		}

		if (is_null($width) && is_null($height)) {
			$width = $this->_adapter->width();
			$height = $this->_adapter->height();
		}

		if ($recalculateBy == 'maximum') {
			if (empty($width)) {
				$width = $this->_adapter->width();
			}
			if (empty($height)) {
				$height = $this->_adapter->height();
			}
		} else {
			if (empty($width)) {
				$ratio = $height / $this->_adapter->height();
				$width = $ratio * $this->_adapter->width();
			}
			if (empty($height)) {
				$ratio = $width / $this->_adapter->width();
				$height = $ratio * $this->_adapter->height();
			}
		}
		return [$width, $height];
	}

	/**
	 * Calculates a box' coordinates.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string $gravity Currently supported values are "center", "topleft",
	 *                      "topright", "bottomleft", "bottomright", defaults to "center"
	 * @return array An array containing left and top coordinates
	 */
	protected function _boxify($width, $height, $gravity = 'center') {
		switch ($gravity) {
			case 'center':
				$left = max(0, ($this->_adapter->width() - $width) / 2);
				$top = max(0, ($this->_adapter->height() - $height) / 2);
				break;
			case 'topleft':
				$left = $top = 0;
				break;
			case 'topright':
				$left = max(0, $this->_adapter->width() - $width);
				$top = 0;
				break;
			case 'bottomleft':
				$left = 0;
				$top = max(0, $this->_adapter->height() - $height);
				break;
			case 'bottomright':
				$left = max(0, $this->_adapter->width() - $width);
				$top = max(0, $this->_adapter->height() - $height);
				break;
			default:
				throw new InvalidArgumentException("Unsupported gravity `{$gravity}`.");
		}
		return [$left, $top];
	}
}

?>