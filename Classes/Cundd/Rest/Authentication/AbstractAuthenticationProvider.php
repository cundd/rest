<?php

namespace Cundd\Rest\Authentication;

abstract class AbstractAuthenticationProvider implements AuthenticationProviderInterface {
	/**
	 * Indicates if the current request is authenticated
	 * @var bool
	 */
	protected $isAuthenticated = FALSE;

	/**
	 * Tries to authenticate the current request
	 * @return bool Returns if the authentication was successful
	 */
	public function authenticate() {
		return FALSE;
	}

	/**
	 * Returns if the current request is authenticated
	 * @return bool
	 */
	public function isAuthenticated() {
		return $this->isAuthenticated;
	}

	/**
	 * Returns if the given request needs authentication
	 * @param \Bullet\Request $request
	 * @return bool
	 */
	public function requestNeedsAuthentication(\Bullet\Request $request) {
		return FALSE;
	}
}