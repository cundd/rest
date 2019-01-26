<?php
declare(strict_types=1);

namespace Cundd\Rest\Authentication;

interface UserProviderInterface
{
    /**
     * Returns if the user with the given credentials is valid
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function checkCredentials(string $username, string $password): bool;
}
