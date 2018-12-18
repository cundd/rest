<?php
declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

interface IdentityProviderInterface
{
    /**
     * Return the Model's identity property-name and -type
     *
     * @param string $modelClass
     * @return array Returns `[string $name, string $type]` on success, `[null, null]` otherwise
     */
    public function getIdentityProperty($modelClass);
}
