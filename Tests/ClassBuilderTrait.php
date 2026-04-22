<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests;

use Exception;

trait ClassBuilderTrait
{
    /**
     * Dynamically creates a class
     *
     * @throws Exception
     */
    public static function buildClass(
        string $className,
        string $namespace = '',
        string $extends = '',
        bool $silent = false,
    ): void {
        ClassBuilder::buildClass($className, $namespace, $extends, $silent);
    }

    /**
     * Dynamically creates a class
     *
     * @throws Exception
     */
    public static function buildClassIfNotExists(
        string $className,
        string $namespace = '',
        string $extends = '',
    ): void {
        ClassBuilder::buildClassIfNotExists($className, $namespace, $extends);
    }

    /**
     * Dynamically creates an interface
     *
     * @throws Exception
     */
    public static function buildInterface(
        string $interfaceName,
        string $namespace = '',
        string $extends = '',
    ): void {
        ClassBuilder::buildInterface($interfaceName, $namespace, $extends);
    }

    /**
     * Dynamically creates an interface
     *
     * @throws Exception
     */
    public static function buildInterfaceIfNotExists(
        string $interfaceName,
        string $namespace = '',
        string $extends = '',
    ): void {
        ClassBuilder::buildInterfaceIfNotExists($interfaceName, $namespace, $extends);
    }
}
