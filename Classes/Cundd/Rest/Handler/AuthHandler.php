<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 01.04.14
 * Time: 21:55
 */

namespace Cundd\Rest\Handler;


use Bullet\App;
use Cundd\Rest\Dispatcher;
use Cundd\Rest\Handler;
use Cundd\Rest\HandlerInterface;
use Cundd\Rest\Request;

class AuthHandler implements HandlerInterface {
	const STATUS_LOGGED_IN = 'logged-in';
	const STATUS_LOGGED_OUT = 'logged-out';
	const STATUS_FAILURE = 'login failure';

	/**
	 * Current request
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * @var \Cundd\Rest\SessionManager
	 * @inject
	 */
	protected $sessionManager;

	/**
	 * Provider that will check the user credentials
	 *
	 * @var \Cundd\Rest\Authentication\UserProviderInterface
	 * @inject
	 */
	protected $userProvider;

	/**
	 * Sets the current request
	 *
	 * @param \Cundd\Rest\Request $request
	 * @return $this
	 */
	public function setRequest($request) {
		$this->request    = $request;
		return $this;
	}

	/**
	 * Returns the current request
	 *
	 * @return \Cundd\Rest\Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Returns the current status
	 *
	 * @return array
	 */
	public function getStatus() {
		$loginStatus = $this->sessionManager->valueForKey('loginStatus');
		if ($loginStatus === NULL) {
			$loginStatus = self::STATUS_LOGGED_OUT;
		}
		return array(
			'status' => $loginStatus
		);
	}

	/**
	 * Check the given login data
	 *
	 * @param array $sentData
	 * @return array
	 */
	public function checkLogin($sentData) {
		$loginStatus = self::STATUS_LOGGED_OUT;
		if (isset($sentData['username']) && isset($sentData['apikey'])) {
			$username = $sentData['username'];
			$apikey = $sentData['apikey'];

			if ($this->userProvider->checkCredentials($username, $apikey)) {
				$loginStatus = self::STATUS_LOGGED_IN;
			} else {
				$loginStatus = self::STATUS_FAILURE;
			}
			$this->sessionManager->setValueForKey('loginStatus', $loginStatus);
		}
		return array(
			'status' => $loginStatus
		);
	}

	/**
	 * Log out
	 *
	 * @return array
	 */
	public function logout() {
		$this->sessionManager->setValueForKey('loginStatus', self::STATUS_LOGGED_OUT);
		return array(
			'status' => self::STATUS_LOGGED_OUT
		);
	}

	/**
	 * Configure the API paths
	 */
	public function configureApiPaths() {
		$dispatcher = Dispatcher::getSharedDispatcher();

		/** @var App $app */
		$app = $dispatcher->getApp();

		/** @var AuthHandler */
		$handler = $this;

		$app->path($dispatcher->getPath(), function ($request) use ($handler, $app) {
			$handler->setRequest($request);


			$app->path('login', function($request) use ($handler, $app) {
				$getCallback = function ($request) use ($handler) {
					return $handler->getStatus();
				};
				$app->get($getCallback);

				$loginCallback = function ($request) use ($handler) {
					$dispatcher = Dispatcher::getSharedDispatcher();
					return $handler->checkLogin($dispatcher->getSentData());

				};
				$app->post($loginCallback);
			});

			$app->path('logout', function($request) use ($handler, $app) {
				$getCallback = function ($request) use ($handler) {
					return $handler->logout();
				};
				$app->get($getCallback);
			});
		});
	}
} 