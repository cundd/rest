<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication;

interface UserProviderInterface
{
    /**
     * Returns if the user with the given credentials is valid
     */
    public function checkCredentials(string $username, string $password): bool;
}
