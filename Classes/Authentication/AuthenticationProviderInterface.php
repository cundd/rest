<?php

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Http\RestRequestInterface;

interface AuthenticationProviderInterface
{
    /**
     * Tries to authenticate the current request
     *
     * @param RestRequestInterface $request
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request);
}
