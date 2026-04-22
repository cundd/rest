<?php

declare(strict_types=1);

namespace Cundd\Rest\Router;

use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request\RequestType;
use Cundd\Rest\Request\ResourceType;
use Psr\Http\Message\ResponseInterface;

final class Route implements RouteInterface, RouteFactoryInterface
{
    /**
     * @var non-empty-string
     */
    private string $pattern;

    private int $priority;

    /**
     * @var non-empty-string[]
     */
    private array $parameters;

    /**
     * @var non-empty-string
     */
    private string $method;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @param non-empty-string|ResourceType $pattern
     * @param non-empty-string              $method
     */
    public function __construct(
        ResourceType|string $pattern,
        string $method,
        callable $callback,
    ) {
        if (!$method) {
            throw new InvalidArgumentException('Argument "method" must not be empty');
        }
        $this->pattern = $this->normalizePattern($pattern);
        $this->method = strtoupper($method);

        $this->callback = $callback;
        $this->parameters = ParameterType::extractParameterTypesFromPattern(
            $this->pattern
        );
    }

    /**
     * @param non-empty-string|ResourceType $pattern
     */
    public static function get(
        string|ResourceType $pattern,
        callable $callback,
    ): RouteInterface {
        return new static($pattern, 'GET', $callback);
    }

    /**
     * @param non-empty-string|ResourceType $pattern
     */
    public static function post(
        string|ResourceType $pattern,
        callable $callback,
    ): RouteInterface {
        return new static($pattern, 'POST', $callback);
    }

    /**
     * @param non-empty-string|ResourceType $pattern
     */
    public static function put(
        string|ResourceType $pattern,
        callable $callback,
    ): RouteInterface {
        return new static($pattern, 'PUT', $callback);
    }

    /**
     * @param non-empty-string|ResourceType $pattern
     */
    public static function delete(
        string|ResourceType $pattern,
        callable $callback,
    ): RouteInterface {
        return new static($pattern, 'DELETE', $callback);
    }

    /**
     * @param non-empty-string|ResourceType $pattern
     */
    public static function options(
        string|ResourceType $pattern,
        callable $callback,
    ): RouteInterface {
        return new static($pattern, 'OPTIONS', $callback);
    }

    /**
     * @param non-empty-string|ResourceType $pattern
     */
    public static function patch(
        string|ResourceType $pattern,
        callable $callback,
    ): RouteInterface {
        return new static($pattern, 'PATCH', $callback);
    }

    /**
     * Create a new Route with the given pattern and callback for the method GET
     *
     * @param non-empty-string|ResourceType $pattern
     */
    public static function routeWithPattern(
        ResourceType|string $pattern,
        callable $callback,
    ): RouteInterface {
        return new static($pattern, 'GET', $callback);
    }

    /**
     * Create a new Route with the given pattern, method and callback
     *
     * @param non-empty-string|ResourceType $pattern
     * @param non-empty-string              $method
     */
    public static function routeWithPatternAndMethod(
        ResourceType|string $pattern,
        string $method,
        callable $callback,
    ): RouteInterface {
        return new static($pattern, $method, $callback);
    }

    /**
     * Return the normalized path pattern
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Return the request method for this route
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Return the requested parameters
     *
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getRequestType(): RequestType
    {
        return RequestType::fromMethod($this->getMethod());
    }

    /**
     * Process the route
     *
     * @param mixed[] $parameters
     *
     * @return ResponseInterface|mixed
     */
    public function process(
        RestRequestInterface $request,
        mixed ...$parameters,
    ): mixed {
        $callback = $this->callback;

        return $callback($request, ...$parameters);
    }

    /**
     * @param mixed[] $arguments
     */
    public function __invoke(
        RestRequestInterface $request,
        mixed ...$arguments,
    ): mixed {
        return $this->process($request, ...$arguments);
    }

    /**
     * Return the priority of this route
     *
     * Deeper nested paths have a higher priority. Fixed paths have precedence over paths with parameter expressions.
     */
    public function getPriority(): int
    {
        if (!isset($this->priority)) {
            $this->priority = $this->determinePriority();
        }

        return $this->priority;
    }

    /**
     * Normalize the path pattern
     *
     * @return non-empty-string
     */
    private function normalizePattern(ResourceType|string $inputPattern): string
    {
        if ('' === (string) $inputPattern) {
            throw new InvalidArgumentException('Argument "pattern" must not be empty');
        }
        $pattern = '/' . ltrim((string) $inputPattern, '/');
        $patternParts = explode('/', $pattern);
        $parameterTypes = ParameterType::extractParameterTypesFromPattern($pattern);

        foreach ($parameterTypes as $index => $type) {
            $patternParts[$index] = '{' . $type . '}';
        }

        return implode('/', $patternParts);
    }

    private function determinePriority(): int
    {
        if ('/' === $this->pattern) {
            return 0;
        }

        $pattern = ltrim($this->pattern, '/');

        return 1 + 10 * substr_count($pattern, '/') - substr_count($pattern, '{');
    }
}
