<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Configuration\ConfigurationProviderFactoryInterface;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Request;
use Cundd\Rest\Request\Format;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\RequestFactory;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\Tests\RequestBuilderUtility;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Test case for class new \Cundd\Rest\RequestFactory
 */
final class RequestFactoryTest extends TestCase
{
    use ProphecyTrait;

    protected RequestFactoryInterface $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixture = $this->buildRequestFactory();
    }

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    public function getUriTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1'));
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getUriWithFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/2.json'));
        $this->assertEquals('/MyExt-MyModel/2', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getUriWithHtmlFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/2.html'));
        $this->assertEquals('/MyExt-MyModel/2', $request->getPath());
        $this->assertEquals('html', $request->getFormat());
    }

    #[Test]
    public function getAliasUriTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('myAlias/1'));
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getAliasUriWithFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('myAlias/2.json'));
        $this->assertEquals('/MyExt-MyModel/2', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getAliasUriWithHtmlFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('myAlias/2.html'));
        $this->assertEquals('/MyExt-MyModel/2', $request->getPath());
        $this->assertEquals('html', $request->getFormat());
    }

    #[Test]
    public function getOriginalResourceTypeTest(): void
    {
        /** @var Request $request */
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1'));
        $this->assertEquals('MyExt-MyModel', $request->getOriginalResourceType());
    }

    #[Test]
    public function getOriginalResourceTypeWithFormatTest(): void
    {
        /** @var Request $request */
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/2.json'));
        $this->assertEquals('MyExt-MyModel', $request->getOriginalResourceType());
    }

    #[Test]
    public function getPathTest(): void
    {
        $path = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1'))
            ->getResourceType();
        $this->assertEquals('MyExt-MyModel', $path);
    }

    #[Test]
    public function getPathWithFormatTest(): void
    {
        $path = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1.json'))
            ->getResourceType();
        $this->assertEquals('MyExt-MyModel', $path);
    }

    #[Test]
    public function getUnderscoredPathWithFormatAndIdTest(): void
    {
        $path = $this->fixture
            ->buildRequest($this->buildServerRequest('my_ext-my_model/1.json'))
            ->getResourceType();
        $this->assertEquals('my_ext-my_model', $path);
    }

    #[Test]
    public function getUnderscoredPathWithFormatTest2(): void
    {
        $path = $this->fixture
            ->buildRequest($this->buildServerRequest('my_ext-my_model.json'))
            ->getResourceType();
        $this->assertEquals('my_ext-my_model', $path);
    }

    #[Test]
    public function getFormatWithoutFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1'));
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getFormatWithFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1.json'));
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getFormatWithoutPathTest(): void
    {
        $request = $this->fixture->buildRequest(
            $this->buildServerRequest(
                '.json'
            )
        );
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getFormatWithHtmlFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1.html'));
        $this->assertEquals('html', $request->getFormat());
    }

    #[Test]
    public function getFormatWithDecimalSegmentJsonFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1.0.json'));
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getFormatWithDecimalSegmentHtmlFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1.0.html'));
        $this->assertEquals('html', $request->getFormat());
    }

    #[Test]
    public function getFormatWithDecimalSegmentTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1.0'));
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getFormatWithNotExistingFormatTest(): void
    {
        $request = $this->fixture
            ->buildRequest($this->buildServerRequest('MyExt-MyModel/1.blur'));
        $this->assertEquals('json', $request->getFormat());
    }

    #[Test]
    public function getUriWithAbsRefPrefixInSubDirectoryTest(): void
    {
        $request = $this->buildRequestFactory(['absRefPrefix' => '/subDirectory/'])
            ->buildRequest(
                $this->buildServerRequest('/subDirectory/rest/MyExt-MyModel/1')
            );
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    #[Test]
    public function getUriWithAbsRefPrefixInSubDirectoryWithoutTrailingSlashTest(): void
    {
        $request = $this->buildRequestFactory(['absRefPrefix' => '/subDirectory'])
            ->buildRequest($this->buildServerRequest('/subDirectory/rest/MyExt-MyModel/1'));
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    #[Test]
    public function getUriWithAbsRefPrefixSlashTest(): void
    {
        $request = $this->buildRequestFactory(['absRefPrefix' => '/'])
            ->buildRequest($this->buildServerRequest('/rest/MyExt-MyModel/1'));
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    #[Test]
    public function getUriWithAbsRefPrefixDomainTest(): void
    {
        $request = $this->buildRequestFactory(['absRefPrefix' => 'http://example.com/'])
            ->buildRequest($this->buildServerRequest('/rest/MyExt-MyModel/1'));
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    #[Test]
    public function getUriWithAbsRefPrefixAutoTest(): void
    {
        $request = $this->buildRequestFactory(['absRefPrefix' => 'auto'])
            ->buildRequest($this->buildServerRequest('/rest/MyExt-MyModel/1'));
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    // #[Test]
    // public function pathShouldNotIncludeQueryDataTest(): void
    // {
    //     $request = $this->buildRequestFactory()->buildRequest(
    //         $this->buildServerRequest('MyExt-MyModel/1?query=string')
    //     );
    //     $this->assertEquals('MyExt-MyModel', $request->getResourceType());
    //     $this->assertEquals('json', $request->getFormat());
    //
    //     $request = $this->buildRequestFactory()->buildRequest(
    //         $this->buildServerRequest('MyExt-MyModel/?query=string')
    //     );
    //     $this->assertEquals('MyExt-MyModel', $request->getResourceType());
    //     $this->assertEquals('json', $request->getFormat());
    //
    //     $request = $this->buildRequestFactory()->buildRequest(
    //         $this->buildServerRequest('MyExt-MyModel?query=string')
    //     );
    //     $this->assertEquals('MyExt-MyModel', $request->getResourceType());
    //     $this->assertEquals('json', $request->getFormat());
    // }

    // #[Test]
    // public function urlAndPathShouldNotIncludeQueryDataFromRequestUriTest(): void
    // {
    //     $request = $this->buildRequestFactory()->buildRequest(
    //         $this->buildServerRequest('/rest/MyExt-MyModel/1?query=string')
    //     );
    //     $this->assertEquals('MyExt-MyModel', $request->getResourceType());
    //     $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    //     $this->assertEquals('json', $request->getFormat());
    //
    //     $request = $this->buildRequestFactory()->buildRequest(
    //         $this->buildServerRequest('/rest/MyExt-MyModel/?query=string')
    //     );
    //     $this->assertEquals('MyExt-MyModel', $request->getResourceType());
    //     $this->assertEquals('/MyExt-MyModel/', $request->getPath());
    //     $this->assertEquals('json', $request->getFormat());
    //
    //     $request = $this->buildRequestFactory()->buildRequest(
    //         $this->buildServerRequest('/rest/MyExt-MyModel?query=string')
    //     );
    //     $this->assertEquals('MyExt-MyModel', $request->getResourceType());
    //     $this->assertEquals('/MyExt-MyModel', $request->getPath());
    //     $this->assertEquals('json', $request->getFormat());
    // }

    #[Test]
    #[DataProvider('createRequestTestDataProvider')]
    public function createRequestTest(string $input, string $resourceType, string $path, string $format): void
    {
        // $_SERVER['REQUEST_URI'] = $input;
        $request = $this->buildRequestFactory()->buildRequest(
            $this->buildServerRequest($input)
        );
        $this->assertInstanceOf(ResourceType::class, $request->getResourceType());
        $this->assertSame($resourceType, (string) $request->getResourceType());
        $this->assertSame($path, $request->getPath());
        $this->assertInstanceOf(Format::class, $request->getFormat());
        $this->assertSame($format, (string) $request->getFormat());
    }

    /**
     * @return list<array{0:string,1:string,2:string,3:string}>
     */
    public static function createRequestTestDataProvider(): array
    {
        return [
            ['/rest/MyExt-MyModel', 'MyExt-MyModel', '/MyExt-MyModel', 'json'],
            ['/rest/MyExt-MyModel/', 'MyExt-MyModel', '/MyExt-MyModel/', 'json'],
            ['/rest/MyExt-MyModel/1.0', 'MyExt-MyModel', '/MyExt-MyModel/1.0', 'json'],
            ['/rest/MyExt-MyModel/1.0.json', 'MyExt-MyModel', '/MyExt-MyModel/1.0', 'json'],
            ['/rest/MyExt-MyModel/1.0.html', 'MyExt-MyModel', '/MyExt-MyModel/1.0', 'html'],
            ['/rest/MyExt-MyModel/198.0.html', 'MyExt-MyModel', '/MyExt-MyModel/198.0', 'html'],
            ['/rest/MyExt-MyModel/19.80', 'MyExt-MyModel', '/MyExt-MyModel/19.80', 'json'],
            ['/rest/MyExt-MyModel/19.80.html', 'MyExt-MyModel', '/MyExt-MyModel/19.80', 'html'],
            ['/rest/MyExt-MyModel/19.8.html', 'MyExt-MyModel', '/MyExt-MyModel/19.8', 'html'],
        ];
    }

    /**
     * @param array<string,mixed> $configurationProviderSetting
     */
    private function buildRequestFactory(
        array $configurationProviderSetting = [],
    ): RequestFactoryInterface {
        $configurationProviderMock = $this->prophesize(
            ConfigurationProviderInterface::class
        );

        if (empty($configurationProviderSetting)) {
            $configurationProviderSetting = [
                'aliases.myAlias' => 'MyExt-MyModel',
            ];
        }
        /** @var string $stringArg */
        $stringArg = Argument::type('string');
        $configurationProviderMock->getSetting($stringArg, Argument::cetera())->will(
            function ($args) use ($configurationProviderSetting) {
                if (isset($args[0])) {
                    $key = $args[0];

                    return $configurationProviderSetting[$key] ?? $args[1] ?? null;
                }

                return null;
            }
        );

        $_SERVER['SERVER_NAME'] = 'rest.cundd.net';

        /** @var ConfigurationProviderInterface $configurationProvider */
        $configurationProvider = $configurationProviderMock->reveal();

        $configurationProviderFactory = $this->prophesize(
            ConfigurationProviderFactoryInterface::class
        );
        $configurationProviderFactory
            ->build(Argument::any())
            ->willReturn($configurationProvider);

        return new RequestFactory($configurationProviderFactory->reveal());
    }

    private function buildServerRequest(string $url): ServerRequestInterface
    {
        return RequestBuilderUtility::buildTestServerRequest($url);
    }
}
