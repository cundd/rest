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
     * @param UserProviderInterface $userProvider Provider that will check the user credentials
     */
    public function __construct(private readonly UserProviderInterface $userProvider)
    {
    }

    /**
     * Tries to authenticate the current request
     *
     * @return bool Returns if the authentication was successful
     */
    public function authenticate(RestRequestInterface $request): bool
    {
        $username = null;
        $password = null;

        // TODO: Rate limit failed attempts
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

    /**
     * @return array{0:string,1:string}
     */
    private function checkServerData(string $key): array
    {
        if (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
            if (0 === strpos(strtolower($value), 'basic')) {
                $parts = explode(':', base64_decode(substr($value, 6)), 2);
                if (2 === count($parts)) {
                    return $parts;
                }
            }
        }

        return ['', ''];
    }
}
