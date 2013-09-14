<?php

namespace Cundd\Rest\Authentication;


use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class BasicAuthenticationProvider extends AbstractAuthenticationProvider {
	/**
	 * Provider that will check the user credentials
	 * @var \Cundd\Rest\Authentication\UserProviderInterface
	 * @inject
	 */
	protected $userProvider;

	/**
	 * Tries to authenticate the current request
	 * @return bool Returns if the authentication was successful
	 */
	public function authenticate() {
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
		return $this->userProvider->checkCredentials($username, $password);
	}
}