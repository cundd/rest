<?php
declare(strict_types=1);

namespace Cundd\Rest\Router;

interface ParameterTypeInterface
{
    public const INTEGER = 'integer';
    public const FLOAT = 'float';
    public const SLUG = 'slug';
    public const BOOLEAN = 'boolean';
    public const RAW = 'raw';
}
