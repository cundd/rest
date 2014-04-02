<?php
/*
*  Copyright notice
*
*  (c) 2014 Daniel Corn <info@cundd.net>, cundd
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*/

namespace Cundd\Rest\Authentication;


use Cundd\Rest\Handler\AuthHandler;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class BasicAuthenticationProvider extends AbstractAuthenticationProvider {
	/**
	 * Provider that will check the user credentials
	 *
	 * @var \Cundd\Rest\Authentication\UserProviderInterface
	 * @inject
	 */
	protected $userProvider;

	/**
	 * @var \Cundd\Rest\SessionManager
	 * @inject
	 */
	protected $sessionManager;

	/**
	 * Tries to authenticate the current request
	 * @return bool Returns if the authentication was successful
	 */
	public function authenticate() {
		$username = NULL;
		$password = NULL;

		/**
		 * @TODO Move this to a separate Authentication Provider
		 */
		$loginStatus = $this->sessionManager->valueForKey('loginStatus');
		if ($loginStatus !== NULL) {
			return $loginStatus === AuthHandler::STATUS_LOGGED_IN;
		}

		// mod_php
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$username = $_SERVER['PHP_AUTH_USER'];
			$password = $_SERVER['PHP_AUTH_PW'];

		// most other servers
		} elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
			if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']), 'basic') === 0) {
				list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHENTICATION'], 6)));
			}
		}
		return $this->userProvider->checkCredentials($username, $password);
	}
}
