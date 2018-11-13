<?php

namespace Cundd\Rest\Handler;

use Countable;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * Handler for default CRUD requests
 */
class CrudHandler implements CrudHandlerInterface, HandlerDescriptionInterface
{
    /**
     * Current request
     *
     * @var RestRequestInterface
     * @deprecated will be removed in 4.0.0
     */
    protected $request;

    /**
     * Unique identifier of the currently matching Domain Model
     *
     * @var string
     * @deprecated will be removed in 4.0.0
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
     * @param ObjectManagerInterface   $objectManager
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * Return the description of the handler
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Default Handler for CRUD requests';
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
        if ($this->getAddRootObjectForCollection()) {
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
     *
     * @return bool
     */
    public function options()
    {
        // TODO: Respond with the correct preflight headers
        return true;
    }

    /**
     * Let the handler configure the routes
     *
     * @param RouterInterface      $router
     * @param RestRequestInterface $request
     */
    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $resourceType = $request->getResourceType();
        $router->add(Route::get($resourceType . '/?', [$this, 'listAll']));
        $router->add(Route::get($resourceType . '/_count/?', [$this, 'countAll']));
        $router->add(Route::post($resourceType . '/?', [$this, 'create']));
        $router->add(Route::get($resourceType . '/{slug}/?', [$this, 'show']));
        $router->add(Route::put($resourceType . '/{slug}/?', [$this, 'replace']));
        $router->add(Route::post($resourceType . '/{slug}/?', [$this, 'replace']));
        $router->add(Route::delete($resourceType . '/{slug}/?', [$this, 'delete']));
        $router->add(Route::routeWithPatternAndMethod($resourceType . '/{slug}/?', 'PATCH', [$this, 'replace']));
        $router->add(Route::get($resourceType . '/{slug}/{slug}/?', [$this, 'getProperty']));
        $router->add(Route::routeWithPatternAndMethod($resourceType . '/?', 'OPTIONS', [$this, 'options']));
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
        if ($this->getAddRootObjectForCollection()) {
            return [
                Utility::singularize($request->getRootObjectKey()) => $result,
            ];
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function getAddRootObjectForCollection()
    {
        return (bool)$this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection');
    }
}
