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

/**
 * `Video` allows for manipulating video files.
 * Most methods are simply inherited from the generic media type.
 */
class Video extends \mm\Media\Process\Generic {

	use \mm\Media\Process\SizingTrait;
}

?>