<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Exception;
use InvalidArgumentException;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class IdentityProvider implements IdentityProviderInterface
{
    /**
     * The reflection service
     *
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * IdentityProvider constructor
     *
     * @param ReflectionService $reflectionService
     */
    public function __construct(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    public function getIdentityProperty(string $modelClass): array
    {
        if (!is_string($modelClass)) {
            throw new InvalidArgumentException('Expected argument "modelClass" to be of type string');
        }

        if ((new Typo3Version())->getMajorVersion() >= 10) {
            return [null, null];
        }

        // Fetch the first identity property and search the repository for it
        $type = null;
        $property = null;
        try {
            $classSchema = $this->reflectionService->getClassSchema($modelClass);
            $identityProperties = $classSchema->getIdentityProperties();

            $type = reset($identityProperties);
            $property = key($identityProperties);

            return [$property, $type];
        } catch (Exception $exception) {
            return [null, null];
        }
    }
}
