<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Factory class to get the current Request
 */
interface RequestFactoryInterface
{
    /**
     * Build the prepared REST Request for the given Server Request
     *
     * @param ServerRequestInterface $request
     * @return RestRequestInterface
     */
    public function buildRequest(ServerRequestInterface $request): RestRequestInterface;

    /**
     * Returns the request
     *
     * @return RestRequestInterface
     * @deprecated use buildRequest() instead. Will be removed in 6.0
     */
    public function getRequest();

    /**
     * Resets the current request
     *
     * @return $this
     * @deprecated use buildRequest() instead. Will be removed in 6.0
     */
    public function resetRequest();

    /**
     * Register/overwrite the current request
     *
     * @param ServerRequestInterface $request
     * @return $this
     * @deprecated use buildRequest() instead. Will be removed in 6.0
     */
    public function registerCurrentRequest($request);
}
