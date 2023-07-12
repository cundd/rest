<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class IdentityProvider implements IdentityProviderInterface
{
    protected ReflectionService $reflectionService;

    public function __construct(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * The default implementation returns only the failure state
     */
    public function getIdentityProperty(string $modelClass): array
    {
        return [null, null];
    }
}
