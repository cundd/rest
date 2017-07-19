<?php

namespace Cundd\Rest\Router;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for Routes
 */
interface RouteInterface
{
    /**
     * Returns the normalized path pattern
     *
     * @return string
     */
    public function getPattern();

    /**
     * Returns the request method for this route
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns the requested parameters
     *
     * @return string[]
     */
    public function getParameters();

    /**
     * Returns the priority of this route
     *
     * Deeper nested paths have a higher priority. Fixed paths have precedence over paths with parameter expressions.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Process the route
     *
     * @param RestRequestInterface $request
     * @param array                $parameters
     * @return ResponseInterface
     */
    public function process(RestRequestInterface $request, ...$parameters);
}
