<?php
declare(strict_types=1);


namespace Cundd\Rest\Tests\Functional\VirtualObject;

use Cundd\Rest\VirtualObject\Exception\InvalidPropertyException;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\ObjectConverter;
use Cundd\Rest\VirtualObject\VirtualObject;

require_once __DIR__ . '/AbstractVirtualObjectCase.php';


class ObjectConverterTest extends AbstractVirtualObjectCase
{
    /**
     * @var ObjectConverter
     */
    protected $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = new ObjectConverter();
        $this->fixture->setConfiguration($this->getTestConfiguration());
    }

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function prepareDataFromVirtualObjectDataTest()
    {
        $testObjectData = $this->testObjectData;
        $virtualObject = new VirtualObject($testObjectData);
        $rawData = $this->fixture->prepareDataFromVirtualObjectData($virtualObject->getData());
        $this->assertEquals($this->testRawData, $rawData);
    }

    /**
     * @test
     */
    public function prepareForVirtualObjectDataTest()
    {
        $testRawData = $this->testRawData;
        $preparedData = $this->fixture->prepareForVirtualObjectData($testRawData);
        $this->assertEquals($this->testObjectData, $preparedData);
    }

    /**
     * @test
     */
    public function prepareDataFromVirtualObjectDataUpdateTest()
    {
        $testObjectData = $this->testObjectData;
        unset($testObjectData['property1']);
        unset($testObjectData['property6']);

        $testRawData = $this->testRawData;
        unset($testRawData['property_one']);
        unset($testRawData['property_six']);

        $virtualObject = new VirtualObject($testObjectData);
        $rawData = $this->fixture->prepareDataFromVirtualObjectData($virtualObject->getData(), false);
        $this->assertEquals($testRawData, $rawData);
    }

    /**
     * @test
     */
    public function prepareForVirtualObjectDataUpdateTest()
    {
        $testRawData = $this->testRawData;
        unset($testRawData['property_one']);
        unset($testRawData['property_six']);

        $testObjectData = $this->testObjectData;
        unset($testObjectData['property1']);
        unset($testObjectData['property6']);

        $preparedData = $this->fixture->prepareForVirtualObjectData($testRawData, false);
        $this->assertEquals($testObjectData, $preparedData);
    }

    /**
     * @test
     */
    public function convertFromVirtualObjectTest()
    {
        $testObjectData = $this->testObjectData;
        $virtualObject = new VirtualObject($testObjectData);
        $rawData = $this->fixture->convertFromVirtualObject($virtualObject);
        $this->assertEquals($this->testRawData, $rawData);
    }

    /**
     * @test
     */
    public function convertToVirtualObjectTest()
    {
        $testRawData = $this->testRawData;
        $virtualObject = $this->fixture->convertToVirtualObject($testRawData);
        $this->assertEquals($this->testObjectData, $virtualObject->getData());
    }

    /**
     * @test
     */
    public function convertFromVirtualObjectWithTypeTransformationTest()
    {
        $testObjectData = $this->testObjectData;
        $testObjectData['property2'] = '0.98';
        $testObjectData['property3'] = 10.002;
        $virtualObject = new VirtualObject($testObjectData);
        $rawData = $this->fixture->convertFromVirtualObject($virtualObject);
        $this->assertEquals($this->testRawData, $rawData);


        $testObjectData = $this->testObjectData;
        $testObjectData['property2'] = 'Actually this should not be a string';
        $virtualObject = new VirtualObject($testObjectData);
        $rawData = $this->fixture->convertFromVirtualObject($virtualObject);
        $this->assertNotEquals($this->testRawData, $rawData);
    }

    /**
     * @test
     */
    public function convertToVirtualObjectWithTypeTransformationTest()
    {
        $testRawData = $this->testRawData;
        $testRawData['property_two'] = '0.98';
        $testRawData['property_three'] = 10.002;
        $virtualObject = $this->fixture->convertToVirtualObject($testRawData);
        $this->assertEquals($this->testObjectData, $virtualObject->getData());


        $testRawData = $this->testRawData;
        $testRawData['property_two'] = 'Actually this should not be a string';
        $virtualObject = $this->fixture->convertToVirtualObject($testRawData);
        $this->assertNotEquals($this->testObjectData, $virtualObject->getData());
    }

    /**
     * @test
     */
    public function convertFromVirtualObjectWithSkippedUndefinedPropertyTest()
    {
        $this->fixture->getConfiguration()->setSkipUnknownProperties(true);

        $testObjectData = $this->testObjectData;
        $testObjectData['property99'] = 'What ever - this must not be in the result';
        $virtualObject = new VirtualObject($testObjectData);
        $rawData = $this->fixture->convertFromVirtualObject($virtualObject);

        $this->assertFalse(isset($rawData['property99']));
        $this->assertEquals($this->testRawData, $rawData);
    }

    /**
     * @test
     */
    public function convertToVirtualObjectWithSkippedUndefinedPropertyTest()
    {
        $this->fixture->getConfiguration()->setSkipUnknownProperties(true);

        $testRawData = $this->testRawData;
        $testRawData['property_ninetynine'] = 'What ever - this must not be in the result';
        $virtualObject = $this->fixture->convertToVirtualObject($testRawData);

        $virtualObjectData = $virtualObject->getData();
        $this->assertFalse(isset($virtualObjectData['property_ninetynine']));
        $this->assertEquals($this->testObjectData, $virtualObjectData);
    }

    /**
     * @test
     * @expectedException InvalidPropertyException
     */
    public function convertFromVirtualObjectWithUndefinedPropertyTest()
    {
        $testObjectData = $this->testObjectData;
        $testObjectData['property99'] = 'What ever - this must not be in the result';
        $virtualObject = new VirtualObject($testObjectData);
        $rawData = $this->fixture->convertFromVirtualObject($virtualObject);

        $this->assertFalse(isset($rawData['property99']));
        $this->assertEquals($this->testRawData, $rawData);
    }

    /**
     * @test
     * @expectedException InvalidPropertyException
     */
    public function convertToVirtualObjectWithUndefinedPropertyTest()
    {
        $testRawData = $this->testRawData;
        $testRawData['property_ninetynine'] = 'What ever - this must not be in the result';
        $virtualObject = $this->fixture->convertToVirtualObject($testRawData);

        $virtualObjectData = $virtualObject->getData();
        $this->assertFalse(isset($virtualObjectData['property_ninetynine']));
        $this->assertEquals($this->testObjectData, $virtualObjectData);
    }

    /**
     * @test
     */
    public function typeConverterTest()
    {
        $this->assertSame(90, $this->fixture->convertToType(90, 'integer'));
        $this->assertSame(1, $this->fixture->convertToType(1, 'int'));
        $this->assertSame(90, $this->fixture->convertToType(90.08, 'integer'));
        $this->assertSame(1, $this->fixture->convertToType(1.09, 'int'));
        $this->assertSame(90, $this->fixture->convertToType('90.08', 'integer'));
        $this->assertSame(1, $this->fixture->convertToType('1.09', 'int'));

        $this->assertSame(true, $this->fixture->convertToType(true, 'boolean'));
        $this->assertSame(false, $this->fixture->convertToType(false, 'boolean'));
        $this->assertSame(true, $this->fixture->convertToType(1, 'boolean'));
        $this->assertSame(false, $this->fixture->convertToType(0, 'boolean'));
        $this->assertSame(true, $this->fixture->convertToType('yes', 'boolean'));
        $this->assertSame(false, $this->fixture->convertToType('', 'boolean'));
        $this->assertSame(false, $this->fixture->convertToType([], 'boolean'));
        $this->assertSame(false, $this->fixture->convertToType(null, 'boolean'));

        $this->assertSame(1.09, $this->fixture->convertToType(1.09, 'float'));
        $this->assertSame(90.08, $this->fixture->convertToType(90.08, 'float'));
        $this->assertSame(1.0, $this->fixture->convertToType(1, 'float'));
        $this->assertSame(90.0, $this->fixture->convertToType(90, 'float'));
        $this->assertSame(0.0, $this->fixture->convertToType(0, 'float'));
        $this->assertSame(1.09, $this->fixture->convertToType('1.09', 'float'));
        $this->assertSame(90.08, $this->fixture->convertToType('90.08', 'float'));
        $this->assertSame(1.0, $this->fixture->convertToType(true, 'float'));
        $this->assertSame(0.0, $this->fixture->convertToType(false, 'float'));

        $this->assertSame('Hello', $this->fixture->convertToType('Hello', 'string'));
        $this->assertSame('how are you?', $this->fixture->convertToType('how are you?', 'string'));
        $this->assertSame('1.09', $this->fixture->convertToType(1.09, 'string'));
        $this->assertSame('90.08', $this->fixture->convertToType(90.08, 'string'));
        $this->assertSame('1', $this->fixture->convertToType(1, 'string'));
        $this->assertSame('90', $this->fixture->convertToType(90, 'string'));
        $this->assertSame('0', $this->fixture->convertToType(0, 'string'));
        $this->assertSame('1.09', $this->fixture->convertToType('1.09', 'string'));
        $this->assertSame('90.08', $this->fixture->convertToType('90.08', 'string'));
        $this->assertSame('1', $this->fixture->convertToType(true, 'string'));
        $this->assertSame('', $this->fixture->convertToType(false, 'string'));

        $this->assertSame('Hello', $this->fixture->convertToType('Hello', 'slug'));
        $this->assertSame(null, $this->fixture->convertToType('how are you?', 'slug'));
        $this->assertSame(null, $this->fixture->convertToType(1.09, 'slug'));
        $this->assertSame(null, $this->fixture->convertToType(90.08, 'slug'));
        $this->assertSame('1', $this->fixture->convertToType(1, 'slug'));
        $this->assertSame('90', $this->fixture->convertToType(90, 'slug'));
        $this->assertSame('0', $this->fixture->convertToType(0, 'slug'));
        $this->assertSame(null, $this->fixture->convertToType('1.09', 'slug'));
        $this->assertSame(null, $this->fixture->convertToType('90.08', 'slug'));
        $this->assertSame('1', $this->fixture->convertToType(true, 'slug'));
        $this->assertSame(null, $this->fixture->convertToType(false, 'slug'));
        $this->assertSame(null, $this->fixture->convertToType('i am not-"slug"', 'slug'));
        $this->assertSame(null, $this->fixture->convertToType('i am neither', 'slug'));
        $this->assertSame('but-i-am-1', $this->fixture->convertToType('but-i-am-1', 'slug'));
        $this->assertSame('me2', $this->fixture->convertToType('me2', 'slug'));

        $this->assertSame('www.my-domain.com', $this->fixture->convertToType('www.my-domain.com', 'url'));
        $this->assertSame('sub.my-domain.com', $this->fixture->convertToType('sub.my-domain.com', 'url'));
        $this->assertSame('my-domain.com', $this->fixture->convertToType('my-domain.com', 'url'));
        $this->assertSame('www.my-domain.com/', $this->fixture->convertToType('www.my-domain.com/', 'url'));
        $this->assertSame('sub.my-domain.com/', $this->fixture->convertToType('sub.my-domain.com/', 'url'));
        $this->assertSame('my-domain.com/', $this->fixture->convertToType('my-domain.com/', 'url'));
        $this->assertSame('www.my-domain.com/home', $this->fixture->convertToType('www.my-domain.com/home', 'url'));
        $this->assertSame('sub.my-domain.com/home', $this->fixture->convertToType('sub.my-domain.com/home', 'url'));
        $this->assertSame('my-domain.com/home', $this->fixture->convertToType('my-domain.com/home', 'url'));
        $this->assertSame('www.my-domain.com/home/', $this->fixture->convertToType('www.my-domain.com/home/', 'url'));
        $this->assertSame('sub.my-domain.com/home/', $this->fixture->convertToType('sub.my-domain.com/home/', 'url'));
        $this->assertSame('my-domain.com/home/', $this->fixture->convertToType('my-domain.com/home/', 'url'));
        $this->assertSame(
            'www.my-domain.com/home?id=whatever',
            $this->fixture->convertToType('www.my-domain.com/home?id=whatever', 'url')
        );
        $this->assertSame(
            'sub.my-domain.com/home?id=whatever',
            $this->fixture->convertToType('sub.my-domain.com/home?id=whatever', 'url')
        );
        $this->assertSame(
            'my-domain.com/home?id=whatever',
            $this->fixture->convertToType('my-domain.com/home?id=whatever', 'url')
        );
        $this->assertSame(
            'www.my-domain.com/home?id=whatever',
            $this->fixture->convertToType('www.my-domain.com/home?id=whatever', 'url')
        );
        $this->assertSame(
            'sub.my-domain.com/home?id=whatever',
            $this->fixture->convertToType('sub.my-domain.com/home?id=whatever', 'url')
        );
        $this->assertSame(
            'my-domain.com/home?id=whatever',
            $this->fixture->convertToType('my-domain.com/home?id=whatever', 'url')
        );
        $this->assertSame(
            'my-domain.com/home?id=whatever',
            $this->fixture->convertToType('my-domain.com/home?id=whatever', 'url')
        );
        $this->assertSame(
            'www.my-domain.com/index.php?id=whatever',
            $this->fixture->convertToType('www.my-domain.com/index.php?id=whatever', 'url')
        );
        $this->assertSame(
            'sub.my-domain.com/index.php?id=whatever',
            $this->fixture->convertToType('sub.my-domain.com/index.php?id=whatever', 'url')
        );
        $this->assertSame(
            'my-domain.com/index.php?id=whatever',
            $this->fixture->convertToType('my-domain.com/index.php?id=whatever', 'url')
        );
        $this->assertSame(
            'www.my-domain.com/?id=whatever',
            $this->fixture->convertToType('www.my-domain.com/?id=whatever', 'url')
        );
        $this->assertSame(
            'sub.my-domain.com/?id=whatever',
            $this->fixture->convertToType('sub.my-domain.com/?id=whatever', 'url')
        );
        $this->assertSame(
            'my-domain.com/?id=whatever',
            $this->fixture->convertToType('my-domain.com/?id=whatever', 'url')
        );
        $this->assertSame(
            'www.my-domain.com/home?id=whatever&one=more-parameter',
            $this->fixture->convertToType('www.my-domain.com/home?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'sub.my-domain.com/home?id=whatever&one=more-parameter',
            $this->fixture->convertToType('sub.my-domain.com/home?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'my-domain.com/home?id=whatever&one=more-parameter',
            $this->fixture->convertToType('my-domain.com/home?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'www.my-domain.com/home?id=whatever&one=more-parameter',
            $this->fixture->convertToType('www.my-domain.com/home?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'sub.my-domain.com/home?id=whatever&one=more-parameter',
            $this->fixture->convertToType('sub.my-domain.com/home?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'my-domain.com/home?id=whatever&one=more-parameter',
            $this->fixture->convertToType('my-domain.com/home?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'my-domain.com/home?id=whatever&one=more-parameter',
            $this->fixture->convertToType('my-domain.com/home?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'www.my-domain.com/index.php?id=whatever&one=more-parameter',
            $this->fixture->convertToType('www.my-domain.com/index.php?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'sub.my-domain.com/index.php?id=whatever&one=more-parameter',
            $this->fixture->convertToType('sub.my-domain.com/index.php?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'my-domain.com/index.php?id=whatever&one=more-parameter',
            $this->fixture->convertToType('my-domain.com/index.php?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'www.my-domain.com/?id=whatever&one=more-parameter',
            $this->fixture->convertToType('www.my-domain.com/?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'sub.my-domain.com/?id=whatever&one=more-parameter',
            $this->fixture->convertToType('sub.my-domain.com/?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'my-domain.com/?id=whatever&one=more-parameter',
            $this->fixture->convertToType('my-domain.com/?id=whatever&one=more-parameter', 'url')
        );

        $this->assertSame(
            'my-domain.com/?id=whatever&one=more-parameter',
            $this->fixture->convertToType('my-domain.com/  ?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'my-domain.com/?i<scriptd=whatever&one=more-parameter',
            $this->fixture->convertToType('my-domain.com/  ?i<scriptd=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            '/?id=whatever&one=more-parameter',
            $this->fixture->convertToType('/?id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame(
            'id=whatever&one=more-parameter',
            $this->fixture->convertToType('id=whatever&one=more-parameter', 'url')
        );
        $this->assertSame('i', $this->fixture->convertToType('i', 'url'));
        $this->assertSame('', $this->fixture->convertToType('£', 'url'));
        $this->assertSame('', $this->fixture->convertToType('ö', 'url'));
        $this->assertSame('', $this->fixture->convertToType('§', 'url'));

        $this->assertSame('someone@example.com', $this->fixture->convertToType('someone@example.com', 'email'));
        $this->assertSame('some.one@example.com', $this->fixture->convertToType('some.one@example.com', 'email'));
        $this->assertSame('some-one@example.com', $this->fixture->convertToType('some-one@example.com', 'email'));
        $this->assertSame('someone@example.com', $this->fixture->convertToType('some(one)@exa\\mple.com', 'email'));

        $this->assertSame('someone@example.com', $this->fixture->convertToType("\0\0someone@example.com\t", 'trim'));
        $this->assertSame('someone@example.com', $this->fixture->convertToType("  \0\0someone@example.com\t ", 'trim'));
        $this->assertSame(
            'someone@example.com',
            $this->fixture->convertToType("\x0B \0\0\0someone@example.com\t ", 'trim')
        );
    }

    /**
     * @test
     * @expectedException MissingConfigurationException
     */
    public function throwExceptionIfConfigurationIsNotSet()
    {
        $virtualObject = new VirtualObject();
        $converter = new ObjectConverter();
        $converter->convertFromVirtualObject($virtualObject);
    }

    protected $testRawData = [
        'property_one'   => 'Property 1 value',
        'property_two'   => 0.98,
        'property_three' => 10,
        'property_four'  => PHP_INT_MAX,
        'property_five'  => true,
        'property_six'   => false,
        'property_seven' => false,
        'property_eight' => 8,
    ];

    protected $testObjectData = [
        'property1'      => 'Property 1 value',
        'property2'      => 0.98,
        'property3'      => 10,
        'property4'      => PHP_INT_MAX,
        'property5'      => true,
        'property6'      => false,
        'property_seven' => false,
        'property_eight' => 8,
    ];
}
