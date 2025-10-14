<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Exception;

trait ClassBuilderTrait
{
    /**
     * Dynamically creates a class
     *
     * @param string|array $className
     *
     * @throws Exception
     */
    public static function buildClass(
        string $className,
        string $namespace = '',
        string $extends = '',
        bool $silent = false,
    ) {
        ClassBuilder::buildClass($className, $namespace, $extends, $silent);
    }

    /**
     * Dynamically creates a class
     *
     * @throws Exception
     */
    public static function buildClassIfNotExists(string $className, string $namespace = '', string $extends = '')
    {
        ClassBuilder::buildClassIfNotExists($className, $namespace, $extends);
    }

    /**
     * Dynamically creates an interface
     *
     * @throws Exception
     */
    public static function buildInterface(string $interfaceName, string $namespace = '', string $extends = '')
    {
        ClassBuilder::buildInterface($interfaceName, $namespace, $extends);
    }

    /**
     * Dynamically creates an interface
     *
     * @throws Exception
     */
    public static function buildInterfaceIfNotExists($interfaceName, string $namespace = '', string $extends = ''): void
    {
        ClassBuilder::buildInterfaceIfNotExists($interfaceName, $namespace, $extends);
    }
}
