<?php

declare(strict_types=1);

namespace Cundd\Rest\Router;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Route implements RouteInterface, RouteFactoryInterface
{
    private string $pattern;

    private int $priority;

    private array $parameters;

    private string $method;

    /**
     * @var callable
     */
    private $callback;

    public function __construct(ResourceType|string $pattern, string $method, callable $callback)
    {
        $this->pattern = $this->normalizePattern($pattern);
        $this->method = strtoupper($method);
        $this->callback = $callback;
        $this->parameters = ParameterType::extractParameterTypesFromPattern($this->pattern);
    }

    public static function get(string|ResourceType $pattern, callable $callback): RouteInterface
    {
        return new static($pattern, 'GET', $callback);
    }

    public static function post(string|ResourceType $pattern, callable $callback): RouteInterface
    {
        return new static($pattern, 'POST', $callback);
    }

    public static function put(string|ResourceType $pattern, callable $callback): RouteInterface
    {
        return new static($pattern, 'PUT', $callback);
    }

    public static function delete(string|ResourceType $pattern, callable $callback): RouteInterface
    {
        return new static($pattern, 'DELETE', $callback);
    }

    public static function options(string|ResourceType $pattern, callable $callback): RouteInterface
    {
        return new static($pattern, 'OPTIONS', $callback);
    }

    public static function patch(string|ResourceType $pattern, callable $callback): RouteInterface
    {
        return new static($pattern, 'PATCH', $callback);
    }

    /**
     * Creates a new Route with the given pattern and callback for the method GET
     *
     * @param string|ResourceType $pattern
     * @param callable            $callback
     * @return static
     */
    public static function routeWithPattern(ResourceType|string $pattern, callable $callback): RouteInterface
    {
        return new static($pattern, 'GET', $callback);
    }

    /**
     * Creates a new Route with the given pattern, method and callback
     *
     * @param string|ResourceType $pattern
     * @param string              $method
     * @param callable            $callback
     * @return static
     */
    public static function routeWithPatternAndMethod($pattern, string $method, callable $callback): RouteInterface
    {
        return new static($pattern, $method, $callback);
    }

    /**
     * Returns the normalized path pattern
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Returns the request method for this route
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns the requested parameters
     *
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Process the route
     *
     * @param RestRequestInterface $request
     * @param array                $parameters
     * @return ResponseInterface|mixed
     */
    public function process(RestRequestInterface $request, ...$parameters)
    {
        $callback = $this->callback;

        return $callback($request, ...$parameters);
    }

    /**
     * The __invoke method is called when a script tries to call an object as a function.
     *
     * @param RestRequestInterface $request
     * @param array                $arguments
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.invoke
     */
    public function __invoke(RestRequestInterface $request, ...$arguments)
    {
        return $this->process($request, ...$arguments);
    }

    /**
     * Returns the priority of this route
     *
     * Deeper nested paths have a higher priority. Fixed paths have precedence over paths with parameter expressions.
     *
     * @return int
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
     * @param string|ResourceType $inputPattern
     * @return string
     */
    private function normalizePattern(ResourceType|string $inputPattern): string
    {
        $pattern = '/' . ltrim((string)$inputPattern, '/');
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
