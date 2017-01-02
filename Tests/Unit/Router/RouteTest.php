<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02.01.17
 * Time: 14:45
 */

namespace Cundd\Rest\Router;


use Cundd\Rest\Router\ParameterTypeInterface;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider routeShouldTrimSlashesDataProvider
     * @param string $inputPattern
     * @param string $outputPattern
     */
    public function routeShouldTrimSlashesTest($inputPattern, $outputPattern)
    {
        $this->assertEquals($outputPattern, (new Route($inputPattern))->getPattern());
    }

    /**
     * @return array
     */
    public function routeShouldTrimSlashesDataProvider()
    {
        return [
            ['/', ''],
            ['path/', 'path'],
            ['/path', 'path'],
            ['/path/', 'path'],
            ['/path/sub-path', 'path/sub-path'],
            ['/path/sub-path/', 'path/sub-path'],
            ['path/sub-path/', 'path/sub-path'],
            ['path/sub-path', 'path/sub-path'],
        ];
    }

    /**
     * @test
     */
    public function getDefaultMethodTest()
    {
        $this->assertEquals('GET', (new Route('/'))->getMethod());
    }

    /**
     * @test
     * @dataProvider getMethodTestDataProvider
     * @param string $method
     * @param string $expected
     */
    public function getMethodTest($method, $expected)
    {
        $this->assertEquals($expected, (new Route('/', $method))->getMethod());
    }

    public function getMethodTestDataProvider()
    {
        return [
            ['GET', 'GET'],
            ['POST', 'POST'],
            ['PUT', 'PUT'],
            ['PATCH', 'PATCH'],
            ['TRACE', 'TRACE'],
            ['OPTIONS', 'OPTIONS'],
            ['DELETE', 'DELETE'],
            ['get', 'GET'],
            ['Post', 'POST'],
            ['put', 'PUT'],
            ['Patch', 'PATCH'],
            ['trace', 'TRACE'],
            ['options', 'OPTIONS'],
            ['delete', 'DELETE'],
        ];
    }

    /**
     * @test
     * @dataProvider deeperPathsHaveHigherPriorityDataProvider
     * @param string $patternLowPriority
     * @param string $patternHighPriority
     */
    public function deeperPathsHaveHigherPriorityTest($patternLowPriority, $patternHighPriority)
    {
        $this->assertTrue(
            (new Route($patternLowPriority))->getPriority() < (new Route($patternHighPriority))->getPriority()
        );
    }

    public function deeperPathsHaveHigherPriorityDataProvider()
    {
        return [
            ['', 'path'],
            ['path', 'path/sub-path'],
            ['path/sub-path', 'path/sub-path/another'],
            ['path/sub-path/', 'path/sub-path/another/'],
            ['path/sub-path/another', 'path/sub-path/another/1'],
            ['path/sub-path/another/', 'path/sub-path/another/1/'],
            ['path/sub-path/another/1', 'path/sub-path/another/1/2/'],
            ['path/sub-path/another/1/2', 'path/sub-path/another/1/2/path'],
            ['path/sub-path/another/1/2/path', 'path/sub-path/another/1/2/path/item'],
            ['path/sub-path/another/1/2/path/item', 'path/sub-path/another/1/2/path/item/x'],
            ['path/sub-path/another/1/2/path/item/x', 'path/sub-path/another/1/2/path/item/x/y'],
        ];
    }

    /**
     * @test
     * @dataProvider patternsWithExpressionsHaveLowerPriorityDataProvider
     * @param string $patternLowPriority
     * @param string $patternHighPriority
     */
    public function patternsWithExpressionsHaveLowerPriorityTest($patternLowPriority, $patternHighPriority)
    {
        $this->assertTrue(
            (new Route($patternLowPriority))->getPriority() < (new Route($patternHighPriority))->getPriority()
        );
    }

    public function patternsWithExpressionsHaveLowerPriorityDataProvider()
    {
        return [
            ['path/{string}', 'path/sub-path'],
            ['path/{string}', 'path/sub-path/'],
            ['path/{int}/another', 'path/sub-path/another'],
            ['path/{float}/another/', 'path/sub-path/another/'],
            ['path/{string}/{float}/1', 'path/sub-path/another/1'],
            ['path/sub-path/{string}/1/2/path/item/x', 'path/sub-path/another/1/2/path/item/x'],
            ['path/sub-path/{string}/1/2/path/item/x/y', 'path/sub-path/another/1/2/path/item/x/y'],

            ['path/sub-path/{string}/{int}/2/path/item/x', 'path/sub-path/{string}/1/2/path/item/x'],
            ['path/sub-path/{string}/{int}/2/path/item/x/y', 'path/sub-path/{string}/1/2/path/item/x/y'],
        ];
    }

    /**
     * @test
     * @param string $pattern
     * @param array  $expectedParameters
     * @dataProvider getParametersDataProvider
     */
    public function getParametersTest($pattern, array $expectedParameters)
    {
        $this->assertEquals($expectedParameters, array_values((new Route($pattern))->getParameters()));
    }

    public function getParametersDataProvider()
    {
        return [
            [
                'path/{slug}',
                [ParameterTypeInterface::SLUG],
            ],
            [
                'path/{string}',
                [ParameterTypeInterface::SLUG],
            ],
            [
                'path/{int}/another',
                [ParameterTypeInterface::INTEGER],
            ],
            [
                'path/{integer}/another',
                [ParameterTypeInterface::INTEGER],
            ],
            [
                'path/{float}/another/',
                [ParameterTypeInterface::FLOAT],
            ],
            [
                'path/{string}/{float}/1',
                [ParameterTypeInterface::SLUG, ParameterTypeInterface::FLOAT],
            ],
            [
                'path/sub-path/{string}/1/2/path/item/x',
                [ParameterTypeInterface::SLUG],
            ],
            [
                'path/sub-path/{bool}/1/2/path/item/x/y',
                [ParameterTypeInterface::BOOLEAN],
            ],
            [
                'path/sub-path/{boolean}/1/2/path/item/x/y',
                [ParameterTypeInterface::BOOLEAN],
            ],

            [
                'path/sub-path/{string}/{int}/2/path/item/x',
                [ParameterTypeInterface::SLUG, ParameterTypeInterface::INTEGER],
            ],
            [
                'path/sub-path/{string}/{int}/2/path/item/x/y',
                [ParameterTypeInterface::SLUG, ParameterTypeInterface::INTEGER],
            ],
        ];
    }
}
