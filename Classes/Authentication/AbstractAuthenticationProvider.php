<?php

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Http\RestRequestInterface;

abstract class AbstractAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * Tries to authenticate the current request
     *
     * @param RestRequestInterface $request
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request)
    {
        return false;
    }
}
