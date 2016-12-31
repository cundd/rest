<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest\Tests\Functional\Dispatcher;

use Cundd\Rest\Dispatcher;
use Cundd\Rest\Dispatcher\ApiConfigurationInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;


/**
 * Test case for class new \Cundd\Rest\App
 *
 * @version   $Id$
 * @copyright Copyright belongs to the respective authors
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author    Daniel Corn <cod@(c) 2014 Daniel Corn <info@cundd.net>, cundd.li>
 */
class ApiConfigurationTest extends AbstractCase
{
    /**
     * @var \Cundd\Rest\Dispatcher\ApiConfigurationInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        require_once __DIR__ . '/../../FixtureClasses.php';
        $restObjectManager = $this->objectManager->get(ObjectManager::class);
        $this->fixture = new Dispatcher($restObjectManager, false);
    }

    public function tearDown()
    {
        /** @var RequestFactoryInterface $requestFactory */
        if ($this->objectManager) {
            $requestFactory = $this->objectManager->get('Cundd\\Rest\\RequestFactory');
            $requestFactory->resetRequest();
        }
        unset($this->fixture);
        unset($_GET['u']);
        parent::tearDown();
    }

    /**
     * @param ApiConfigurationInterface|object $object
     * @param string                           $property
     * @param mixed                            $value
     * @return ApiConfigurationInterface|object
     */
    protected function injectProperty($object, $property, $value)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }

    /**
     * @param string                                $methodName
     * @param Callback                              $callback
     * @param ApiConfigurationInterface|object|null $object
     * @return ApiConfigurationInterface|object
     */
    protected function mockAppInstanceMethodFunctionWithParameters($methodName, $callback, $object = null)
    {
        if (!$object) {
            $object = $this->fixture;
        }
        $appInstance = $this->getMockObjectGenerator()->getMock('Bullet\App', array('method'));
        $appInstance->expects($this->once())
            ->method('method')
            ->with(
                $this->equalTo($methodName),
                $this->equalTo($callback)
            );

        return $this->injectProperty($object, 'app', $appInstance);
    }

    /**
     * @test
     */
    public function registerParameterTest()
    {
        $callback = function () {
        };
        $param = 'int';

        $appInstance = $this->getMockObjectGenerator()->getMock('Bullet\App', array('param'));
        $appInstance->expects($this->once())
            ->method('param')
            ->with(
                $this->equalTo($param),
                $this->equalTo($callback)
            );
        $this->injectProperty($this->fixture, 'app', $appInstance);

        $this->fixture->registerParameter($param, $callback);
    }

    /**
     * @test
     */
    public function registerPathTest()
    {
        $callback = function () {
        };
        $path = 'login';

        $appInstance = $this->getMockObjectGenerator()->getMock('Bullet\App', array('path'));
        $appInstance->expects($this->once())
            ->method('path')
            ->with(
                $this->equalTo($path),
                $this->equalTo($callback)
            );
        $this->injectProperty($this->fixture, 'app', $appInstance);

        $this->fixture->registerPath($path, $callback);
    }

    /**
     * @test
     */
    public function registerGetMethodTest()
    {
        $callback = function () {
        };
        $this->mockAppInstanceMethodFunctionWithParameters('GET', $callback);
        $this->fixture->registerGetMethod($callback);
    }

    /**
     * @test
     */
    public function registerPostMethodTest()
    {
        $callback = function () {
        };
        $this->mockAppInstanceMethodFunctionWithParameters('POST', $callback);
        $this->fixture->registerPostMethod($callback);
    }

    /**
     * @test
     */
    public function registerPutMethodTest()
    {
        $callback = function () {
        };
        $this->mockAppInstanceMethodFunctionWithParameters('PUT', $callback);
        $this->fixture->registerPutMethod($callback);
    }

    /**
     * @test
     */
    public function registerDeleteMethodTest()
    {
        $callback = function () {
        };
        $this->mockAppInstanceMethodFunctionWithParameters('DELETE', $callback);
        $this->fixture->registerDeleteMethod($callback);
    }

    /**
     * @test
     */
    public function registerPatchMethodTest()
    {
        $callback = function () {
        };
        $this->mockAppInstanceMethodFunctionWithParameters('PATCH', $callback);
        $this->fixture->registerPatchMethod($callback);
    }

    /**
     * @test
     */
    public function registerHttpMethodTest()
    {
        $callback = function () {
        };
        $method = 'head';
        $this->mockAppInstanceMethodFunctionWithParameters($method, $callback);
        $this->fixture->registerHttpMethod($method, $callback);
    }

    /**
     * @test
     */
    public function registerHttpMethodWithMultipleMethodsTest()
    {
        $callback = function () {
        };
        $method = array('head', 'option', 'get', 'post');
        $this->mockAppInstanceMethodFunctionWithParameters($method, $callback);
        $this->fixture->registerHttpMethod($method, $callback);
    }
}
