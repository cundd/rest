<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Documentation;

use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Documentation\HandlerDescriptor;
use Cundd\Rest\Handler\AuthHandler;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;

class HandlerDescriptorTest extends AbstractCase
{
    /**
     * @var HandlerDescriptor
     */
    private $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->registerLoggerImplementation();

        $configurationProvider = new TypoScriptConfigurationProvider();
        $configurationProvider->setSettings(
            [
                'paths' => [
                    'all'  => [
                        'path'         => 'all',
                        'handlerClass' => CrudHandler::class,
                    ],
                    'auth' => [
                        'path'         => 'auth',
                        'handlerClass' => AuthHandler::class,
                    ],
                ],

                'aliases' => [
                    'auth_alias' => 'auth',
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
        $this->assertIsArray($result);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('all', $result);
        $allHandler = $result['all'];
        $this->assertInstanceOf(CrudHandler::class, $allHandler['handler']);
        $this->assertCount(6, $allHandler['routes']);

        $this->assertArrayHasKey('GET', $allHandler['routes']);
        $this->assertArrayHasKey('POST', $allHandler['routes']);
        $this->assertArrayHasKey('PUT', $allHandler['routes']);
        $this->assertArrayHasKey('DELETE', $allHandler['routes']);
        $this->assertArrayHasKey('PATCH', $allHandler['routes']);

        $this->assertCount(4, $allHandler['routes']['GET']);

        $this->assertArrayHasKey('auth', $result);
        $authHandler = $result['auth'];
        $this->assertInstanceOf(AuthHandler::class, $authHandler['handler']);
        $this->assertCount(3, $authHandler['routes']);
        $this->assertArrayHasKey('GET', $authHandler['routes']);
        $this->assertArrayHasKey('POST', $authHandler['routes']);
        $this->assertArrayHasKey('OPTIONS', $authHandler['routes']);
        /** @var ResourceConfiguration $configuration */
        $configuration = $authHandler['configuration'];
        $this->assertInstanceOf(ResourceConfiguration::class, $configuration);
        $this->assertEquals(['auth_alias'], $configuration->getAliases());
    }

    protected function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }
}
