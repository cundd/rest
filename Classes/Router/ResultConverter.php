<?php

namespace Cundd\Rest\Router;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ErrorHandler;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ResponseFactoryInterface;
use Cundd\Rest\Router\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class to convert simple Router results into Response instances and handle exceptions
 */
class ResultConverter implements RouterInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var callable
     */
    private $exceptionHandler;

    /**
     * Result converter constructor
     *
     * @param RouterInterface          $router
     * @param ResponseFactoryInterface $responseFactory
     * @param callable                 $exceptionHandler
     */
    public function __construct(
        RouterInterface $router,
        ResponseFactoryInterface $responseFactory,
        callable $exceptionHandler
    ) {
        $this->router = $router;
        $this->responseFactory = $responseFactory;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Dispatch the request to the router and convert the result
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RestRequestInterface $request)
    {
        try {
            $result = $this->router->dispatch($request);
        } catch (\Exception $exception) {
            $result = $exception;
        }
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if ($result instanceof NotFoundException) {
            return $this->responseFactory->createErrorResponse($result->getMessage() ?: null, 404, $request);
        }
        if ($result instanceof \Exception) {
            return $this->exceptionToResponse($result, $request);
        }

        return $this->responseFactory->createSuccessResponse($result, 200, $request);
    }

    /**
     * Add the given Route
     *
     * @param Route $route
     * @return RouterInterface
     */
    public function add(Route $route)
    {
        $this->router->add($route);

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method GET
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routeGet($pattern, callable $callback)
    {
        $this->router->routeGet($pattern, $callback);

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method POST
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routePost($pattern, callable $callback)
    {
        $this->router->routePost($pattern, $callback);

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method PUT
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routePut($pattern, callable $callback)
    {
        $this->router->routePut($pattern, $callback);

        return $this;
    }

    /**
     * Creates and registers a new Route with the given pattern and callback for the method DELETE
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return RouterInterface
     */
    public function routeDelete($pattern, callable $callback)
    {
        $this->router->routeDelete($pattern, $callback);

        return $this;
    }


    /**
     * Convert exceptions that occurred during the dispatching
     *
     * @param \Exception           $exception
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    private function exceptionToResponse(\Exception $exception, RestRequestInterface $request)
    {
        try {
            $exceptionHandler = $this->exceptionHandler;
            $exceptionHandler($exception, $request);
        } catch (\Exception $handlerError) {
        }

        if ($this->getShowDebugInformation()) {
            $exceptionDetails = $this->getDebugDetails($exception);
        } else {
            $exceptionDetails = sprintf('Sorry! Something is wrong. Exception code #%d', $exception->getCode());
        }

        return $this->responseFactory->createErrorResponse($exceptionDetails, 501, $request);
    }

    /**
     * @param $exception
     * @return array
     */
    private function getDebugTrace(\Exception $exception)
    {
        return array_map(
            function ($step) {
                $arguments = count($step['args']) > 0 ? sprintf('(%d Arguments)', count($step['args'])) : '()';
                if (isset($step['class'])) {
                    return $step['class'] . $step['type'] . $step['function'] . $arguments;
                }
                if (isset($step['function'])) {

                    return $step['function'] . $arguments;
                }

                return '';
            },
            $exception->getTrace()
        );
    }

    /**
     * @param $exception
     * @return array
     */
    private function getDebugDetails(\Exception $exception)
    {
        return [
            'error' => sprintf(
                '%s #%d: %s',
                get_class($exception),
                $exception->getCode(),
                $exception->getMessage()
            ),
            'trace' => $this->getDebugTrace($exception),
        ];
    }

    /**
     * @return bool
     */
    private function getShowDebugInformation()
    {
        return ErrorHandler::getShowDebugInformation();
    }
}
