<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Documentation;

use Cundd\Rest\Configuration\ConfigurationProviderFactoryInterface;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;
use Cundd\Rest\Documentation\HandlerDescriptor;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Tests\Functional\AbstractCase;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

class HandlerDescriptorTest extends AbstractCase
{
    /**
     * @var HandlerDescriptor
     */
    private $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $configurationProvider = new StandaloneConfigurationProvider(
            [
                'paths' => [
                    'all' => [
                        'path'         => 'all',
                        'handlerClass' => CrudHandler::class,
                    ],
                ],
            ]
        );
        $configurationProviderFactory = $this->prophesize(
            ConfigurationProviderFactoryInterface::class
        );
        $configurationProviderFactory->build(Argument::type(RestRequestInterface::class))
            ->willReturn($configurationProvider);

        $configurationProviderFactory->buildFromSite(Argument::type(Site::class))
            ->willReturn($configurationProvider);

        $this->fixture = new HandlerDescriptor(
            $this->getContainer()->get(ObjectManager::class),
            $configurationProviderFactory->reveal()
        );
    }

    protected function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    public function getInformationTest(): void
    {
        $site = $this->get(SiteFinder::class)->getSiteByIdentifier('test-site');
        $result = $this->fixture->getInformation($site);
        $this->assertIsArray($result);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('all', $result);
        $allHandler = $result['all'];
        $this->assertInstanceOf(CrudHandler::class, $allHandler['handler'] ?? null);
        $this->assertCount(6, $allHandler['routes']);

        $this->assertArrayHasKey('GET', $allHandler['routes']);
        $this->assertArrayHasKey('POST', $allHandler['routes']);
        $this->assertArrayHasKey('PUT', $allHandler['routes']);
        $this->assertArrayHasKey('DELETE', $allHandler['routes']);
        $this->assertArrayHasKey('PATCH', $allHandler['routes']);

        $this->assertCount(4, $allHandler['routes']['GET']);
    }
}
