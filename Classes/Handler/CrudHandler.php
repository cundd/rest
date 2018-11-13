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

    public function getProperty(RestRequestInterface $request, $identifier, $propertyKey)
    {
        $dataProvider = $this->getDataProvider();
        $model = $dataProvider->getModelWithDataForResourceType($identifier, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }

        return $dataProvider->getModelProperty($model, $propertyKey);
    }

    public function show(RestRequestInterface $request, $identifier)
    {
        $dataProvider = $this->getDataProvider();
        $model = $dataProvider->getModelWithDataForResourceType($identifier, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
    }

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

        $model = $dataProvider->getModelWithDataForResourceType($data, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 400, $request);
        }

        $dataProvider->saveModelForResourceType($model, $request->getResourceType());
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
    }

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

    public function delete(RestRequestInterface $request, $identifier)
    {
        $dataProvider = $this->getDataProvider();
        $this->logger->logRequest('delete request', ['identifier' => $identifier]);
        $model = $dataProvider->getModelWithDataForResourceType($identifier, $request->getResourceType());
        if (!$model) {
            return $this->responseFactory->createSuccessResponse(null, 404, $request);
        }
        $dataProvider->removeModelForResourceType($model, $request->getResourceType());

        return $this->responseFactory->createSuccessResponse('Deleted', 200, $request);
    }

    public function create(RestRequestInterface $request)
    {
        $dataProvider = $this->getDataProvider();

        $data = $request->getSentData();
        $this->logger->logRequest('create request', ['body' => $data]);

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
        $dataProvider = $this->getDataProvider();

        $allModels = $dataProvider->getAllModelsForResourceType($request->getResourceType());
        if (!is_array($allModels) && $allModels instanceof Traversable) {
            $allModels = iterator_to_array($allModels);
        }

        return $this->prepareResult($request, array_map([$dataProvider, 'getModelData'], $allModels), false);
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
     * @param bool                 $singularize
     * @return array
     */
    protected function prepareResult(RestRequestInterface $request, $result, $singularize = true)
    {
        if ($this->getAddRootObjectForCollection()) {
            $key = $singularize ? Utility::singularize($request->getRootObjectKey()) : $request->getRootObjectKey();

            return [$key => $result];
        }

        return $result;
    }

    /**
     * Return if the root object key should be added to the response data
     *
     * @return bool
     */
    protected function getAddRootObjectForCollection()
    {
        return (bool)$this->objectManager->getConfigurationProvider()->getSetting('addRootObjectForCollection');
    }
}
