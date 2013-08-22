<?php

namespace Cundd\Rest\Authentication;

/**
 * The class expects an existing valid authenticated Frontend User or credentials passed through the request.
 *
 * Example URL: logintype=login&pid=PIDwhereTheFEUsersAreStored&user=MyUserName&pass=MyPassword
 *
 * @package Cundd\Rest\Authentication
 */
class RequestAuthenticationProvider extends AbstractAuthenticationProvider {
	/**
	 * Tries to authenticate the current request
	 * @return bool Returns if the authentication was successful
	 */
	public function authenticate() {
		return (TRUE == ($GLOBALS['TSFE']->fe_user->user));
	}

	/**
	 * Returns if the given request needs authentication
	 * @return bool
	 */
	public function requestNeedsAuthentication() {
		return TRUE;
	}
}