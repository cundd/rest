<?php

declare(strict_types=1);

namespace Cundd\Rest\Handler;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for handlers of API requests
 */
interface CrudHandlerInterface extends HandlerInterface
{
    /**
     * Return the given property of the requested Model
     *
     * @param non-empty-string $propertyKey
     */
    public function getProperty(
        RestRequestInterface $request,
        string $identifier,
        string $propertyKey,
    ): mixed;

    /**
     * Return the data of the current Model
     *
     * @return array<mixed>|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function show(
        RestRequestInterface $request,
        string $identifier,
    ): array|int|ResponseInterface;

    /**
     * Replace the requested Model with the data from the request
     *
     * @return array<mixed>|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function update(
        RestRequestInterface $request,
        string $identifier,
    ): array|int|ResponseInterface;

    /**
     * Delete the requested Model
     *
     * @return int|ResponseInterface Returns 200 an success
     */
    public function delete(
        RestRequestInterface $request,
        string $identifier,
    ): int|ResponseInterface;

    /**
     * Create a new Model with the data from the request
     *
     * @return array<mixed>|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function create(RestRequestInterface $request): array|int|ResponseInterface;

    /**
     * List all Models
     *
     * @return array<mixed> Returns all Models
     */
    public function listAll(RestRequestInterface $request): iterable;

    /**
     * Count all Models
     *
     * @return int Returns the number of models
     */
    public function countAll(RestRequestInterface $request): int;
}
