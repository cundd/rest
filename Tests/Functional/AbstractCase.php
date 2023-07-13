<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface as CunddLoggerInterface;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Tests\ClassBuilderTrait;
use Cundd\Rest\Tests\Functional\Integration\StreamLogger;
use Cundd\Rest\Tests\InjectPropertyTrait;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Cundd\Rest\Tests\ResponseBuilderTrait;
use Cundd\Rest\VirtualObject\Persistence\BackendFactory;
use Cundd\Rest\VirtualObject\Persistence\BackendInterface;
use Cundd\Rest\VirtualObject\Persistence\RawQueryBackendInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as DoctrineException;
use Exception;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @method void assertInternalType($expected, $actual, $message = '')
 * @method void assertEquals($expected, $actual, $message = '', ...$args)
 * @method void assertSame($expected, $actual, $message = '')
 * @method void assertEmpty($actual, $message = '')
 * @method void markTestSkipped($message = '')
 * @method void markTestIncomplete($message = '')
 * @method void assertInstanceOf($expected, $actual, $message = '')
 * @method void assertArrayHasKey($key, $array, $message = '')
 * @method void assertCount($expectedCount, $haystack, $message = '')
 * @method void assertNotEquals($expected, $actual, $message = '', ...$args)
 * @method void assertNull($actual, $message = '')
 * @method void assertFalse($condition, $message = '')
 * @method void assertTrue($condition, $message = '')
 * @method void assertNotEmpty($actual, $message = '')
 */
class AbstractCase extends FunctionalTestCase
{
    use ProphecyTrait;
    use ResponseBuilderTrait;
    use RequestBuilderTrait;
    use ClassBuilderTrait;
    use InjectPropertyTrait;

    protected array $testExtensionsToLoad = ['typo3conf/ext/rest'];

    public function setUp(): void
    {
        try {
            parent::setUp();
        } catch (DBALException|DoctrineException|\TYPO3\CMS\Core\Exception $exception) {
        }

        $_SERVER['HTTP_HOST'] = 'rest.cundd.net';

        $this->registerAssetCache();
        $this->registerLoggerImplementation();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Build a new request with the given URI
     *
     * @param string      $uri
     * @param string|null $format
     * @param string|null $method
     * @return RestRequestInterface
     */
    public function buildRequestWithUri(
        string $uri,
        ?string $format = null,
        ?string $method = null
    ): RestRequestInterface {
        return $this->buildTestRequest(
            $uri,
            $method,
            [],     // $params
            [],     // $headers
            null,   // $rawBody
            null,   // $parsedBody
            $format
        );
    }

    /**
     * @return BackendInterface|RawQueryBackendInterface
     */
    protected function getDatabaseBackend(): BackendInterface
    {
        return BackendFactory::getBackend();
    }

    protected function buildConfiguredObjectManager(): ObjectManagerInterface
    {
        return new ObjectManager($this->getContainer());
    }

    private function registerLoggerImplementation(): void
    {
        /** @var Container $container */
        $container = $this->getContainer();
        $streamLogger = new StreamLogger();
        $container->set(PsrLoggerInterface::class, $streamLogger);
        $container->set(CunddLoggerInterface::class, $streamLogger);
    }

    private function registerAssetCache(): void
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        try {
            $cacheManager->getCache('assets');
        } catch (Exception $e) {
            $cache = new VariableFrontend('assets', new NullBackend('unused'));
            $cacheManager->registerCache($cache);
        }
    }
}
