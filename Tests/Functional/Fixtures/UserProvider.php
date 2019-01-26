<?php
declare(strict_types=1);


namespace Cundd\Rest\Tests\Functional\Fixtures;


use Cundd\Rest\Authentication\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function checkCredentials(string $username, string $password): bool
    {
        return $username === $this->getApiUser() && $password === $this->getApiKey();
    }

    /**
     * Correct user for the API
     *
     * @return string
     */
    public static function getApiUser()
    {
        return 'daniel';
    }

    /**
     * Correct API key
     *
     * @return string
     */
    public static function getApiKey()
    {
        return 'api-key';
    }
}
