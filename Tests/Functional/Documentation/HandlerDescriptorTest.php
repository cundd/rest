<?php

namespace Cundd\Rest\Tests\Functional\Documentation;

use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Documentation\HandlerDescriptor;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;

class HandlerDescriptorTest extends AbstractCase
{
    /**
     * @var HandlerDescriptor
     */
    private $fixture;

    public function setup()
    {
        parent::setUp();

        $this->registerLoggerImplementation();

        $configurationProvider = new TypoScriptConfigurationProvider();
        $configurationProvider->setSettings(
            [
                'handler' => [
                    'all' => [
                        'path'      => 'all',
                        'className' => CrudHandler::class,
                    ],
                ],
            ]
        );

        $this->fixture = new HandlerDescriptor(
            $this->objectManager->get(ObjectManagerInterface::class),
            $configurationProvider
        );
    }

    /**
     * @test
     */
    public function getInformationTest()
    {
        $result = $this->fixture->getInformation();
        $this->assertInternalType('array', $result);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('all', $result);
        $allHandler = $result['all'];
        $this->assertInstanceOf(CrudHandler::class, $allHandler['handler']);
        $this->assertCount(5, $allHandler['routes']);

        $this->assertArrayHasKey('GET', $allHandler['routes']);
        $this->assertArrayHasKey('POST', $allHandler['routes']);
        $this->assertArrayHasKey('PUT', $allHandler['routes']);
        $this->assertArrayHasKey('DELETE', $allHandler['routes']);
        $this->assertArrayHasKey('PATCH', $allHandler['routes']);

        $this->assertCount(4, $allHandler['routes']['GET']);
    }

    protected function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }
}
