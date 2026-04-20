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
     * Return the normalized path pattern
     */
    public function getPattern(): string;

    /**
     * Return the request method for this route
     *
     * @return non-empty-string
     */
    public function getMethod(): string;

    /**
     * Return the requested parameters
     *
     * @return string[]
     */
    public function getParameters(): array;

    /**
     * Return the priority of this route
     *
     * Deeper nested paths have a higher priority. Fixed paths have precedence
     * over paths with parameter expressions.
     */
    public function getPriority(): int;

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
    ): mixed;
}
