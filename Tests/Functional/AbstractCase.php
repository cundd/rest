<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional;

use Cundd\Rest\Authentication\BasicAuthenticationProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\GreetingHandler;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Log\LoggerInterface as CunddLoggerInterface;
use Cundd\Rest\Tests\ClassBuilderTrait;
use Cundd\Rest\Tests\Functional\Integration\StreamLogger;
use Cundd\Rest\Tests\RequestBuilderTrait;
use Cundd\Rest\Tests\ResponseBuilderTrait;
use Doctrine\DBAL\Exception as DoctrineException;
use Exception;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Exception as TYPO3CoreException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class AbstractCase extends FunctionalTestCase
{
    use ProphecyTrait;
    use ResponseBuilderTrait;
    use RequestBuilderTrait;
    use ClassBuilderTrait;
    use SiteBasedTestTrait;

    public const BASE_URI = 'http://localhost:8888/';

    protected const LANGUAGE_PRESETS = [
        'EN' => ['id' => 0, 'title' => 'English', 'locale' => 'en_US.UTF8'],
        'DE' => ['id' => 1, 'title' => 'German', 'locale' => 'de_DE.UTF8'],
        'FR' => ['id' => 2, 'title' => 'French', 'locale' => 'fr_FR.UTF8'],
        'ES' => ['id' => 3, 'title' => 'Spanish', 'locale' => 'es_ES.UTF8'],
    ];

    protected const DEFAULT_SETTINGS = [
        'rest' => [
            'settings' => [
                'paths' => [
                    'greeting' => [
                        'path'              => 'greeting',
                        'read'              => 'allow',
                        'write'             => 'deny',
                        'cacheLifetime'     => -1,
                        'handlerClass'      => GreetingHandler::class,
                        'dataProviderClass' => DataProviderInterface::class,
                    ],
                    'all' => [
                        'path'              => 'all',
                        'read'              => 'deny',
                        'write'             => 'deny',
                        'cacheLifetime'     => -1,
                        'handlerClass'      => CrudHandler::class,
                        'dataProviderClass' => DataProviderInterface::class,
                    ],
                ],
                'singularToPlural' => [
                    'news'        => 'news',
                    'equipment'   => 'equipment',
                    'information' => 'information',
                    'rice'        => 'rice',
                    'money'       => 'money',
                    'species'     => 'species',
                    'series'      => 'series',
                    'fish'        => 'fish',
                    'sheep'       => 'sheep',
                    'press'       => 'press',
                    'sms'         => 'sms',
                ],
                'authenticationProvider' => [
                    '10' => BasicAuthenticationProvider::class,
                ],
            ],
        ],
    ];
    protected array $testExtensionsToLoad = ['typo3conf/ext/rest'];

    public function setUp(): void
    {
        try {
            parent::setUp();
        } catch (DoctrineException|TYPO3CoreException $exception) {
        }

        $_SERVER['HTTP_HOST'] = 'rest.cundd.net';

        $this->registerAssetCache();
        $this->registerLoggerImplementation();

        $this->writeSiteConfiguration(
            'test-site',
            $this->buildSiteConfiguration(1000, self::BASE_URI)
                      + ['settings' => self::DEFAULT_SETTINGS],
            [
                $this->buildDefaultLanguageConfiguration('EN', '/'),
                $this->buildLanguageConfiguration('FR', '/fr/', ['EN']),
                $this->buildLanguageConfiguration('DE', '/de/', ['EN']),
                $this->buildLanguageConfiguration('ES', '/es/', ['EN']),
            ]
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Build a new request with the given URI
     */
    public function buildTestRequestWithUri(
        string $uri,
        ?string $format = null,
        ?string $method = null,
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
     * @param array<mixed,mixed>|string|null $body
     * @param array<mixed,mixed>|string|null $body
     * @param array<mixed,mixed>             $headers
     */
    public function buildTestRequestWithSite(
        string $path,
        string $method = 'GET',
        array|string|null $body = null,
        array $headers = [],
        ?Site $site = null,
    ): RestRequestInterface {
        /* @var Site $site */
        $site ??= $this->get(SiteFinder::class)->getSiteByIdentifier('test-site');

        $uri = self::BASE_URI . ltrim($path, '/');

        return $this->buildTestRequest(
            $uri,
            $method,
            [],
            $headers,
            !is_array($body) ? $body : null,
            is_array($body) ? $body : null
        )
            ->withAttribute('site', $site);
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
