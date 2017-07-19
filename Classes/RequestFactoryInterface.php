<?php

namespace Cundd\Rest;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Factory class to get the current Request
 */
interface RequestFactoryInterface
{
    /**
     * Returns the request
     *
     * @return RestRequestInterface
     */
    public function getRequest();

    /**
     * Resets the current request
     *
     * @return $this
     */
    public function resetRequest();

    /**
     * Register/overwrite the current request
     *
     * @param ServerRequestInterface $request
     * @return $this
     */
    public function registerCurrentRequest($request);
}
