<?php
declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class IdentityProvider implements IdentityProviderInterface
{
    /**
     * The reflection service
     *
     * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
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

    public function getIdentityProperty($modelClass)
    {
        if (!is_string($modelClass)) {
            throw new \InvalidArgumentException('Expected argument "modelClass" to be of type string');
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
        } catch (\Exception $exception) {
            return [null, null];
        }
    }
}
