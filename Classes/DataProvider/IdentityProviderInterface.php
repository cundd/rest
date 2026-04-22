<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

interface IdentityProviderInterface
{
    /**
     * Return the Model's identity property-name and -type
     *
     * @return array{0:non-empty-string,1:'string'|'boolean'|'integer'|'float'}|array{0:null,1:null} Return property name and type on success, `[null, null]` otherwise
     */
    public function getIdentityProperty(string $modelClass): array;
}
