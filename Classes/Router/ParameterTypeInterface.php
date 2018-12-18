<?php
declare(strict_types=1);

namespace Cundd\Rest\Router;

interface ParameterTypeInterface
{
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const SLUG = 'slug';
    const BOOLEAN = 'boolean';
}
