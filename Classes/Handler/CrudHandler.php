<?php
declare(strict_types=1);

namespace Cundd\Rest\Handler;

use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;
use Exception;
use Iterator;
use IteratorAggregate;
use LimitIterator;

/**
 * Handler for default CRUD requests
 */
class CrudHandler implements CrudHandlerInterface, HandlerDescriptionInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ResponseFactoryInterface
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

    public function getDescription()
    {
        return 'Default Handler for CRUD requests';
    }

    public function getProperty(RestRequestInterface $request, string $identifier, string $propertyKey)
    {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $model = $dataProvider->fetchModel($identifier, $resourceType);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }

        return $dataProvider->getModelProperty($model, $propertyKey);
    }

    public function show(RestRequestInterface $request, string $identifier)
    {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $model = $dataProvider->fetchModel($identifier, $resourceType);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
    }

    public function create(RestRequestInterface $request)
    {
        $data = $request->getSentData();
        $this->logger->logRequest('create request', ['body' => $data]);

        if (null === $data) {
            return $this->responseFactory->createErrorResponse('Invalid or missing payload', 400, $request);
        }

        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $model = $dataProvider->createModel($data, $resourceType);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(
                'Could not create model from data',
                400,
                $request
            );
        } elseif ($model instanceof Exception) {
            return $this->responseFactory->createErrorResponse($model->getMessage(), 400, $request);
        }

        $dataProvider->saveModel($model, $resourceType);
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
    }

    public function update(RestRequestInterface $request, string $identifier)
    {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);

        $data = $request->getSentData();
        $data['__identity'] = $identifier;
        $this->logger->logRequest('update request', ['body' => $data]);

        // Make sure the object with the given identifier exists
        $oldObject = $dataProvider->fetchModel($identifier, $resourceType);
        if (!$oldObject) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }

        $model = $dataProvider->convertIntoModel($data, $resourceType);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(
                'Could not create model from data',
                400,
                $request
            );
        } elseif ($model instanceof Exception) {
            return $this->responseFactory->createErrorResponse($model->getMessage(), 400, $request);
        }

        $dataProvider->saveModel($model, $resourceType);
        $result = $dataProvider->getModelData($model);

        return $this->prepareResult($request, $result);
    }

    public function delete(RestRequestInterface $request, string $identifier)
    {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $this->logger->logRequest('delete request', ['identifier' => $identifier]);
        $model = $dataProvider->fetchModel($identifier, $resourceType);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }
        $dataProvider->removeModel($model, $resourceType);

        return $this->responseFactory->createSuccessResponse('Deleted', 200, $request);
    }

    public function listAll(RestRequestInterface $request)
    {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $allModels = $dataProvider->fetchAllModels($resourceType);

        return $this->prepareResult(
            $request,
            array_map([$dataProvider, 'getModelData'], $this->sliceResults($allModels)),
            false
        );
    }

    public function countAll(RestRequestInterface $request)
    {
        $resourceType = $request->getResourceType();

        return $this->getDataProvider($request)->countAllModels($resourceType);
    }

    /**
     * @return bool
     */
    public function options()
    {
        // TODO: Respond with the correct preflight headers
        return true;
    }

    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        $resourceType = $request->getResourceType();
        $router->add(Route::get($resourceType . '/?', [$this, 'listAll']));
        $router->add(Route::get($resourceType . '/_count/?', [$this, 'countAll']));
        $router->add(Route::post($resourceType . '/?', [$this, 'create']));
        $router->add(Route::get($resourceType . '/{slug}/?', [$this, 'show']));
        $router->add(Route::put($resourceType . '/{slug}/?', [$this, 'update']));
        $router->add(Route::post($resourceType . '/{slug}/?', [$this, 'update']));
        $router->add(Route::delete($resourceType . '/{slug}/?', [$this, 'delete']));
        $router->add(Route::patch($resourceType . '/{slug}/?', [$this, 'update']));
        $router->add(Route::get($resourceType . '/{slug}/{slug}/?', [$this, 'getProperty']));
        $router->add(Route::options($resourceType . '/?', [$this, 'options']));
    }

    /**
     * Return the Data Provider
     *
     * @param RestRequestInterface $request
     * @return DataProviderInterface
     */
    protected function getDataProvider(RestRequestInterface $request)
    {
        return $this->objectManager->getDataProvider($request);
    }

    /**
     * Add the root object key if configured
     *
     * @param RestRequestInterface $request
     * @param mixed                $result
     * @param bool                 $singularize
     * @return mixed|array
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

    /**
     * @param iterable|array $models
     * @return array|LimitIterator
     */
    protected function sliceResults($models)
    {
        $limit = $this->getListLimit();
        if (is_array($models)) {
            return array_slice($models, 0, $limit, true);
        }
        if ($models instanceof IteratorAggregate) {
            $models = $models->getIterator();
        }
        if ($models instanceof Iterator) {
            return iterator_to_array(new LimitIterator($models, 0, $limit));
        }

        return $models;
    }

    /**
     * Specifies the maximum number of models that should be output in `listAll()`
     *
     * @return int
     */
    protected function getListLimit(): int
    {
        return PHP_INT_MAX;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
