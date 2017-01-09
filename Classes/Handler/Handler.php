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

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 25.03.14
 * Time: 14:38
 */

namespace Cundd\Rest\Handler;

use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Dispatcher;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * Handler for requests
 */
class Handler implements CrudHandlerInterface
{
    /**
     * Current request
     *
     * @var RestRequestInterface
     */
    protected $request;

    /**
     * Unique identifier of the currently matching Domain Model
     *
     * @var string
     */
    protected $identifier;

    /**
     * Object Manager
     *
     * @var \Cundd\Rest\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Cundd\Rest\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Handler constructor
     *
     * @param ObjectManager            $objectManager
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ObjectManager $objectManager, ResponseFactoryInterface $responseFactory)
    {
        $this->objectManager = $objectManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Returns the given property of the currently matching Model
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @param string               $propertyKey
     * @return mixed
     */
    public function getProperty(RestRequestInterface $request, $identifier, $propertyKey)
    {
        $dataProvider = $this->getDataProvider();
        $model = $dataProvider->getModelWithDataForResourceType($identifier, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }

        return $dataProvider->getModelProperty($model, $propertyKey);
    }

    /**
     * Returns the data of the current Model
     *
     * @param RestRequestInterface $request
     * @param                      $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function show(RestRequestInterface $request, $identifier)
    {
        /* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
        /* SHOW
        /* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
        $dataProvider = $this->getDataProvider();
        $model = $dataProvider->getModelWithDataForResourceType($identifier, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }
        $result = $dataProvider->getModelData($model);
        if ($this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
            return array(
                Utility::singularize($request->getRootObjectKey()) => $result,
            );
        }

        return $result;
    }

    /**
     * Replaces the currently matching Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @param                      $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function replace(RestRequestInterface $request, $identifier)
    {
        $dataProvider = $this->getDataProvider();

        $data = $request->getSentData();
        $data['__identity'] = $identifier;
        Dispatcher::getSharedDispatcher()->logRequest('update request', array('body' => $data));

        $oldModel = $dataProvider->getModelWithDataForResourceType($identifier, $request->getResourceType());
        if (!$oldModel) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }

        /** @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model */
        $model = $dataProvider->getModelWithDataForResourceType($data, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 400, $request);
        }

        $dataProvider->saveModelForResourceType($model, $request->getResourceType());
        $result = $dataProvider->getModelData($model);
        if ($this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
            return array(
                Utility::singularize($request->getRootObjectKey()) => $result,
            );
        }

        return $result;
    }

    /**
     * Updates the currently matching Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @param                      $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function update(RestRequestInterface $request, $identifier)
    {
        $dataProvider = $this->getDataProvider();

        $data = $request->getSentData();
        $data['__identity'] = $identifier;
        Dispatcher::getSharedDispatcher()->logRequest('update request', array('body' => $data));

        $model = $dataProvider->getModelWithDataForResourceType($data, $request->getResourceType());

        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }

        $dataProvider->saveModelForResourceType($model, $request->getResourceType());
        $result = $dataProvider->getModelData($model);
        if ($this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
            return array(
                Utility::singularize($request->getRootObjectKey()) => $result,
            );
        }

        return $result;
    }

    /**
     * Deletes the currently matching Model
     *
     * @param RestRequestInterface $request
     * @param                      $identifier
     * @return int|ResponseInterface Returns 200 an success
     */
    public function delete(RestRequestInterface $request, $identifier)
    {
        /* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
        /* REMOVE																	 */
        /* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
        $dataProvider = $this->getDataProvider();
        $model = $dataProvider->getModelWithDataForResourceType($identifier, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }
        $dataProvider->removeModelForResourceType($model, $request->getResourceType());

        return $this->responseFactory->createSuccessResponse(null, 200, $request);
    }

    /**
     * Creates a new Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function create(RestRequestInterface $request)
    {
        /* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
        /* CREATE																	 */
        /* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
        $dataProvider = $this->getDataProvider();

        $data = $request->getSentData();
        Dispatcher::getSharedDispatcher()->logRequest('create request', array('body' => $data));

        /**
         * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
         */
        $model = $dataProvider->getModelWithDataForResourceType($data, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 400, $request);
        }

        $dataProvider->saveModelForResourceType($model, $request->getResourceType());
        $result = $dataProvider->getModelData($model);
        if ($this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
            return array(
                Utility::singularize($request->getRootObjectKey()) => $result,
            );
        }

        return $result;
    }

    /**
     * List all Models
     *
     * @param RestRequestInterface $request
     * @return array Returns all Models
     */
    public function listAll(RestRequestInterface $request)
    {
        /* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
        /* LIST 																	 */
        /* MWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWMWM */
        $dataProvider = $this->getDataProvider();

        $allModels = $dataProvider->getAllModelsForResourceType($request->getResourceType());
        if (!is_array($allModels) && $allModels instanceof Traversable) {
            $allModels = iterator_to_array($allModels);
        }

        $result = array_map(array($dataProvider, 'getModelData'), $allModels);
        if ($this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
            return array(
                $request->getRootObjectKey() => $result,
            );
        }

        return $result;
    }

    /**
     * Let the handler configure the routes
     *
     * @param RouterInterface      $router
     * @param RestRequestInterface $request
     */
    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $router->add(Route::get($request->getResourceType() . '/?', [$this, 'listAll']));
        $router->add(Route::post($request->getResourceType() . '/?', [$this, 'create']));
        $router->add(Route::get($request->getResourceType() . '/{slug}/?', [$this, 'show']));
        $router->add(Route::put($request->getResourceType() . '/{slug}/?', [$this, 'replace']));
        $router->add(Route::post($request->getResourceType() . '/{slug}/?', [$this, 'replace']));
        $router->add(Route::delete($request->getResourceType() . '/{slug}/?', [$this, 'delete']));
        $router->add(
            Route::routeWithPatternAndMethod($request->getResourceType() . '/{slug}/?', 'PATCH', [$this, 'replace'])
        );
        $router->add(Route::get($request->getResourceType() . '/{slug}/{slug}/?', [$this, 'getProperty']));
    }

    /**
     * Returns the Data Provider
     *
     * @return DataProviderInterface
     */
    protected function getDataProvider()
    {
        return $this->objectManager->getDataProvider();
    }
}
