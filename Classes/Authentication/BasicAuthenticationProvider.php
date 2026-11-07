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
        // TODO: Rate limit failed attempts
        $authorization = $request->getHeaderLine('authorization');
        [$username, $password] = $this->decodeBasicAuthHeader($authorization);

        if (!is_string($username) || !is_string($password)) {
            return false;
        }

        return $this->userProvider->checkCredentials($username, $password);
    }

    /**
     * @return array{0:string|null,1:string|null}
     */
    private function decodeBasicAuthHeader(string $value): array
    {
        if (0 === strpos(strtolower($value), 'basic')) {
            $decoded = base64_decode(substr($value, 6), true);
            if (false !== $decoded) {
                $parts = explode(':', $decoded, 2);
                if (2 === count($parts)) {
                    return $parts;
                }
            }
        }

        return [null, null];
    }
}
