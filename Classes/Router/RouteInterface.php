<?php

declare(strict_types=1);

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
     */
    public function getPattern(): string;

    /**
     * Returns the request method for this route
     */
    public function getMethod(): string;

    /**
     * Returns the requested parameters
     *
     * @return string[]
     */
    public function getParameters(): array;

    /**
     * Returns the priority of this route
     *
     * Deeper nested paths have a higher priority. Fixed paths have precedence over paths with parameter expressions.
     */
    public function getPriority(): int;

    /**
     * Process the route
     *
     * @param array $parameters
     *
     * @return ResponseInterface|mixed
     */
    public function process(RestRequestInterface $request, ...$parameters);
}
