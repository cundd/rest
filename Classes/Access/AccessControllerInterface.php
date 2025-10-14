<?php

declare(strict_types=1);

namespace Cundd\Rest\Access;

use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Http\RestRequestInterface;

interface AccessControllerInterface
{
    /**
     * Returns if the current request's client has access to the requested resource
     */
    public function getAccess(RestRequestInterface $request): Access;

    /**
     * Returns if the given request needs authentication
     */
    public function requestNeedsAuthentication(RestRequestInterface $request): bool;
}
