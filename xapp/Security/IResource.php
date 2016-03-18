<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

/**
 * Represents resource, an object to which access is controlled.
 *
 * @author     David Grudl
 */
interface XApp_Security_IResource
{
	/**
	 * Returns a string identifier of the Resource.
	 * @return string
	 */
	function getResourceId();
}
