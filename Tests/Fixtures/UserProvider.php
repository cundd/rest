<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function checkCredentials(
        ConfigurationProviderInterface $configurationProvider,
        string $username,
        string $password,
    ): bool {
        return $username === $this->getApiUser()
            && $password === $this->getApiKey();
    }

    /**
     * Correct user for the API
     */
    public static function getApiUser(): string
    {
        return 'daniel';
    }

    /**
     * Correct API key
     */
    public static function getApiKey(): string
    {
        return 'api-key';
    }
}
