<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02.01.17
 * Time: 13:32
 */

namespace Cundd\Rest\Router;


use Cundd\Rest\Exception\InvalidArgumentException;

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
     * Route constructor
     *
     * @param string $pattern
     * @param string $method
     */
    public function __construct($pattern, $method = 'GET')
    {
        $this->assertString($pattern, 'pattern');
        $this->assertString($method, 'method');

        $this->pattern = trim($pattern, '/');
        $this->method = strtoupper($method);
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
        if (!$this->parameters) {
            $this->parameters = array_filter(array_map([$this, 'createParameter'], explode('/', $this->pattern)));
        }

        return $this->parameters;
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

    private function createParameter($input)
    {
        if (substr($input, 0, 1) !== '{' || substr($input, -1) !== '}') {
            return null;
        }
        $type = substr($input, 1, -1);
        switch (strtolower($type)) {
            case 'integer':
            case 'int':
                return ParameterTypeInterface::INTEGER;

            case 'slug':
            case 'string':
                return ParameterTypeInterface::SLUG;

            case 'float':
            case 'double':
            case 'number':
                return ParameterTypeInterface::FLOAT;

            case 'bool':
            case 'boolean':
                return ParameterTypeInterface::BOOLEAN;

            default:

        }
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
