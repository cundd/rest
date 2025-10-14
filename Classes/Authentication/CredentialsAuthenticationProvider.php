<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Handler\AuthHandler;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\SessionManager;

/**
 * Authentication Provider for requests authenticated through the login route (/auth/login)
 */
class CredentialsAuthenticationProvider extends AbstractAuthenticationProvider
{
    protected SessionManager $sessionManager;

    /**
     * Credentials Authentication Provider constructor
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Tries to authenticate the current request
     *
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request): bool
    {
        return AuthHandler::STATUS_LOGGED_IN === $this->sessionManager->valueForKey('loginStatus');
    }
}
