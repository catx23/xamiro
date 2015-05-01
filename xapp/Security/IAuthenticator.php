<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */
/**
 * Performs authentication.
 *
 * @author     David Grudl
 */
interface XApp_Security_IAuthenticator
{
	/** Credential key */
	const USERNAME = 0,
		PASSWORD = 1;

	/** Exception error code */
	const IDENTITY_NOT_FOUND = 1,
		INVALID_CREDENTIAL = 2,
		FAILURE = 3,
		NOT_APPROVED = 4;

	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws AuthenticationException
	 * @return XApp_Security_IIdentity
	 * @throws XApp_Security_AuthenticationException
	 */
	function authenticate(array $credentials);

}
