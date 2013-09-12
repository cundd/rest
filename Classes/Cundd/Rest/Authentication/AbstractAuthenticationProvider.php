<?php

namespace Cundd\Rest\Authentication;

abstract class AbstractAuthenticationProvider implements AuthenticationProviderInterface {
	/**
	 * Indicates if the current request is authenticated
	 * @var bool
	 */
	protected $isAuthenticated = FALSE;

	/**
	 * The current request
	 * @var \Cundd\Rest\Request
	 */
	protected $request;

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
	 * @param \Bullet\Request|\Cundd\Rest\Request $request
	 * @return mixed|void
	 */
	public function setRequest(\Cundd\Rest\Request $request) {
		$this->request = $request;
	}

	/**
	 * @return \Bullet\Request
	 */
	public function getRequest() {
		return $this->request;
	}
}