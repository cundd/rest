<?php
namespace Cundd\Rest\Authentication;


interface AuthenticationProviderInterface {
	/**
	 * Tries to authenticate the current request
	 * @return bool Returns if the authentication was successful
	 */
	public function authenticate();

	/**
	 * Returns if the current request is authenticated
	 * @return bool
	 */
	public function isAuthenticated();

	/**
	 * Returns if the given request needs authentication
	 * @return bool
	 */
	public function requestNeedsAuthentication();

	/**
	 * Sets the request to get the authentication requirements for
	 * @param \Bullet\Request|\Cundd\Rest\Request $request
	 * @return mixed
	 */
	public function setRequest(\Cundd\Rest\Request $request);
}