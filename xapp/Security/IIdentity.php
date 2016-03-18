<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

/**
 * Represents the user of application.
 *
 * @author     David Grudl
 */
interface XApp_Security_IIdentity
{

	/**
	 * Returns the ID of user.
	 * @return mixed
	 */
	function getId();

	/**
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	function getRoles();

}
