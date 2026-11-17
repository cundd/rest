<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use SensitiveParameter;

interface UserProviderInterface
{
    /**
     * Returns if the user with the given credentials is valid
     */
    public function checkCredentials(
        ConfigurationProviderInterface $configurationProvider,
        string $username,
        #[SensitiveParameter]
        string $password,
    ): bool;
}
