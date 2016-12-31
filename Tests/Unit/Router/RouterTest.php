<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 17/06/16
 * Time: 19:09
 */

namespace Cundd\Rest\Tests\Unit\Router;


use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request;
use Cundd\Rest\Router\Router;
use Cundd\Rest\Tests\Unit\AbstractRequestBasedCase;

class RouterTest extends AbstractRequestBasedCase
{
    /**
     * @var Router
     */
    protected $fixture;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new Router();
    }

    protected function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider getConfiguration
     * @param $path
     * @param $method
     * @param $expectedResult
     */
    public function routeTest($path, $method, $expectedResult)
    {
        $this->markTestSkipped();
        $handler = function (RestRequestInterface $request) {
            return 1;
        };

        $handler = \Closure::bind(function() {}, new \stdClass());
//        $handler = \Closure::bind($handler, new \stdClass());

        $this->fixture->register($handler, $path, $method);
        $this->fixture->register($handler, dirname($path), $method);
        $this->fixture->register($handler, dirname(dirname($path)), $method);
        $result = $this->fixture->dispatch($this->buildTestRequest($path, $method));

        $this->assertInstanceOf('Cundd\\Rest\\Response', $result);
        $this->assertEquals($expectedResult, (string)$result->getBody());
    }




    public function getConfiguration()
    {
        return array(
            array('path/to/resource/', 'GET', 1),
//            array('path/to/resource', 'GET', 1),
//            array('path/to/', 'GET', 1),
//            array('path/to', 'GET', 1),
        );
    }
}
