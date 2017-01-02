<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02.01.17
 * Time: 13:32
 */

namespace Cundd\Rest\Router;


use Cundd\Rest\Exception\InvalidArgumentException;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Route implements RouteInterface
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string
     */
    private $method;

    /**
     * @var callable
     */
    private $callback;

    /**
     * Route constructor
     *
     * @param string   $pattern
     * @param string   $method
     * @param callable $callback
     */
    public function __construct($pattern, $method, callable $callback)
    {
        $this->assertString($pattern, 'pattern');
        $this->assertString($method, 'method');

        $this->pattern = trim($pattern, '/');
        $this->method = strtoupper($method);
        $this->callback = $callback;
        $this->parameters = ParameterType::extractParameterTypesFromPattern($pattern);
    }

    /**
     * Creates a new Route with the given pattern and callback for the method GET
     *
     * @param string   $pattern
     * @param callable $callback
     * @return static
     */
    public static function routeWithPattern($pattern, callable $callback)
    {
        return new static($pattern, 'GET', $callback);
    }

    /**
     * Creates a new Route with the given pattern, method and callback
     *
     * @param string   $pattern
     * @param string   $method
     * @param callable $callback
     * @return static
     */
    public static function routeWithPatternAndMethod($pattern, $method, callable $callback)
    {
        return new static($pattern, $method, $callback);
    }

    /**
     * Returns the path pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Returns the request method for this route
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the requested parameters
     *
     * @return string[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Process the route
     *
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    public function process(RestRequestInterface $request)
    {
        $callback = $this->callback;

        return $callback($request);
    }

    /**
     * The __invoke method is called when a script tries to call an object as a function.
     *
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.invoke
     */
    public function __invoke(RestRequestInterface $request)
    {
        return $this->process($request);
    }

    /**
     * Returns the priority of this route
     *
     * Deeper nested paths have a higher priority. Fixed paths have precedence over paths with parameter expressions.
     *
     * @return int
     */
    public function getPriority()
    {
        if (!$this->priority) {
            if ('' === $this->pattern) {
                $this->priority = 0;
            } else {
                $this->priority = 1 + 10 * substr_count($this->pattern, '/') - substr_count($this->pattern, '{');
            }
        }

        return $this->priority;
    }

    /**
     * @param mixed  $input
     * @param string $argumentName
     */
    private function assertString($input, $argumentName)
    {
        if (!is_string($input)) {
            throw InvalidArgumentException::buildException($input, 'string', $argumentName);
        }
    }
}
