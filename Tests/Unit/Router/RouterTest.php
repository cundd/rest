<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 17/06/16
 * Time: 19:09
 */

namespace Cundd\Rest\Tests\Unit\Router;


use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\Router;
use Cundd\Rest\Tests\Unit\AbstractRequestBasedCase;

class RouterTest extends AbstractRequestBasedCase
{
    /**
     * @var Router
     */
    protected $fixture;

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


        $this->cb = \Closure::bind(
            function () {
                return func_get_args();
            },
            new \stdClass()
        );

        $this->fixture = new Router();


    }

    protected function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider getPreparedParametersDataProvider
     * @param string $pattern
     * @param string $path
     * @param array  $expectedParameters
     * @param bool   $noResult
     */
    public function dispatchTest($pattern, $path, array $expectedParameters, $noResult = false)
    {
        $this->fixture->add(Route::get($pattern, $this->cb));

        $request = $this->buildTestRequest($path, 'GET');
        $result = $this->fixture->dispatch($request);

        if ($noResult) {
            $this->assertNull($result);
        } else {
            $this->assertSame($request, array_shift($result));
            $this->assertSame($expectedParameters, $result);
        }
    }

    /**
     * @test
     * @dataProvider getPreparedParametersDataProvider
     * @param string $pattern
     * @param string $path
     * @param array  $expectedParameters
     */
    public function getPreparedParametersTest($pattern, $path, array $expectedParameters)
    {
        $this->fixture->add(Route::get($pattern, $this->cb));

        $prepareParameters = $this->fixture->getPreparedParameters($this->buildTestRequest($path, 'GET'));
        $this->assertSame($expectedParameters, $prepareParameters);
    }

    public function getPreparedParametersDataProvider()
    {
        return [
            ['{slug}/{float}/{bool}/{int}', '/slug/1.0/no/9', ['slug', 1.0, false, 9]],
            ['path/{slug}/{float}/{bool}/{int}', '/path/slug/1.0/no/9', ['slug', 1.0, false, 9]],
            ['path/{slug}/{float}/{bool}/{int}', '/path/slug/79.1/no/901', ['slug', 79.1, false, 901]],
            ['path/{slug}/sub-path/{float}/{bool}/{int}', '/path/slug/sub-path/1.0/no/9', ['slug', 1.0, false, 9]],
            ['path/{slug}/sub-path/{float}/{bool}/{int}', '/slug/sub-path/1.0/no/9', [], true],
            ['path/{slug}/sub-path/{float}/{bool}/{int}', '/', [], true],
            ['path/{slug}/sub-path/{float}/{bool}/{int}', '', [], true],
        ];
    }

    /**
     * @test
     * @param string $method
     * @dataProvider getMatchingRoutesMethodDataProvider
     */
    public function getMatchingRoutesSortedTest($method)
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
        $this->assertSame('path/{slug}', $matchingRoutes['path/{slug}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/perfect-match', $method)
        );
        $this->assertCount(2, $matchingRoutes);
        $firstMatch = reset($matchingRoutes);
        $this->assertSame('path/perfect-match', $firstMatch->getPattern());
    }

    /**
     * @test
     * @param string $method
     * @dataProvider getMatchingRoutesMethodDataProvider
     */
    public function getMatchingRoutesSlugTest($method)
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
        $this->assertSame('path/{slug}', $matchingRoutes['path/{slug}']->getPattern());
    }

    /**
     * @test
     * @param string $method
     * @dataProvider getMatchingRoutesMethodDataProvider
     */
    public function getMatchingRoutesIntegerTest($method)
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
        $this->assertSame('path/{integer}', $matchingRoutes['path/{integer}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/1', $method)
        );
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('path/{integer}', $matchingRoutes['path/{integer}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes(
            $this->buildTestRequest('/path/0', $method)
        );
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('path/{integer}', $matchingRoutes['path/{integer}']->getPattern());
    }

    /**
     * @test
     * @param string $method
     * @dataProvider getMatchingRoutesMethodDataProvider
     */
    public function getMatchingRoutesFloatTest($method)
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
        $this->assertSame('path/{float}', $matchingRoutes['path/{float}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/1.0', $method));
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('path/{float}', $matchingRoutes['path/{float}']->getPattern());

        $matchingRoutes = $this->fixture->getMatchingRoutes($this->buildTestRequest('/path/0.0', $method));
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('path/{float}', $matchingRoutes['path/{float}']->getPattern());
    }

    /**
     * @test
     * @param string $method
     * @dataProvider getMatchingRoutesMethodDataProvider
     */
    public function getMatchingRoutesBooleanNotMatchesTest($method)
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

    public function getMatchingRoutesMethodDataProvider()
    {
        return [
            ['GET'],
            ['POST'],
            ['DELETE'],
            ['PUT'],
        ];
    }

    /**
     * @test
     * @param string $path
     * @param string $method
     * @dataProvider getMatchingRoutesBooleanMatchesDataProvider
     */
    public function getMatchingRoutesBooleanMatchesTest($path, $method)
    {
        $this->fixture->add(Route::get('path/{boolean}', $this->cb));
        $this->fixture->add(Route::post('path/{boolean}', $this->cb));
        $this->fixture->add(Route::delete('path/{boolean}', $this->cb));
        $this->fixture->add(Route::put('path/{boolean}', $this->cb));

        $matchingRoutes = $this->fixture->getMatchingRoutes($this->buildTestRequest($path, $method));
        $this->assertCount(1, $matchingRoutes);
        $this->assertSame('path/{boolean}', $matchingRoutes['path/{boolean}']->getPattern());
    }

    public function getMatchingRoutesBooleanMatchesDataProvider()
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
