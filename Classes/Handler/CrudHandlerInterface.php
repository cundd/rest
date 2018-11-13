<?php

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
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @param string               $propertyKey
     * @return mixed
     */
    public function getProperty(RestRequestInterface $request, $identifier, $propertyKey);

    /**
     * Return the data of the current Model
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function show(RestRequestInterface $request, $identifier);

    /**
     * Replace the requested Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function replace(RestRequestInterface $request, $identifier);

    /**
     * Update the requested Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function update(RestRequestInterface $request, $identifier);

    /**
     * Delete the requested Model
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @return int|ResponseInterface Returns 200 an success
     */
    public function delete(RestRequestInterface $request, $identifier);

    /**
     * Create a new Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function create(RestRequestInterface $request);

    /**
     * List all Models
     *
     * @param RestRequestInterface $request
     * @return array Returns all Models
     */
    public function listAll(RestRequestInterface $request);
}
