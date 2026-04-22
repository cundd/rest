<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Router;

use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\Router\ParameterTypeInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Tests\RequestBuilderTrait;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    use RequestBuilderTrait;

    /**
     * @var callable
     */
    private $cb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cb = function () {
            return 'hello';
        };
    }

    #[Test]
    public function processTest(): void
    {
        $this->assertEquals(
            'hello',
            Route::routeWithPattern('a/route', $this->cb)
                ->process($this->buildTestRequest('/a/route'))
        );
    }

    /**
     * @param non-empty-string $inputPattern
     */
    #[Test]
    #[DataProvider('routeShouldTrimSlashesDataProvider')]
    public function routeShouldStartWithSlashTest(
        string $inputPattern,
        string $outputPattern,
    ): void {
        $this->assertEquals(
            $outputPattern,
            Route::routeWithPattern($inputPattern, $this->cb)->getPattern()
        );
    }

    /**
     * @return array<int,array<int,string>>
     */
    public static function routeShouldTrimSlashesDataProvider(): array
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

    #[Test]
    public function routeShouldAcceptResourceTypeTest(): void
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

    #[Test]
    public function getDefaultMethodTest(): void
    {
        $this->assertEquals('GET', Route::routeWithPattern('/', $this->cb)->getMethod());
    }

    /**
     * @param non-empty-string $method
     */
    #[Test]
    #[DataProvider('getMethodTestDataProvider')]
    public function getMethodTest(string $method, string $expected): void
    {
        $route = new Route('/', $method, $this->cb);

        $this->assertEquals($expected, $route->getMethod());
    }

    /**
     * @return list<array{0:string,1:string}>
     */
    public static function getMethodTestDataProvider(): array
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
     * @param non-empty-string $patternLowPriority
     * @param non-empty-string $patternHighPriority
     */
    #[Test]
    #[DataProvider('deeperPathsHaveHigherPriorityDataProvider')]
    public function deeperPathsHaveHigherPriorityTest(
        string $patternLowPriority,
        string $patternHighPriority,
    ): void {
        $this->assertTrue(
            Route::routeWithPattern($patternLowPriority, $this->cb)->getPriority()
            < Route::routeWithPattern($patternHighPriority, $this->cb)->getPriority()
        );
    }

    /**
     * @return list<array{0:string,1:string}>
     */
    public static function deeperPathsHaveHigherPriorityDataProvider(): array
    {
        return [
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
     * @param non-empty-string $patternLowPriority
     * @param non-empty-string $patternHighPriority
     */
    #[Test]
    #[DataProvider('patternsWithExpressionsHaveLowerPriorityDataProvider')]
    public function patternsWithExpressionsHaveLowerPriorityTest(
        string $patternLowPriority,
        string $patternHighPriority,
    ): void {
        $this->assertTrue(
            Route::routeWithPattern($patternLowPriority, $this->cb)->getPriority()
            < Route::routeWithPattern($patternHighPriority, $this->cb)->getPriority()
        );
    }

    /**
     * @return list<array{0:string,1:string}>
     */
    public static function patternsWithExpressionsHaveLowerPriorityDataProvider(): array
    {
        return [
            ['path/{string}', 'path/sub-path'],
            ['path/{string}', 'path/sub-path/'],
            ['path/{int}/another', 'path/sub-path/another'],
            ['path/{float}/another/', 'path/sub-path/another/'],
            ['path/{string}/{float}/1', 'path/sub-path/another/1'],
            ['path/sub-path/{string}/1/2/path/item/x', 'path/sub-path/another/1/2/path/item/x'],
            ['path/sub-path/{string}/1/2/path/item/x/y', 'path/sub-path/another/1/2/path/item/x/y'],
            ['path/sub-path/{raw}/1/2/path/item/x/y', 'path/sub-path/another/1/2/path/item/x/y'],

            ['path/sub-path/{string}/{int}/2/path/item/x', 'path/sub-path/{string}/1/2/path/item/x'],
            ['path/sub-path/{string}/{int}/2/path/item/x/y', 'path/sub-path/{string}/1/2/path/item/x/y'],

            ['path/sub-path/{raw}/{int}/2/path/item/x', 'path/sub-path/{string}/1/2/path/item/x'],
            ['path/sub-path/{raw}/{int}/2/path/item/x/y', 'path/sub-path/{string}/1/2/path/item/x/y'],
        ];
    }

    /**
     * @param non-empty-string $inputPattern
     * @param non-empty-string $expectedPattern
     */
    #[Test]
    #[DataProvider('getNormalizedPatternDataProvider')]
    public function getNormalizedPatternTest(string $inputPattern, string $expectedPattern): void
    {
        $this->assertEquals($expectedPattern, Route::routeWithPattern($inputPattern, $this->cb)->getPattern());
    }

    /**
     * @return list<array{0:string,1:string}>
     */
    public static function getNormalizedPatternDataProvider(): array
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
     * @param non-empty-string            $pattern
     * @param array<int,non-empty-string> $expectedParameters
     */
    #[Test]
    #[DataProvider('getParametersDataProvider')]
    public function getParametersTest(
        string $pattern,
        array $expectedParameters,
    ): void {
        $this->assertEquals(
            $expectedParameters,
            array_values(Route::routeWithPattern($pattern, $this->cb)->getParameters())
        );
    }

    /**
     * @return list<list<mixed>>
     */
    public static function getParametersDataProvider(): array
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

            [
                'path/sub-path/{string}/{raw}/2/path/item/x',
                [ParameterTypeInterface::SLUG, ParameterTypeInterface::RAW],
            ],
            [
                'path/sub-path/{string}/{raw}/2/path/item/x/y',
                [ParameterTypeInterface::SLUG, ParameterTypeInterface::RAW],
            ],
        ];
    }

    /**
     * @param non-empty-string $pattern
     */
    #[Test]
    #[DataProvider('shouldThrowForInvalidParametersDataProvider')]
    public function shouldThrowForInvalidParametersTest(string $pattern): void
    {
        $this->expectException(LogicException::class);
        Route::routeWithPattern($pattern, $this->cb);
    }

    /**
     * @return list<list<string>>
     */
    public static function shouldThrowForInvalidParametersDataProvider(): array
    {
        return [
            ['{}'],
            ['{bool'],
            ['bool}'],
            ['{b00l}'],
        ];
    }

    #[Test]
    public function factoryMethodsTest(): void
    {
        $this->assertEquals('GET', Route::get('path/sub-path', $this->cb)->getMethod());
        $this->assertEquals('POST', Route::post('path/sub-path', $this->cb)->getMethod());
        $this->assertEquals('PUT', Route::put('path/sub-path', $this->cb)->getMethod());
        $this->assertEquals('DELETE', Route::delete('path/sub-path', $this->cb)->getMethod());
        $this->assertEquals('OPTIONS', Route::options('path/sub-path', $this->cb)->getMethod());
        $this->assertEquals('PATCH', Route::patch('path/sub-path', $this->cb)->getMethod());
    }
}
