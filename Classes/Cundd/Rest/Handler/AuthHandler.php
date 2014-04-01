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
use Cundd\Rest\SessionManager;

class AuthHandler implements HandlerInterface {
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

	public function getStatus() {
		$login = $this->sessionManager->valueForKey('login');
		return array(
			'success' => $login ? $login : FALSE
		);
	}

	public function checkLogin($sentData) {
		$login = FALSE;
		if (isset($sentData['username']) && isset($sentData['apikey'])) {
			$username = $sentData['username'];
			$apikey = $sentData['apikey'];
			$login = $this->userProvider->checkCredentials($username, $apikey);
			$this->sessionManager->setValueForKey('login', $login);
		}
		return array(
			'success' => $login
		);
	}


	public function logout() {
		$this->sessionManager->setValueForKey('login', FALSE);
		return array(
			'success' => TRUE
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

		/** @var SessionManager $sessionManager */
		$sessionManager = $this->sessionManager;


		$app->path($dispatcher->getPath(), function ($request) use ($handler, $app, $sessionManager, $dispatcher) {
			$handler->setRequest($request);


			$app->path('login', function($request) use ($handler, $app, $sessionManager, $dispatcher) {
				$getCallback = function ($request) use ($handler, $sessionManager, $dispatcher) {
					return $handler->getStatus();
				};
				$app->get($getCallback);

				$loginCallback = function ($request) use ($handler, $sessionManager, $dispatcher) {
					return $handler->checkLogin($dispatcher->getSentData());

				};
				$app->post($loginCallback);
			});

			$app->path('logout', function($request) use ($handler, $app, $sessionManager, $dispatcher) {
				$getCallback = function ($request) use ($handler, $sessionManager, $dispatcher) {
					return $handler->logout();
				};
				$app->get($getCallback);
			});


//			/*
//			 * Handle an action
//			 */
//			$app->param('slug', function ($request, $identifier) use ($handler, $app) {
//				$handler->setIdentifier($identifier);
//
//				/*
//				 * Get single property
//				 */
//				$getPropertyCallback = function ($request, $propertyKey) use ($handler) {
//					return $handler->getProperty($propertyKey);
//				};
//				$app->param('slug', $getPropertyCallback);
//
//				/*
//				 * Show a single Model
//				 */
//				$getCallback = function ($request) use ($handler) {
//					return $handler->show();
//				};
//				$app->get($getCallback);
//
//				/*
//				 * Replace a Model
//				 */
//				$replaceCallback = function ($request) use ($handler) {
//					return $handler->replace();
//				};
//				$app->put($replaceCallback);
//				$app->post($replaceCallback);
//
//				/*
//				 * Update a Model
//				 */
//				$updateCallback = function ($request) use ($handler) {
//					return $handler->update();
//				};
//				$app->patch($updateCallback);
//
//				/*
//				 * Delete a Model
//				 */
//				$deleteCallback = function ($request) use ($handler) {
//					return $handler->delete();
//				};
//				$app->delete($deleteCallback);
//			});
//
//			/*
//			 * Create a Model
//			 */
//			$createCallback = function ($request) use ($handler) {
//				return $handler->create();
//			};
//			$app->post($createCallback);
//
//			/*
//			 * List all Models
//			 */
//			$listCallback = function ($request) use ($handler) {
//				return $handler->listAll();
//			};
//			$app->get($listCallback);
		});
	}
} 