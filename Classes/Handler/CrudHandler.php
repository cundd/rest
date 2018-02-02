<?php

namespace Cundd\Rest\Handler;

use Countable;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * Handler for default CRUD requests
 */
class CrudHandler implements CrudHandlerInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Handler constructor
     *
     * @param ObjectManager            $objectManager
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ObjectManager $objectManager,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
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

        return $this->prepareResult($request, $result);
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
        $this->logger->logRequest('update request', ['body' => $data]);

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

        return $this->prepareResult($request, $result);
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
        $this->logger->logRequest('update request', ['body' => $data]);

        $model = $dataProvider->getModelWithDataForResourceType($data, $request->getResourceType());

        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }

        $dataProvider->saveModelForResourceType($model, $request->getResourceType());
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
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

        return $this->responseFactory->createSuccessResponse('Deleted', 200, $request);
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
        $this->logger->logRequest('create request', ['body' => $data]);

        /**
         * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
         */
        $model = $dataProvider->getModelWithDataForResourceType($data, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 400, $request);
        }

        $dataProvider->saveModelForResourceType($model, $request->getResourceType());
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
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

        $result = array_map([$dataProvider, 'getModelData'], $allModels);
        if ($this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
            return [
                $request->getRootObjectKey() => $result,
            ];
        }

        return $result;
    }

    /**
     * Count all Models
     *
     * @param RestRequestInterface $request
     * @return int Returns the number of models
     */
    public function countAll(RestRequestInterface $request)
    {
        $allModels = $this->getDataProvider()->getAllModelsForResourceType($request->getResourceType());

        if (is_array($allModels) || $allModels instanceof Countable) {
            return count($allModels);
        }
        if ($allModels instanceof Traversable) {
            return count(iterator_to_array($allModels));
        }

        return NAN;
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
        $router->add(Route::get($request->getResourceType() . '/_count/?', [$this, 'countAll']));
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

    /**
     * Add the root object key if configured
     *
     * @param RestRequestInterface $request
     * @param mixed                $result
     * @return array
     */
    protected function prepareResult(RestRequestInterface $request, $result)
    {
        if ($this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection')) {
            return [
                Utility::singularize($request->getRootObjectKey()) => $result,
            ];
        }

        return $result;
    }
}
