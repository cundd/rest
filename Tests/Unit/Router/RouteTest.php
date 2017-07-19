<?php

namespace Cundd\Rest\Tests\Unit\Router;


use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Router\ParameterTypeInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Tests\RequestBuilderTrait;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    use RequestBuilderTrait;

    /**
     * @var callable
     */
    private $cb;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cb = function () {
            return 'hello';
        };
    }

    /**
     * @test
     */
    public function processTest()
    {
        $this->assertEquals(
            'hello',
            Route::routeWithPattern('a/route', $this->cb)->process($this->buildTestRequest('/a/route'))
        );
    }

    /**
     * @test
     * @dataProvider routeShouldTrimSlashesDataProvider
     * @param string $inputPattern
     * @param string $outputPattern
     */
    public function routeShouldStartWithSlashTest($inputPattern, $outputPattern)
    {
        $this->assertEquals($outputPattern, Route::routeWithPattern($inputPattern, $this->cb)->getPattern());
    }

    /**
     * @return array
     */
    public function routeShouldTrimSlashesDataProvider()
    {
        return [
            ['/', '/'],
            ['path/', '/path/'],
            ['/path', '/path'],
            ['/path/', '/path/'],
            ['/path/sub-path', '/path/sub-path'],
            ['/path/sub-path/', '/path/sub-path/'],
            ['path/sub-path/', '/path/sub-path/'],
            ['path/sub-path', '/path/sub-path'],
        ];
    }

    /**
     * @test
     */
    public function routeShouldAcceptResourceTypeTest()
    {
        $this->assertEquals(
            '/path',
            Route::routeWithPattern(new ResourceType('path'), $this->cb)->getPattern()
        );
        $this->assertEquals(
            '/path',
            Route::routeWithPatternAndMethod(new ResourceType('path'), 'GET', $this->cb)->getPattern()
        );
        $route = new Route(new ResourceType('path'), 'GET', $this->cb);
        $this->assertEquals(
            '/path',
            $route->getPattern()
        );
    }

    /**
     * @test
     */
    public function getDefaultMethodTest()
    {
        $this->assertEquals('GET', Route::routeWithPattern('/', $this->cb)->getMethod());
    }

    /**
     * @test
     * @dataProvider getMethodTestDataProvider
     * @param string $method
     * @param string $expected
     */
    public function getMethodTest($method, $expected)
    {
        $route = new Route('/', $method, $this->cb);

        $this->assertEquals($expected, $route->getMethod());
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
            Route::routeWithPattern($patternLowPriority, $this->cb)->getPriority()
            < Route::routeWithPattern($patternHighPriority, $this->cb)->getPriority()
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
            Route::routeWithPattern($patternLowPriority, $this->cb)->getPriority()
            < Route::routeWithPattern($patternHighPriority, $this->cb)->getPriority()
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
     * @param string $inputPattern
     * @param string $expectedPattern
     * @dataProvider getNormalizedPatternDataProvider
     */
    public function getNormalizedPatternTest($inputPattern, $expectedPattern)
    {
        $this->assertEquals($expectedPattern, Route::routeWithPattern($inputPattern, $this->cb)->getPattern());
    }

    public function getNormalizedPatternDataProvider()
    {
        return [
            ['path/{string}', '/path/{slug}'],
            ['path/{string}/', '/path/{slug}/'],
            ['path/{int}/another', '/path/{integer}/another'],
            ['path/{float}/another/', '/path/{float}/another/'],
            ['path/{string}/{float}/1', '/path/{slug}/{float}/1'],
            ['path/sub-path/{string}/1/2/path/item/x', '/path/sub-path/{slug}/1/2/path/item/x'],
            ['path/sub-path/{string}/1/2/path/item/x/y', '/path/sub-path/{slug}/1/2/path/item/x/y'],

            ['path/sub-path/{string}/{int}/2/path/item/x', '/path/sub-path/{slug}/{integer}/2/path/item/x'],
            ['path/sub-path/{string}/{int}/2/path/item/x/y', '/path/sub-path/{slug}/{integer}/2/path/item/x/y'],
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
        $this->assertEquals(
            $expectedParameters,
            array_values(Route::routeWithPattern($pattern, $this->cb)->getParameters())
        );
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
                'path/{string}.json',
                [ParameterTypeInterface::SLUG],
            ],
            [
                'path/{int}/another',
                [ParameterTypeInterface::INTEGER],
            ],
            [
                'path/{int}/another.json',
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

    /**
     * @test
     * @param string $pattern
     * @dataProvider shouldThrowForInvalidParametersDataProvider
     * @expectedException \LogicException
     */
    public function shouldThrowForInvalidParametersTest($pattern)
    {
        Route::routeWithPattern($pattern, $this->cb);
    }

    public function shouldThrowForInvalidParametersDataProvider()
    {
        return [
            ['{}'],
            ['{bool'],
            ['bool}'],
            ['{b00l}'],
        ];
    }

    /**
     * @test
     */
    public function factoryMethodsTest()
    {
        $this->assertEquals('GET', Route::get('path/sub-path', $this->cb)->getMethod());
        $this->assertEquals('POST', Route::post('path/sub-path', $this->cb)->getMethod());
        $this->assertEquals('PUT', Route::put('path/sub-path', $this->cb)->getMethod());
        $this->assertEquals('DELETE', Route::delete('path/sub-path', $this->cb)->getMethod());
    }
}
