<?php

namespace Cundd\Rest\Authentication;


use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class BasicAuthenticationProvider extends AbstractAuthenticationProvider {
	/**
	 * Tries to authenticate the current request
	 * @return bool Returns if the authentication was successful
	 */
	public function authenticate() {
//		return FALSE;
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

//		unset($GLOBALS['TSFE']->fe_user->user);
//		unset($GLOBALS['TSFE']->fe_user);
//
//		$_GET['logintype']	= 'login';
//		$_GET['user'] 		= $username;
//		$_GET['pass'] 		= $password;
//		$_GET['submit'] 	= 'Anmelden';
//		$_GET['pid'] 		= '82';
//
//
//		$userAuth = new FrontendUserAuthentication();
//		$userAuth->start();
//		$GLOBALS['TSFE']->fe_user = $userAuth;

		echo '<pre>';
		var_dump($userAuth->user);
//		var_dump($userAuth->getKey('uc', 'name'));
//		var_dump($GLOBALS['TSFE']->fe_user);
		var_dump($GLOBALS['TSFE']->fe_user->user);
		var_dump($GLOBALS['TSFE']->fe_user->user['uid']);
		var_dump($username);
		var_dump($password);
//		var_dump($userAuth->getAuthInfoArray());
//		var_dump($_REQUEST);
//		var_dump($userAuth->loginSessionStarted);
//		var_dump($userAuth->loginFailure);
		var_dump(($GLOBALS['TSFE']->fe_user));
		echo '</pre>';


		if (($GLOBALS['TSFE']->fe_user->user) === FALSE) {
			header('WWW-Authenticate: Basic realm="My Realm"');
			header('HTTP/1.0 401 Unauthorized');
			return FALSE;
		}
		return TRUE;
	}
}