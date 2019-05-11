<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests;


use Exception;
use InvalidArgumentException;

trait ClassBuilderTrait
{
    /**
     * Dynamically creates a class
     *
     * @param string|array $className
     * @param string       $namespace
     * @param string       $extends
     * @param bool         $silent
     * @throws Exception
     */
    public static function buildClass($className, string $namespace = '', string $extends = '', bool $silent = false)
    {
        list($preparedClassName, $preparedNamespace, $preparedExtends) = self::buildClassSignature(
            $className,
            $namespace,
            $extends
        );

        if (class_exists("$preparedNamespace\\$preparedClassName")) {
            if (!$silent) {
                printf('Class %s already exists' . PHP_EOL, "$preparedNamespace\\$preparedClassName");
            }

            return;
        }

        static::buildCode('class', $preparedClassName, $preparedNamespace, $preparedExtends);

        if (!class_exists("$preparedNamespace\\$preparedClassName")) {
            throw new Exception(sprintf('Could not create class %s', "$preparedNamespace\\$preparedClassName"));
        }
    }

    /**
     * Dynamically creates a class
     *
     * @param string $className
     * @param string $namespace
     * @param string $extends
     * @throws Exception
     */
    public static function buildClassIfNotExists($className, $namespace = '', $extends = '')
    {
        list($preparedClassName, $preparedNamespace, $preparedExtends) = self::buildClassSignature(
            $className,
            $namespace,
            $extends
        );

        if (!class_exists("$preparedNamespace\\$preparedClassName")) {
            static::buildClass($preparedClassName, $preparedNamespace, $preparedExtends);
        }
    }

    /**
     * Dynamically creates an interface
     *
     * @param string $interfaceName
     * @param string $namespace
     * @param string $extends
     * @throws Exception
     */
    public static function buildInterface($interfaceName, $namespace = '', $extends = '')
    {
        list($preparedClassName, $preparedNamespace, $preparedExtends) = self::buildClassSignature(
            $interfaceName,
            $namespace,
            $extends
        );

        if (interface_exists("$preparedNamespace\\$preparedClassName")) {
            printf('Interface %s already exists' . PHP_EOL, "$preparedNamespace\\$preparedClassName");

            return;
        }

        static::buildCode('interface', $preparedClassName, $preparedNamespace, $preparedExtends);

        if (!interface_exists("$preparedNamespace\\$preparedClassName")) {
            throw new Exception(sprintf('Could not create interface %s', "$preparedNamespace\\$preparedClassName"));
        }
    }

    /**
     * Dynamically creates an interface
     *
     * @param string $className
     * @param string $namespace
     * @param string $extends
     * @throws Exception
     */
    public static function buildInterfaceIfNotExists($className, $namespace = '', $extends = '')
    {
        list($preparedClassName, $preparedNamespace, $preparedExtends) = self::buildClassSignature(
            $className,
            $namespace,
            $extends
        );

        if (!interface_exists("$preparedNamespace\\$preparedClassName")) {
            static::buildInterface($preparedClassName, $preparedNamespace, $preparedExtends);
        }
    }

    /**
     * Dynamically create the class or interface
     *
     * @param string $type
     * @param string $preparedClassName
     * @param string $preparedNamespace
     * @param string $preparedExtends
     * @throws Exception
     */
    protected static function buildCode($type, $preparedClassName, $preparedNamespace, $preparedExtends)
    {
        $code = [];
        if ($preparedNamespace) {
            $code[] = "namespace $preparedNamespace;";
        }
        $code[] = "$type $preparedClassName";
        if ($preparedExtends) {
            $code[] = "extends \\$preparedExtends";
        }
        $code[] = '{}';

//        echo PHP_EOL . '------------------------------------------------------------------' . PHP_EOL;
//        echo(implode(' ', $code));
//        echo PHP_EOL . '------------------------------------------------------------------' . PHP_EOL;

        eval(implode(' ', $code));
    }

    /**
     * @param string|array $className
     * @param string       $namespace
     * @param string       $extends
     * @return string[]
     */
    protected static function buildClassSignature($className, $namespace = '', $extends = '')
    {
        if (is_array($className)) {
            return static::buildClassSignature(...$className);
        }

        if (!is_string($className)) {
            throw new InvalidArgumentException('$className must be a string');
        }
        if (!is_string($namespace)) {
            throw new InvalidArgumentException('$namespace must be a string');
        }
        if (!is_string($extends)) {
            throw new InvalidArgumentException('$extends must be a string');
        }

        $preparedClassName = $className;
        $preparedNamespace = $namespace;
        $preparedExtends = $extends;


        if (false !== strpos($className, '\\')) {
            $lastSlashPos = strrpos($className, '\\');
            $preparedNamespace = substr($className, 0, $lastSlashPos);
            $preparedClassName = substr($className, $lastSlashPos + 1);

            // If called like `createClass('\Vendor\Namespace\MyClass', 'ExtendMe')`
            if ($extends === '') {
                $preparedExtends = $namespace;
            }
        }

        return [$preparedClassName, trim($preparedNamespace, '\\'), trim($preparedExtends, '\\')];
    }
}
