<?php
/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

xapp_import('xapp.Security.IAuthenticator');
/**
 * Trivial implementation of IAuthenticator.
 *
 * @author     David Grudl
 */
class XApp_Security_SimpleAuthenticator implements XApp_Security_IAuthenticator
{
	/** @var array */
	private $userlist;


	/**
	 * @param  array  list of pairs username => password
	 */
	public function __construct(array $userlist)
	{
		$this->userlist = $userlist;
	}


	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws AuthenticationException
	 * @return XApp_Security_IIdentity
	 * @throws XApp_Security_AuthenticationException
	 */
	public function authenticate(array $credentials)
	{

		list($username, $password) = $credentials;
		foreach ($this->userlist as $name => $pass) {
			if (strcasecmp($name, $username) === 0) {
				if ((string) $pass === (string) $password) {
					return new XApp_Security_Identity($name);
				} else {
					throw new XApp_Security_AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
				}
			}
		}
		throw new XApp_Security_AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
	}

}
