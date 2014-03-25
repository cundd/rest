<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 25.03.14
 * Time: 14:38
 */

namespace Cundd\Rest;

use Bullet\App;
use Cundd\Rest\Dispatcher;
use Cundd\Rest\DataProvider\Utility;

/**
 * Handler for requests
 *
 * @package Cundd\Rest
 */
class Handler implements HandlerInterface {
	/**
	 * Current request
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Unique identifier of the currently matching Domain Model
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * Sets the current request
	 *
	 * @param \Cundd\Rest\Request $request
	 * @return $this
	 */
	public function setRequest($request) {
		$this->request    = $request;
		$this->identifier = NULL;
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
	 * Returns the unique identifier of the currently matching Domain Model
	 *
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Sets the unique identifier of the currently matching Domain Model
	 *
	 * @param string $identifier
	 * @return $this
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
		return $this;
	}

	/**
	 * Returns the given property of the currently matching Model
	 *
	 * @param string $propertyKey
	 * @return mixed
	 */
	public function getProperty($propertyKey) {
		// $getPropertyCallback = function ($request, $propertyKey) use($uid, $dispatcher, $app) {
		$dispatcher = Dispatcher::getSharedDispatcher();
		$model      = $dispatcher->getModelWithData($this->getIdentifier());
		if (!$model) {
			return 404;
		}
		return $dispatcher->getModelProperty($model, $propertyKey);
	}

	/**
	 * Returns the data of the current Model
	 *
	 * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
	 */
	public function show() {
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
		/* SHOW
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
		//$getCallback = function($request) use($uid, $dispatcher, $app) {
		$dispatcher = Dispatcher::getSharedDispatcher();
		$model      = $dispatcher->getModelWithData($this->getIdentifier());
		if (!$model) {
			return 404;
		}
		$result = $dispatcher->getModelData($model);
		if ($dispatcher->getObjectManager()->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
			return array(
				Utility::singularize($dispatcher->getRootObjectKey()) => $result
			);
		}
		return $result;
	}

	/**
	 * Replaces the currently matching Model with the data from the request
	 *
	 * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
	 */
	public function replace() {
//		$replaceCallback = function($request) use($uid, $dispatcher, $app) {
		$dispatcher = Dispatcher::getSharedDispatcher();

		/** @var \Cundd\Rest\Request $request */
		$data               = $dispatcher->getSentData();
		$data['__identity'] = $this->getIdentifier();
		$dispatcher->logRequest('update request', array('body' => $data));

		$oldModel = $dispatcher->getModelWithData($this->getIdentifier());
		if (!$oldModel) {
			return 404;
		}

		/**
		 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
		 */
		$model = $dispatcher->getModelWithData($data);
		if (!$model) {
			return 400;
		}

		$dispatcher->saveModel($model);
		$result = $dispatcher->getModelData($model);
		if ($dispatcher->getObjectManager()->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
			return array(
				Utility::singularize($dispatcher->getRootObjectKey()) => $result
			);
		}
		return $result;
	}

	/**
	 * Updates the currently matching Model with the data from the request
	 *
	 * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
	 */
	public function update() {
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
		/* UPDATE																	 */
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
//		$updateCallback = function($request) use($uid, $dispatcher, $app) {
		$dispatcher = Dispatcher::getSharedDispatcher();
		/** @var \Cundd\Rest\Request $request */
		$data               = $dispatcher->getSentData();
		$data['__identity'] = $this->getIdentifier();
		$dispatcher->logRequest('update request', array('body' => $data));

		$model = $dispatcher->getModelWithData($data);

		if (!$model) {
			return 404;
		}

		$dispatcher->saveModel($model);
		$result = $dispatcher->getModelData($model);
		if ($dispatcher->getObjectManager()->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
			return array(
				Utility::singularize($dispatcher->getRootObjectKey()) => $result
			);
		}
		return $result;
	}

	/**
	 * Deletes the currently matching Model
	 *
	 * @return integer Returns 200 an success
	 */
	public function delete() {
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
		/* REMOVE																	 */
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
//		$deleteCallback = function($request) use($uid, $dispatcher, $app) {
		$dispatcher = Dispatcher::getSharedDispatcher();
		$model      = $dispatcher->getModelWithData($this->getIdentifier());
		if ($model) {
			$dispatcher->removeModel($model);
		}
		return 200;
	}

	/**
	 * Creates a new Model with the data from the request
	 *
	 * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
	 */
	public function create() {
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
		/* CREATE																	 */
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
//		$createCallback = function($request) use($dispatcher, $app) {
		$dispatcher = Dispatcher::getSharedDispatcher();
		/** @var \Cundd\Rest\Request $request */
		$data = $dispatcher->getSentData();
		$dispatcher->logRequest('create request', array('body' => $data));

		/**
		 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
		 */
		$model = $dispatcher->getModelWithData($data);
		if (!$model) {
			return 400;
		}

		$dispatcher->saveModel($model);
		$result = $dispatcher->getObjectManager()->getDataProvider()->getModelData($model);
		if ($dispatcher->getObjectManager()->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
			return array(
				Utility::singularize($dispatcher->getRootObjectKey()) => $result
			);
		}
		return $result;
	}

	/**
	 * List all Models
	 *
	 * @return array Returns all Models
	 */
	public function listAll() {
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
		/* LIST 																	 */
		/* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
//		$listCallback = function($request) use($dispatcher, $app) {
		$dispatcher = Dispatcher::getSharedDispatcher();
		$allModels  = $dispatcher->getAllModels();
		if (!is_array($allModels)) {
			$allModels = iterator_to_array($allModels);
		}

		$result = array_map(array($dispatcher->getObjectManager()->getDataProvider(), 'getModelData'), $allModels);
		if ($dispatcher->getObjectManager()->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
			return array(
				$dispatcher->getRootObjectKey() => $result
			);
		}
		return $result;
	}

	/**
	 * Configure the API paths
	 */
	public function configureApiPaths() {
		$dispatcher = Dispatcher::getSharedDispatcher();

		/** @var App $app */
		$app = $dispatcher->getApp();

		/** @var HandlerInterface */
		$handler = $this;


		$app->path($dispatcher->getPath(), function ($request) use ($handler, $app) {
			$handler->setRequest($request);

			/*
			 * Handle a specific Model
			 */
			$app->param('slug', function ($request, $identifier) use ($handler, $app) {
				$handler->setIdentifier($identifier);

				/*
				 * Get single property
				 */
				$getPropertyCallback = function ($request, $propertyKey) use ($handler) {
					return $handler->getProperty($propertyKey);
				};
				$app->param('slug', $getPropertyCallback);

				/*
				 * Show a single Model
				 */
				$getCallback = function ($request) use ($handler) {
					return $handler->show();
				};
				$app->get($getCallback);

				/*
				 * Replace a Model
				 */
				$replaceCallback = function ($request) use ($handler) {
					return $handler->replace();
				};
				$app->put($replaceCallback);
				$app->post($replaceCallback);

				/*
				 * Update a Model
				 */
				$updateCallback = function ($request) use ($handler) {
					return $handler->update();
				};
				$app->patch($updateCallback);

				/*
				 * Delete a Model
				 */
				$deleteCallback = function ($request) use ($handler) {
					return $handler->delete();
				};
				$app->delete($deleteCallback);
			});

			/*
			 * Create a Model
			 */
			$createCallback = function ($request) use ($handler) {
				return $handler->create();
			};
			$app->post($createCallback);

			/*
			 * List all Models
			 */
			$listCallback = function ($request) use ($handler) {
				return $handler->listAll();
			};
			$app->get($listCallback);
		});
	}

} 