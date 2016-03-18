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

trait RatioTrait {

	/**
	 * Determines the ratio.
	 *
	 * @return float
	 */
	public function ratio() {
		return $this->get('width') / $this->get('height');
	}

	/**
	 * Figures out which known ratio is closest to provided one.
	 * Can be reused by specific media type classes.
	 *
	 * @return string
	 */
	public function knownRatio() {
		$width = $this->get('width');
		$height = $this->get('height');

		if (empty($width) || empty($height)) {
			return null;
		}

		$knownRatios = [
			'1:1.294' => 1/1.294,
			'1:1.545' => 1/1.1545,
			'4:3'     => 4/3,
			'1.375:1' => 1.375,
			'3:2'     => 3/2,
			'16:9'    => 16/9,
			'1.85:1'  => 1.85,
			'1.96:1'  => 1.96,
			'2.35:1'  => 2.35,
			'√2:1'    => pow(2, 1/2), /* dina4 quer */
			'1:√2'    => 1 / (pow(2, 1/2)), /* dina4 hoch */
			'Φ:1'     => (1 + pow(5, 1/2)) / 2, /* goldener schnitt */
			'1:Φ'     => 1 / ((1 + pow(5, 1/2)) / 2), /* goldener schnitt */
		];

		foreach ($knownRatios as $knownRatioName => &$knownRatio) {
			$knownRatio = abs(($width / $height) - $knownRatio);
		}

		asort($knownRatios);
		$names = array_keys($knownRatios);
		return array_shift($names);
	}
}

?>