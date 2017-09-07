<?php

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\MyModel;
use Cundd\Rest\Tests\MyNestedJsonSerializeModel;
use Cundd\Rest\Tests\MyNestedModel;
use Cundd\Rest\Tests\MyNestedModelWithObjectStorage;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;


/**
 * Test case for class new \Cundd\Rest\App
 */
class DataProviderTest extends AbstractCase
{
    /**
     * @var \Cundd\Rest\DataProvider\DataProviderInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/../../FixtureClasses.php';
        if (!class_exists('Tx_MyExt_Domain_Model_MyModel', false)) {
            class_alias('\\Cundd\\Rest\\Tests\\MyModel', 'Tx_MyExt_Domain_Model_MyModel');
        }
        if (!class_exists('Tx_MyExt_Domain_Repository_MyModelRepository', false)) {
            class_alias('\\Cundd\\Rest\\Tests\\MyModelRepository', 'Tx_MyExt_Domain_Repository_MyModelRepository');
        }

        if (!class_exists('MyExt\\Domain\\Model\\MySecondModel', false)) {
            class_alias('\\Cundd\\Rest\\Tests\\MyModel', 'MyExt\\Domain\\Model\\MySecondModel');
        }
        if (!class_exists('MyExt\\Domain\\Repository\\MySecondModelRepository', false)) {
            class_alias(
                '\\Cundd\\Rest\\Tests\\MyModelRepository',
                'MyExt\\Domain\\Repository\\MySecondModelRepository'
            );
        }

        if (!class_exists('Vendor\\MyExt\\Domain\\Model\\MyModel', false)) {
            class_alias('\\Cundd\\Rest\\Tests\\MyModel', 'Vendor\\MyExt\\Domain\\Model\\MyModel');
        }
        if (!class_exists('Vendor\\MyExt\\Domain\\Repository\\MyModelRepository', false)) {
            class_alias(
                '\\Cundd\\Rest\\Tests\\MyModelRepository',
                'Vendor\\MyExt\\Domain\\Repository\\MyModelRepository'
            );
        }

        $this->fixture = $this->objectManager->get(DataProvider::class);
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function convertTest()
    {
        $data = ['some' => 'Data'];

        /** @var ObjectProphecy|PropertyMapper $propertyMapperMock */
        $propertyMapperMock = $this->prophesize(PropertyMapper::class);

        /** @var MethodProphecy $methodProphecy */
        $methodProphecy = $propertyMapperMock->convert(
            Argument::exact($data),
            Argument::exact('AVendor\\AnotherExt\\Domain\\Model\\MyModel'),
            Argument::type(PropertyMappingConfigurationInterface::class)
        );

        $methodProphecy->shouldBeCalled();

        $this->injectPropertyIntoObject($propertyMapperMock->reveal(), 'propertyMapper', $this->fixture);
        $this->fixture->getModelWithDataForResourceType($data, new ResourceType('a_vendor-another_ext-my_model'));
    }

    /**
     * @test
     */
    public function getRepositoryForPathTest()
    {
        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('MyExt-MyModel'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModelRepository', $repository);

        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('my_ext-my_model'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModelRepository', $repository);
    }

    /**
     * @test
     */
    public function getNamespacedRepositoryForPathTest()
    {
        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('MyExt-MySecondModel'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModelRepository', $repository);

        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('my_ext-my_second_model'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModelRepository', $repository);
    }

    /**
     * @test
     */
    public function getNamespacedRepositoryForPathWithVendorTest()
    {
        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('Vendor-MyExt-MyModel'));
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Repository\\MyModelRepository', $repository);

        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('vendor-my_ext-my_model'));
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Repository\\MyModelRepository', $repository);

        $this->buildClass(
            'MyModelRepository',
            'Vendor\\MyExt\\Domain\\Repository\\Group',
            '\\TYPO3\\CMS\\Extbase\\Persistence\\Repository'
        );
        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('vendor-my_ext-group-my_model'));
        $this->assertInstanceOf('Vendor\\MyExt\\Domain\\Repository\\Group\\MyModelRepository', $repository);
    }

    /**
     * @test
     */
    public function getModelForPathTest()
    {
        $model = $this->fixture->getModelWithDataForResourceType([], new ResourceType('MyExt-MyModel'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModel', $model);

        $model = $this->fixture->getModelWithDataForResourceType([], new ResourceType('my_ext-my_model'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModel', $model);
    }

    /**
     * @test
     */
    public function getNamespacedModelForPathTest()
    {
        $model = $this->fixture->getModelWithDataForResourceType([], new ResourceType('MyExt-MySecondModel'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModel', $model);

        $model = $this->fixture->getModelWithDataForResourceType([], new ResourceType('my_ext-my_second_model'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModel', $model);
    }

    /**
     * @test
     */
    public function getNamespacedModelForPathWithVendorTest()
    {
        $model = $this->fixture->getModelWithDataForResourceType([], new ResourceType('Vendor-MyExt-MyModel'));
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);

        $model = $this->fixture->getModelWithDataForResourceType([], new ResourceType('vendor-my_ext-my_model'));
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);
    }

    /**
     * @test
     */
    public function getModelWithEmptyDataTest()
    {
        $data = [];
        $resourceType = 'MyExt-MyModel';

        /** @var \Cundd\Rest\Tests\MyModel $model */
        $model = $this->fixture->getModelWithDataForResourceType($data, new ResourceType($resourceType));
        $this->assertEquals('Initial value', $model->getName());
    }

    /**
     * @test
     */
    public function getModelDataTest()
    {
        $model = new MyModel();
        $properties = $this->fixture->getModelData($model);
        $this->assertEquals(
            [
                'name' => 'Initial value',
                'uid'  => null,
                'pid'  => null,
            ],
            $properties
        );
    }

    /**
     * @test
     */
    public function getModelDataRecursiveTest()
    {
        $testDate = new \DateTime();
        $model = new MyNestedModel();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);

        $childModel = new MyNestedModel();
        $childModel->setDate($testDate);
        $childModel->_setProperty('uid', 2);

        $childModel->setChild($model);
        $model->setChild($childModel);

        $expectedOutput = [
            'base'  => 'Base',
            'date'  => $testDate->format(\DateTime::ATOM),
            'child' => [
                'base'  => 'Base',
                'date'  => $testDate->format(\DateTime::ATOM),
                'child' => 'http://rest.cundd.net/rest/cundd-rest-tests-my_nested_model/2/child',
                'uid'   => 2,
                'pid'   => null,
            ],

            'uid' => 1,
            'pid' => null,
        ];

        $this->assertEquals($expectedOutput, $this->fixture->getModelData($model));

        // Make sure the same result is returned if getModelData() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->getModelData($model));
    }

    /**
     * @test
     */
    public function getModelDataRecursiveWithObjectStorageTest()
    {
        $testDate = new \DateTime();
        $model = new MyNestedModelWithObjectStorage();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);

        $childModel = new MyNestedModel();
        $childModel->setDate($testDate);
        $childModel->_setProperty('uid', 2);

        $children = new ObjectStorage();
        $children->attach($model);
        $children->attach($childModel);
        $model->setChildren($children);

        $expectedOutput = [
            'base'  => 'Base',
            'date'  => $testDate->format(\DateTime::ATOM),
            'child' => [
                'uid'  => null,
                'pid'  => null,
                'name' => 'Initial value',
            ],

            'uid'      => 1,
            'pid'      => null,
            'children' => [
                0 => 'http://rest.cundd.net/rest/cundd-rest-tests-my_nested_model_with_object_storage/1/',
                // <- This is $model
                1 => [ // <- This is $childModel
                    'base'  => 'Base',
                    'date'  => $testDate->format(\DateTime::ATOM),
                    'uid'   => 2,
                    'pid'   => null,
                    'child' => [
                        'name' => 'Initial value',
                        'uid'  => null,
                        'pid'  => null,

                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedOutput, $this->fixture->getModelData($model));

        // Make sure the same result is returned if getModelData() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->getModelData($model));
    }

    /**
     * @test
     */
    public function getNestedModelDataTest()
    {
        $testDate = new \DateTime();
        $model = new MyNestedModel();
        $model->setDate($testDate);

        $properties = $this->fixture->getModelData($model);
        $this->assertEquals(
            [
                'base'  => 'Base',
                'date'  => $testDate->format(\DateTime::ATOM),
                'uid'   => null,
                'pid'   => null,
                'child' => [
                    'name' => 'Initial value',
                    'uid'  => null,
                    'pid'  => null,
                ],
            ],
            $properties
        );
    }

    /**
     * @test
     */
    public function getJsonSerializeNestedModelDataTest()
    {
        $model = new MyNestedJsonSerializeModel();
        $properties = $this->fixture->getModelData($model);
        $this->assertEquals(
            [
                'base'  => 'Base',
                'child' => [
                    'name' => 'Initial value',
                    'uid'  => null,
                    'pid'  => null,
                ],
            ],
            $properties
        );
    }
}
