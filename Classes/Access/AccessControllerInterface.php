<?php

namespace Cundd\Rest\Access;

use Cundd\Rest\Http\RestRequestInterface;

interface AccessControllerInterface
{
    /**
     * Access identifier to signal if the current request is allowed
     */
    const ACCESS = 'ACCESS-CONST';

    /**
     * Access identifier to signal allowed requests
     */
    const ACCESS_ALLOW = 'allow';

    /**
     * Access identifier to signal denied requests
     */
    const ACCESS_DENY = 'deny';

    /**
     * Access identifier to signal requests that require a valid login
     */
    const ACCESS_REQUIRE_LOGIN = 'require';

    /**
     * Access identifier to signal a missing login
     */
    const ACCESS_UNAUTHORIZED = 'unauthorized';

    /**
     * Returns if the current request's client has access to the requested resource
     *
     * @param RestRequestInterface $request
     * @return string Returns one of the constants AccessControllerInterface::ACCESS
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
