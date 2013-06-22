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
	 * @param \Bullet\Request $request
	 * @return bool
	 */
	public function requestNeedsAuthentication(\Bullet\Request $request);
}