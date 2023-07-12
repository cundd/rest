<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Http\RestRequestInterface;

/**
 * Authentication Provider for login data sent through Basic Auth
 */
class BasicAuthenticationProvider extends AbstractAuthenticationProvider
{
    /**
     * Provider that will check the user credentials
     */
    protected UserProviderInterface $userProvider;

    /**
     * BasicAuth Provider constructor
     *
     * @param UserProviderInterface $userProvider
     */
    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * Tries to authenticate the current request
     *
     * @param RestRequestInterface $request
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request): bool
    {
        $username = null;
        $password = null;

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
        } elseif ($tuple = $this->checkServerData('HTTP_AUTHENTICATION')) {
            [$username, $password] = $tuple;
        } elseif ($tuple = $this->checkServerData('HTTP_AUTHORIZATION')) {
            [$username, $password] = $tuple;
        } elseif ($tuple = $this->checkServerData('REDIRECT_HTTP_AUTHORIZATION')) {
            [$username, $password] = $tuple;
        }

        if (!is_string($username) || !is_string($password)) {
            return false;
        }

        return $this->userProvider->checkCredentials($username, $password);
    }

    private function checkServerData($key)
    {
        if (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
            if (strpos(strtolower($value), 'basic') === 0) {
                return explode(':', base64_decode(substr($value, 6)));
            }
        }

        return [];
    }
}
