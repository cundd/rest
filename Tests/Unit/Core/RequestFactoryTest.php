<?php

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Request;
use Cundd\Rest\RequestFactory;
use Cundd\Rest\RequestFactoryInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Diactoros\ServerRequestFactory;


/**
 * Test case for class new \Cundd\Rest\RequestFactory
 */
class RequestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestFactoryInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->buildRequestFactory();
    }

    public function tearDown()
    {
        unset($this->fixture);
        unset($_GET['u']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getUriTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getUriWithFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/2.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('/MyExt-MyModel/2', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getUriWithHtmlFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/2.html';
        $request = $this->fixture->getRequest();
        $this->assertEquals('/MyExt-MyModel/2', $request->getPath());
        $this->assertEquals('html', $request->getFormat());
    }

    /**
     * @test
     */
    public function getAliasUriTest()
    {
        $_GET['u'] = 'myAlias/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getAliasUriWithFormatTest()
    {
        $_GET['u'] = 'myAlias/2.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('/MyExt-MyModel/2', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getAliasUriWithHtmlFormatTest()
    {
        $_GET['u'] = 'myAlias/2.html';
        $request = $this->fixture->getRequest();
        $this->assertEquals('/MyExt-MyModel/2', $request->getPath());
        $this->assertEquals('html', $request->getFormat());
    }

    /**
     * @test
     */
    public function getOriginalResourceTypeTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1';
        /** @var Request $request */
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getOriginalResourceType());
    }

    /**
     * @test
     */
    public function getOriginalResourceTypeWithFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/2.json';
        /** @var Request $request */
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getOriginalResourceType());
    }

    /**
     * @test
     */
    public function getRootObjectKeyTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getRootObjectKey());
    }

    /**
     * @test
     */
    public function getRootObjectKeyWithFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/2.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getRootObjectKey());
    }

    /**
     * @test
     */
    public function getPathTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1';
        $path = $this->fixture->getRequest()->getResourceType();
        $this->assertEquals('MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getPathWithFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $path = $this->fixture->getRequest()->getResourceType();
        $this->assertEquals('MyExt-MyModel', $path);
    }

    /**
     * @test
     */
    public function getUnderscoredPathWithFormatAndIdTest()
    {
        $_GET['u'] = 'my_ext-my_model/1.json';
        $path = $this->fixture->getRequest()->getResourceType();
        $this->assertEquals('my_ext-my_model', $path);
    }

    /**
     * @test
     */
    public function getUnderscoredPathWithFormatTest2()
    {
        $_GET['u'] = 'my_ext-my_model.json';
        $path = $this->fixture->getRequest()->getResourceType();
        $this->assertEquals('my_ext-my_model', $path);
    }

    /**
     * @test
     */
    public function getFormatWithoutFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getFormatWithFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getFormatWithoutPathTest()
    {
        $_GET['u'] = '.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getFormatWithHtmlFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.html';
        $request = $this->fixture->getRequest();
        $this->assertEquals('html', $request->getFormat());
    }

    /**
     * @test
     */
    public function getFormatWithDecimalSegmentJsonFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.0.json';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getFormatWithDecimalSegmentHtmlFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.0.html';
        $request = $this->fixture->getRequest();
        $this->assertEquals('html', $request->getFormat());
    }

    /**
     * @test
     */
    public function getFormatWithDecimalSegmentTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.0';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getFormatWithNotExistingFormatTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1.blur';
        $request = $this->fixture->getRequest();
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function getUriWithAbsRefPrefixInSubDirectoryTest()
    {
        $_SERVER['REQUEST_URI'] = '/subDirectory/rest/MyExt-MyModel/1';
        $request = $this->buildRequestFactory(['absRefPrefix' => '/subDirectory/'])->getRequest();
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    /**
     * @test
     */
    public function getUriWithAbsRefPrefixInSubDirectoryWithoutTrailingSlashTest()
    {
        $_SERVER['REQUEST_URI'] = '/subDirectory/rest/MyExt-MyModel/1';
        $request = $this->buildRequestFactory(['absRefPrefix' => '/subDirectory'])->getRequest();
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    /**
     * @test
     */
    public function getUriWithAbsRefPrefixSlashTest()
    {
        $_SERVER['REQUEST_URI'] = '/rest/MyExt-MyModel/1';
        $request = $this->buildRequestFactory(['absRefPrefix' => '/'])->getRequest();
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    /**
     * @test
     */
    public function getUriWithAbsRefPrefixDomainTest()
    {
        $_SERVER['REQUEST_URI'] = '/rest/MyExt-MyModel/1';
        $request = $this->buildRequestFactory(['absRefPrefix' => 'http://example.com/'])->getRequest();
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    /**
     * @test
     */
    public function getUriWithAbsRefPrefixAutoTest()
    {
        $_SERVER['REQUEST_URI'] = '/rest/MyExt-MyModel/1';
        $request = $this->buildRequestFactory(['absRefPrefix' => 'auto'])->getRequest();
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
    }

    /**
     * @test
     */
    public function pathShouldNotIncludeQueryDataTest()
    {
        $_GET['u'] = 'MyExt-MyModel/1?query=string';
        $request = $this->buildRequestFactory()->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getResourceType());
        $this->assertEquals('json', $request->getFormat());

        $_GET['u'] = 'MyExt-MyModel/?query=string';
        $request = $this->buildRequestFactory()->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getResourceType());
        $this->assertEquals('json', $request->getFormat());

        $_GET['u'] = 'MyExt-MyModel?query=string';
        $request = $this->buildRequestFactory()->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getResourceType());
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     */
    public function urlAndPathShouldNotIncludeQueryDataFromRequestUriTest()
    {
        $_SERVER['REQUEST_URI'] = '/rest/MyExt-MyModel/1?query=string';
        $request = $this->buildRequestFactory()->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getResourceType());
        $this->assertEquals('/MyExt-MyModel/1', $request->getPath());
        $this->assertEquals('json', $request->getFormat());

        $_SERVER['REQUEST_URI'] = '/rest/MyExt-MyModel/?query=string';
        $request = $this->buildRequestFactory()->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getResourceType());
        $this->assertEquals('/MyExt-MyModel/', $request->getPath());
        $this->assertEquals('json', $request->getFormat());

        $_SERVER['REQUEST_URI'] = '/rest/MyExt-MyModel?query=string';
        $request = $this->buildRequestFactory()->getRequest();
        $this->assertEquals('MyExt-MyModel', $request->getResourceType());
        $this->assertEquals('/MyExt-MyModel', $request->getPath());
        $this->assertEquals('json', $request->getFormat());
    }

    /**
     * @test
     * @dataProvider createRequestTestDataProvider
     * @param $input
     * @param $resourceType
     * @param $path
     * @param $format
     */
    public function createRequestTest($input, $resourceType, $path, $format)
    {
        $_SERVER['REQUEST_URI'] = $input;
        $request = $this->buildRequestFactory()->getRequest();
        $this->assertInstanceOf(ResourceType::class, $request->getResourceType());
        $this->assertSame($resourceType, (string)$request->getResourceType());
        $this->assertSame($path, $request->getPath());
        $this->assertInstanceOf(Format::class, $request->getFormat());
        $this->assertSame($format, (string)$request->getFormat());
    }

    public function createRequestTestDataProvider()
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
     * @param array $configurationProviderSetting
     * @return RequestFactory
     */
    private function buildRequestFactory($configurationProviderSetting = [])
    {
        /** @var TypoScriptConfigurationProvider|ObjectProphecy $configurationProviderMock */
        $configurationProviderMock = $this->prophesize(TypoScriptConfigurationProvider::class);

        if (empty($configurationProviderSetting)) {
            $configurationProviderSetting = [
                'aliases.myAlias' => 'MyExt-MyModel',
            ];
        }
        $configurationProviderMock->getSetting(Argument::type('string'))->will(
            function ($args) use ($configurationProviderSetting) {
                if (isset($args[0])) {
                    $key = $args[0];

                    return isset($configurationProviderSetting[$key]) ? $configurationProviderSetting[$key] : null;
                }

                return null;
            }
        );

        $_SERVER['SERVER_NAME'] = 'rest.cundd.net';

        return new RequestFactory($configurationProviderMock->reveal(), ServerRequestFactory::class);
    }
}
