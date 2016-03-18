<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

/**
 * Represents role, an object that may request access to an IResource.
 *
 * @author     David Grudl
 */
interface XApp_Security_IRole
{

	/**
	 * Returns a string identifier of the Role.
	 * @return string
	 */
	function getRoleId();

}
