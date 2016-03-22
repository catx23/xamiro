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
 * `Document` handles document files like PDFs. Most methods are simply
 * inherited from the generic media type wile some overlap with those defined
 * in `\mm\Media\Info\Image`.
 *
 * @see mm\Media\Info\Image
 */
class Document extends \mm\Media\Info\Generic {

	use \mm\Media\Info\RatioTrait;
}

?>