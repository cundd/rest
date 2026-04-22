<?php

declare(strict_types=1);

namespace Cundd\Rest\Handler;

use Cundd\Rest\DataProvider\DataProviderInterface;
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
use Psr\Http\Message\ResponseInterface;

/**
 * Handler for default CRUD requests
 */
class CrudHandler implements CrudHandlerInterface, HandlerDescriptionInterface
{
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager,
        protected readonly ResponseFactoryInterface $responseFactory,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function getDescription(): string
    {
        return 'Default Handler for CRUD requests';
    }

    public function getProperty(
        RestRequestInterface $request,
        string $identifier,
        string $propertyKey,
    ): mixed {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $model = $dataProvider->fetchModel($request, $identifier);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(
                null,
                404,
                $request
            );
        }

        return $dataProvider->getModelProperty(
            $request,
            $model,
            $propertyKey
        );
    }

    public function show(
        RestRequestInterface $request,
        string $identifier,
    ): array|int|ResponseInterface {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $model = $dataProvider->fetchModel($request, $identifier);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(
                null,
                404,
                $request
            );
        }
        $result = $dataProvider->getModelData($request, $model);

        return $this->prepareResult($request, $result);
    }

    public function create(RestRequestInterface $request): array|int|ResponseInterface
    {
        $data = $request->getSentData();
        $this->logger->logRequest('create request', ['body' => $data]);

        if (null === $data) {
            return $this->responseFactory->createErrorResponse(
                'Invalid or missing payload',
                400,
                $request
            );
        }

        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $model = $dataProvider->createModel($request, $data);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(
                'Could not create model from data',
                400,
                $request
            );
        } elseif ($model instanceof Exception) {
            return $this->responseFactory->createErrorResponse(
                $model->getMessage(),
                400,
                $request
            );
        }

        $dataProvider->saveModel($request, $model);
        $result = $dataProvider->getModelData($request, $model);

        return $this->prepareResult($request, $result);
    }

    public function update(
        RestRequestInterface $request,
        string $identifier,
    ): array|int|ResponseInterface {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);

        $data = $request->getSentData();
        $data['__identity'] = $identifier;
        $this->logger->logRequest('update request', ['body' => $data]);

        // Make sure the object with the given identifier exists
        $oldObject = $dataProvider->fetchModel($request, $identifier);
        if (!$oldObject) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }

        $model = $dataProvider->convertIntoModel($request, $data);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(
                'Could not create model from data',
                400,
                $request
            );
        } elseif ($model instanceof Exception) {
            return $this->responseFactory->createErrorResponse(
                $model->getMessage(),
                400,
                $request
            );
        }

        $dataProvider->saveModel($request, $model);
        $result = $dataProvider->getModelData($request, $model);

        return $this->prepareResult($request, $result);
    }

    public function delete(
        RestRequestInterface $request,
        string $identifier,
    ): ResponseInterface {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $this->logger->logRequest('delete request', ['identifier' => $identifier]);
        $model = $dataProvider->fetchModel($request, $identifier);
        if (!$model) {
            return $this->responseFactory->createErrorResponse(null, 404, $request);
        }
        $dataProvider->removeModel($request, $model);

        return $this->responseFactory->createSuccessResponse('Deleted', 200, $request);
    }

    public function listAll(RestRequestInterface $request): iterable
    {
        $resourceType = $request->getResourceType();
        $dataProvider = $this->getDataProvider($request);
        $allModels = $dataProvider->fetchAllModels($request);
        $uri = $request->getUri();

        return $this->prepareResult(
            $request,
            array_map(
                fn (object $model) => $dataProvider->getModelData($request, $model),
                $this->sliceResults($allModels)
            ),
        );
    }

    public function countAll(RestRequestInterface $request): int
    {
        $resourceType = $request->getResourceType();

        return $this->getDataProvider($request)->countAllModels($request);
    }

    public function options(): bool
    {
        // TODO: Respond with the correct preflight headers
        return true;
    }

    public function configureRoutes(RouterInterface $router, RestRequestInterface $request): void
    {
        $resourceType = $request->getResourceType();
        $router->add(Route::get($resourceType . '/?', $this->listAll(...)));
        $router->add(Route::get($resourceType . '/_count/?', $this->countAll(...)));
        $router->add(Route::post($resourceType . '/?', $this->create(...)));
        $router->add(Route::get($resourceType . '/{slug}/?', $this->show(...)));
        $router->add(Route::put($resourceType . '/{slug}/?', $this->update(...)));
        $router->add(Route::post($resourceType . '/{slug}/?', $this->update(...)));
        $router->add(Route::delete($resourceType . '/{slug}/?', $this->delete(...)));
        $router->add(Route::patch($resourceType . '/{slug}/?', $this->update(...)));
        $router->add(Route::get($resourceType . '/{slug}/{slug}/?', $this->getProperty(...)));
        $router->add(Route::options($resourceType . '/?', $this->options(...)));
    }

    /**
     * Return the Data Provider
     */
    protected function getDataProvider(RestRequestInterface $request): DataProviderInterface
    {
        return $this->objectManager->getDataProvider($request);
    }

    /**
     * @return mixed|array
     */
    protected function prepareResult(
        RestRequestInterface $request,
        mixed $result,
    ): mixed {
        return $result;
    }

    /**
     * @template T
     *
     * @param iterable<T>|array<T> $models
     *
     * @return array<T>
     */
    protected function sliceResults(iterable $models): array
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

        return $this->sliceResults(iterator_to_array($models));
    }

    /**
     * Specifies the maximum number of models that should be output in `listAll()`
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
