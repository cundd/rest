<?php

namespace Cundd\Rest\Tests\Functional;

use PHPUnit\Framework\Assert as PHPUnitAssert;

trait AssertTrait
{
    public static function assertInternalType($expected, $actual, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    private static function forward(string $method, array $arguments)
    {
        call_user_func_array([PHPUnitAssert::class, $method], $arguments);
    }

    public static function assertEquals(
        $expected,
        $actual,
        $message = '',
        $delta = 0.0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertSame($expected, $actual, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertEmpty($actual, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function markTestSkipped($message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function markTestIncomplete($message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertInstanceOf($expected, $actual, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertArrayHasKey($key, $array, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertCount($expectedCount, $haystack, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertNotEquals(
        $expected,
        $actual,
        $message = '',
        $delta = 0.0,
        $maxDepth = 10,
        $canonicalize = false,
        $ignoreCase = false
    ) {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertNull($actual, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertFalse($condition, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertTrue($condition, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }

    public static function assertNotEmpty($actual, $message = '')
    {
        static::forward(__FUNCTION__, func_get_args());
    }
}
