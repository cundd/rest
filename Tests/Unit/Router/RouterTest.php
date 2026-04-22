<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Router;

use Closure;
use Cundd\Rest\Router\Exception\NotFoundException;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\Router;
use Cundd\Rest\Tests\Unit\AbstractRequestBasedCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

final class RouterTest extends AbstractRequestBasedCase
{
    protected Router $fixture;

    private Closure $cb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cb = Closure::bind(
            function () {
                return func_get_args();
            },
            new stdClass()
        );

        $this->fixture = new Router();
    }

    protected function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @param non-empty-string $pattern
     * @param non-empty-string $path
     * @param array<int,mixed> $expectedParameters
     */
    #[Test]
    #[DataProvider('getPreparedParametersDataProvider')]
    public function dispatchTest(
        string $pattern,
        string $path,
        array $expectedParameters,
        bool $noResult = false,
    ): void {
        $this->fixture->add(Route::get($pattern, $this->cb));

        $request = $this->buildTestRequest($path, 'GET');
        $result = $this->fixture->dispatch($request);

        if ($noResult) {
            $this->assertInstanceOf(NotFoundException::class, $result);
        } else {
            $this->assertFalse(is_object($result), 'No object as result expected');
            $this->assertSame($request, array_shift($result));
            $this->assertSame($expectedParameters, $result);
        }
    }

    /**
     * @param non-empty-string $pattern
     * @param non-empty-string $path
     * @param array<int,mixed> $expectedParameters
     */
    #[Test]
    #[DataProvider('getPreparedParametersDataProvider')]
    public function getPreparedParametersTest(
        string $pattern,
        string $path,
        array $expectedParameters,
    ): void {
        $this->fixture->add(Route::get($pattern, $this->cb));

        $prepareParameters = $this->fixture->getPreparedParameters($this->buildTestRequest($path, 'GET'));
        $this->assertSame($expectedParameters, $prepareParameters, "Failed with pattern '$pattern' for path '$path'");
    }

    #[Test]
    #[DataProvider('getMatchingRoutesMethodDataProvider')]
    public function dispatchNotFoundTest(): void
    {
        $this->fixture->add(Route::get('some/route', $this->cb));

        $request = $this->buildTestRequest('another/path', 'GET');
        $result = $this->fixture->dispatch($request);
        $this->assertInstanceOf(NotFoundException::class, $result);
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    public static function getPreparedParametersDataProvider(): array
    {
        return [
            ['{slug}/{float}/{bool}/{int}/?', '/slug/1.0/no/9', ['slug', 1.0, false, 9]],
            ['path/{slug}/{float}/{bool}/{int}/?', '/path/slug/1.0/no/9', ['slug', 1.0, false, 9]],
            ['path/{slug}/{float}/{bool}/{int}/?', '/path/slug/79.1/no/901', ['slug', 79.1, false, 901]],
            ['path/{slug}/sub-path/{float}/{bool}/{int}/?', '/path/slug/sub-path/1.0/no/9', ['slug', 1.0, false, 9]],
            ['path/{slug}/sub-path/{float}/{bool}/{int}/?', '/slug/sub-path/1.0/no/9', [], true],
            ['path/{slug}/sub-path/{float}/{bool}/{int}/?', '/', [], true],
            ['path/{slug}/sub-path/{float}/{bool}/{int}/?', '', [], true],
            [
                'path/{raw}/?',
                '/path/here could be änything but a slash',
                ['here%20could%20be%20änything%20but%20a%20slash'],
                false,
            ],
            [
                'path/{raw}/?',
                '/path/here could be änything but a slash/',
                ['here%20could%20be%20änything%20but%20a%20slash'],
                false,
            ],
            [
                'path/{raw}/{int}/?',
                '/path/here could be änything but a slash/1',
                ['here%20could%20be%20änything%20but%20a%20slash', 1],
                false,
            ],
            [
                'path/{raw}/?',
                '/path/Mr Müller/',
                ['Mr%20Müller'],
                false,
            ],
        ];
    }

    #[Test]
    #[DataProvider('getMatchingRoutesMethodDataProvider')]
    public function getMatchingRoutesSortedTest(string $method): void
    {
        $this->fixture->add(Route::get('path/{slug}', $this->cb));
        $this->fixture->add(Route::post('path/{slug}', $this->cb));
        $this->fixture->add(Route::delete('path/{slug}', $this->cb));
        $this->fixture->add(Route::put('path/{slug}', $this->cb));
        $this->fixture->add(Route::get('path/perfect-match', $this->cb));
        $this->fixture->add(Route::post('path/perfect-match', $this->cb));
        $this->fixture->add(Route::delete('path/perfect-match', $this->cb));
        $this->fixture->add(Route::put('path/perfect-match', $this->cb));

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/slug_segment-123.txt', $method)
        );
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{slug}', $matchingRoutes['/path/{slug}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/perfect-match', $method)
        );
        $this->assertCount(2, $matchingRoutes);
        $firstMatch = reset($matchingRoutes);
        $this->assertInstanceOf(Route::class, $firstMatch);
        $this->assertSame('/path/perfect-match', $firstMatch->getPattern());
    }

    #[Test]
    #[DataProvider('getMatchingRoutesMethodDataProvider')]
    public function dispatchMatchingRoutesSortedTest(string $method): void
    {
        $perfectMatchCallback = Closure::bind(
            function () {
                return 'matched the route path/perfect-match';
            },
            new stdClass()
        );
        $perfectMatchCountCallback = Closure::bind(
            function () {
                return 'matched the route path/perfect-match/_count';
            },
            new stdClass()
        );

        $this->fixture->add(Route::get('path/{slug}', $this->cb));
        $this->fixture->add(Route::post('path/{slug}', $this->cb));
        $this->fixture->add(Route::delete('path/{slug}', $this->cb));
        $this->fixture->add(Route::put('path/{slug}', $this->cb));
        $this->fixture->add(Route::get('path/perfect-match', $perfectMatchCallback));
        $this->fixture->add(Route::post('path/perfect-match', $perfectMatchCallback));
        $this->fixture->add(Route::delete('path/perfect-match', $perfectMatchCallback));
        $this->fixture->add(Route::put('path/perfect-match', $perfectMatchCallback));
        $this->fixture->add(Route::get('path/{slug}', $this->cb));
        $this->fixture->add(Route::post('path/{slug}', $this->cb));
        $this->fixture->add(Route::delete('path/{slug}', $this->cb));
        $this->fixture->add(Route::put('path/{slug}', $this->cb));

        $this->fixture->add(Route::get('path/perfect-match/_count', $perfectMatchCountCallback));
        $this->fixture->add(Route::post('path/perfect-match/_count', $perfectMatchCountCallback));
        $this->fixture->add(Route::delete('path/perfect-match/_count', $perfectMatchCountCallback));
        $this->fixture->add(Route::put('path/perfect-match/_count', $perfectMatchCountCallback));

        $this->assertSame(
            'matched the route path/perfect-match',
            $this->fixture->dispatch($this->buildTestRequest('/path/perfect-match', $method))
        );
        $this->assertSame(
            'matched the route path/perfect-match/_count',
            $this->fixture->dispatch($this->buildTestRequest('/path/perfect-match/_count', $method))
        );
    }

    /**
     * @test
     */
    #[Test]
    public function dispatchLikeDefaultHandlerTest(): void
    {
        $count = Closure::bind(
            function () {
                return 'returns the number of elements';
            },
            new stdClass()
        );
        $resourceType = 'cundd-resource-type';
        $this->fixture->add(Route::get($resourceType . '/?', $this->cb)); // listAll
        $this->fixture->add(Route::get($resourceType . '/_count/?', $count)); // countAll
        $this->fixture->add(Route::post($resourceType . '/?', $this->cb)); // create
        $this->fixture->add(Route::get($resourceType . '/{slug}/?', $this->cb)); // show
        $this->fixture->add(Route::put($resourceType . '/{slug}/?', $this->cb)); // replace
        $this->fixture->add(Route::post($resourceType . '/{slug}/?', $this->cb)); // replace
        $this->fixture->add(Route::delete($resourceType . '/{slug}/?', $this->cb)); // delete
        $this->fixture->add(
            Route::routeWithPatternAndMethod($resourceType . '/{slug}/?', 'PATCH', $this->cb)
        ); // replace
        $this->fixture->add(Route::get($resourceType . '/{slug}/{slug}/?', $this->cb)); // getProperty

        $this->assertSame(
            'returns the number of elements',
            $this->fixture->dispatch($this->buildTestRequest("/$resourceType/_count", 'GET'))
        );
    }

    #[Test]
    #[DataProvider('getMatchingRoutesMethodDataProvider')]
    public function getMatchingRoutesSlugTest(string $method): void
    {
        $this->fixture->add(Route::get('path/{slug}', $this->cb));
        $this->fixture->add(Route::post('path/{slug}', $this->cb));
        $this->fixture->add(Route::delete('path/{slug}', $this->cb));
        $this->fixture->add(Route::put('path/{slug}', $this->cb));
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/slug_segment-123.txt/deeper', $method))
        );
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes($this->buildTestRequest('/path', $method))
        );
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/something@domain', $method))
        );

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/slug_segment-123.txt', $method)
        );
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{slug}', $matchingRoutes['/path/{slug}']->getPattern());
    }

    #[Test]
    #[DataProvider('getMatchingRoutesMethodDataProvider')]
    public function getMatchingRoutesIntegerTest(string $method): void
    {
        $this->fixture->add(Route::get('path/{int}', $this->cb));
        $this->fixture->add(Route::post('path/{int}', $this->cb));
        $this->fixture->add(Route::delete('path/{int}', $this->cb));
        $this->fixture->add(Route::put('path/{int}', $this->cb));
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes(
                $this->buildTestRequest('/path/slug_segment-123.txt/deeper', $method)
            )
        );
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes(
                $this->buildTestRequest('/path/123/deeper', $method)
            )
        );
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes(
                $this->buildTestRequest('/path', $method)
            )
        );
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes(
                $this->buildTestRequest('/path/something@domain', $method)
            )
        );

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/123', $method)
        );
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{integer}', $matchingRoutes['/path/{integer}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/1', $method)
        );
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{integer}', $matchingRoutes['/path/{integer}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/0', $method)
        );
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{integer}', $matchingRoutes['/path/{integer}']->getPattern());
    }

    #[Test]
    #[DataProvider('getMatchingRoutesMethodDataProvider')]
    public function getMatchingRoutesFloatTest(string $method): void
    {
        $this->fixture->add(Route::get('path/{float}', $this->cb));
        $this->fixture->add(Route::post('path/{float}', $this->cb));
        $this->fixture->add(Route::delete('path/{float}', $this->cb));
        $this->fixture->add(Route::put('path/{float}', $this->cb));
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/slug_segment-123.txt/deeper', $method))
        );
        $this->assertEmpty($this->fixture->getMatchingRoutes($this->buildTestRequest('/path/123.1/deeper', $method)));
        $this->assertEmpty($this->fixture->getMatchingRoutes($this->buildTestRequest('/path', $method)));
        $this->assertEmpty(
            $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/something@domain', $method))
        );

        $matchingRoutes = $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/123.1', $method));
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{float}', $matchingRoutes['/path/{float}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/1.0', $method));
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{float}', $matchingRoutes['/path/{float}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/0.0', $method));
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{float}', $matchingRoutes['/path/{float}']->getPattern());
    }

    #[Test]
    #[DataProvider('getMatchingRoutesMethodDataProvider')]
    public function getMatchingRoutesBooleanNotMatchesTest(string $method): void
    {
        $this->fixture->add(Route::get('path/{boolean}', $this->cb));
        $this->fixture->add(Route::post('path/{boolean}', $this->cb));
        $this->fixture->add(Route::delete('path/{boolean}', $this->cb));
        $this->fixture->add(Route::put('path/{boolean}', $this->cb));
        $this->assertEmpty($this->fixture->getMatchingRoutes($this->buildTestRequest('/path/no/deeper', $method)));
        $this->assertEmpty($this->fixture->getMatchingRoutes($this->buildTestRequest('/path/yes/deeper', $method)));
        $this->assertEmpty($this->fixture->getMatchingRoutes($this->buildTestRequest('/path/123.1/deeper', $method)));
        $this->assertEmpty($this->fixture->getMatchingRoutes($this->buildTestRequest('/path', $method)));
        $this->assertEmpty($this->fixture->getMatchingRoutes($this->buildTestRequest('/path/some@domain', $method)));
    }

    /**
     * @return array<list<string>>
     */
    public static function getMatchingRoutesMethodDataProvider(): array
    {
        return [
            ['GET'],
            ['POST'],
            ['DELETE'],
            ['PUT'],
        ];
    }

    #[Test]
    #[DataProvider('getMatchingRoutesBooleanMatchesDataProvider')]
    public function getMatchingRoutesBooleanMatchesTest(string $path, string $method): void
    {
        $this->fixture->add(Route::get('path/{boolean}', $this->cb));
        $this->fixture->add(Route::post('path/{boolean}', $this->cb));
        $this->fixture->add(Route::delete('path/{boolean}', $this->cb));
        $this->fixture->add(Route::put('path/{boolean}', $this->cb));

        $matchingRoutes = $this->fixture->getMatchingRoutes($this->buildTestRequest($path, $method));
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('/path/{boolean}', $matchingRoutes['/path/{boolean}']->getPattern());
    }

    /**
     * @return list<list<string>>
     */
    public static function getMatchingRoutesBooleanMatchesDataProvider(): array
    {
        return [
            ['/path/1', 'GET'],
            ['/path/1', 'POST'],
            ['/path/1', 'PUT'],
            ['/path/1', 'DELETE'],
            ['/path/0', 'GET'],
            ['/path/0', 'POST'],
            ['/path/0', 'PUT'],
            ['/path/0', 'DELETE'],
            ['/path/true', 'GET'],
            ['/path/true', 'POST'],
            ['/path/true', 'PUT'],
            ['/path/true', 'DELETE'],
            ['/path/false', 'GET'],
            ['/path/false', 'POST'],
            ['/path/false', 'PUT'],
            ['/path/false', 'DELETE'],
            ['/path/yes', 'GET'],
            ['/path/yes', 'POST'],
            ['/path/yes', 'PUT'],
            ['/path/yes', 'DELETE'],
            ['/path/no', 'GET'],
            ['/path/no', 'POST'],
            ['/path/no', 'PUT'],
            ['/path/no', 'DELETE'],
            ['/path/on', 'GET'],
            ['/path/on', 'POST'],
            ['/path/on', 'PUT'],
            ['/path/on', 'DELETE'],
            ['/path/off', 'GET'],
            ['/path/off', 'POST'],
            ['/path/off', 'PUT'],
            ['/path/off', 'DELETE'],
        ];
    }
}
