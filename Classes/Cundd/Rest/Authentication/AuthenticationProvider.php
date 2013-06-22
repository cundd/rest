<?php

namespace Cundd\Rest\Authentication;


use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class AuthenticationProvider implements AuthenticationProviderInterface {
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
		$username = NULL;
		$password = NULL;

		// mod_php
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$username = $_SERVER['PHP_AUTH_USER'];
			$password = $_SERVER['PHP_AUTH_PW'];

		// most other servers
		} elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
			if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']),'basic')===0) {
				list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
			}
		}

//		$userAuth = new FrontendUserAuthentication();
//		$userAuth->start();
//		var_dump($userAuth->getKey('uc', 'name'));
		echo '<pre>';
		var_dump($username);
		var_dump($password);
		var_dump($_REQUEST);
		var_dump(($GLOBALS['TSFE']->fe_user));
		echo '</pre>';


		if (is_null($username)) {
			header('WWW-Authenticate: Basic realm="My Realm"');
			header('HTTP/1.0 401 Unauthorized');
			return FALSE;
		}
		return TRUE;
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