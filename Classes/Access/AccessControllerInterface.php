<?php

namespace Cundd\Rest\Access;

use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Http\RestRequestInterface;

interface AccessControllerInterface
{
    /**
     * Returns if the current request's client has access to the requested resource
     *
     * @param RestRequestInterface $request
     * @return Access
     */
    public function getAccess(RestRequestInterface $request);

    /**
     * Returns if the given request needs authentication
     *
     * @param RestRequestInterface $request
     * @return bool
     */
    public function requestNeedsAuthentication(RestRequestInterface $request);

}
