<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class IdentityProvider implements IdentityProviderInterface
{
    public function __construct(protected readonly ReflectionService $reflectionService)
    {
    }

    /**
     * The default implementation returns only the failure state
     */
    public function getIdentityProperty(string $modelClass): array
    {
        return [null, null];
    }
}
