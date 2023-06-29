<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Cundd\Rest\Configuration\ConfigurationProvider;
use Cundd\Rest\DataProvider\ClassLoadingInterface;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Extractor;
use Cundd\Rest\DataProvider\IdentityProviderInterface;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\MyModel;
use Cundd\Rest\Tests\MyModelRepository;
use Cundd\Rest\Tests\MyNestedJsonSerializeModel;
use Cundd\Rest\Tests\MyNestedModel;
use Cundd\Rest\Tests\MyNestedModelWithObjectStorage;
use DateTime;
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
     * @var DataProviderInterface|ClassLoadingInterface
     */
    protected $fixture;

    public function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../FixtureClasses.php';
        if (!class_exists('MyExt\\Domain\\Model\\MyModel', false)) {
            class_alias(MyModel::class, 'MyExt\\Domain\\Model\\MyModel');
        }
        if (!class_exists('MyExt\\Domain\\Repository\\MyModelRepository', false)) {
            class_alias(MyModelRepository::class, 'MyExt\\Domain\\Repository\\MyModelRepository');
        }

        if (!class_exists('MyExt\\Domain\\Model\\MySecondModel', false)) {
            class_alias(MyModel::class, 'MyExt\\Domain\\Model\\MySecondModel');
        }
        if (!class_exists('MyExt\\Domain\\Repository\\MySecondModelRepository', false)) {
            class_alias(MyModelRepository::class, 'MyExt\\Domain\\Repository\\MySecondModelRepository');
        }

        if (!class_exists('Vendor\\MyExt\\Domain\\Model\\MyModel', false)) {
            class_alias(MyModel::class, 'Vendor\\MyExt\\Domain\\Model\\MyModel');
        }
        if (!class_exists('Vendor\\MyExt\\Domain\\Repository\\MyModelRepository', false)) {
            class_alias(MyModelRepository::class, 'Vendor\\MyExt\\Domain\\Repository\\MyModelRepository');
        }

        $this->fixture = $this->objectManager->get(DataProvider::class);
    }

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function convertTest()
    {
        $concreteObjectManager = $this->objectManager;
        $data = ['some' => 'Data'];

        /** @var ObjectProphecy|PropertyMapper $propertyMapperMock */
        $propertyMapperMock = $this->prophesize(PropertyMapper::class);

        $this->buildClassIfNotExists('Tx_AnotherExt_Domain_Model_MyModel');

        /** @var MethodProphecy $methodProphecy */
        $methodProphecy = $propertyMapperMock->convert(
            Argument::exact($data),
            Argument::exact('Tx_AnotherExt_Domain_Model_MyModel'),
            Argument::type(PropertyMappingConfigurationInterface::class)
        );

        $methodProphecy->shouldBeCalled();

        /** @var ObjectProphecy|ObjectManagerInterface $objectManagerProphecy */
        $objectManagerProphecy = $this->prophesize(ObjectManagerInterface::class);

        $propertyMapper = $propertyMapperMock->reveal();
        /** @var MethodProphecy $methodProphecy */
        $objectManagerProphecy->get(Argument::type('string'))->will(
            function ($args) use ($propertyMapper, $concreteObjectManager) {
                if ($args[0] === PropertyMapper::class) {
                    return $propertyMapper;
                } else {
                    return $concreteObjectManager->get($args[0]);
                }
            }
        );

        /** @var ObjectManagerInterface $objectManager */
        $objectManager = $objectManagerProphecy->reveal();

        /** @var IdentityProviderInterface $identityProvider */
        $identityProvider = $this->prophesize(IdentityProviderInterface::class)->reveal();
        $this->fixture = new DataProvider(
            $objectManager,
            new Extractor(new ConfigurationProvider()),
            $identityProvider
        );

        $this->fixture->createModel($data, new ResourceType('a_vendor-another_ext-my_model'));
    }

    /**
     * @test
     */
    public function getRepositoryForPathTest()
    {
        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('MyExt-MyModel'));
        $this->assertInstanceOf(MyModelRepository::class, $repository);

        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('my_ext-my_model'));
        $this->assertInstanceOf(MyModelRepository::class, $repository);
    }

    /**
     * @test
     */
    public function getNamespacedRepositoryForPathTest()
    {
        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('MyExt-MySecondModel'));
        $this->assertInstanceOf(MyModelRepository::class, $repository);

        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('my_ext-my_second_model'));
        $this->assertInstanceOf(MyModelRepository::class, $repository);
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
    public function createNewModelForPathTest()
    {
        $model = $this->fixture->createModel([], new ResourceType('MyExt-MyModel'));
        $this->assertInstanceOf(MyModel::class, $model);

        $model = $this->fixture->createModel([], new ResourceType('my_ext-my_model'));
        $this->assertInstanceOf(MyModel::class, $model);
    }

    /**
     * @test
     */
    public function createNamespacedModelForPathTest()
    {
        $model = $this->fixture->createModel([], new ResourceType('MyExt-MySecondModel'));
        $this->assertInstanceOf(MyModel::class, $model);

        $model = $this->fixture->createModel([], new ResourceType('my_ext-my_second_model'));
        $this->assertInstanceOf(MyModel::class, $model);
    }

    /**
     * @test
     */
    public function createNamespacedModelForPathWithVendorTest()
    {
        $model = $this->fixture->createModel([], new ResourceType('Vendor-MyExt-MyModel'));
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);

        $model = $this->fixture->createModel([], new ResourceType('vendor-my_ext-my_model'));
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);
    }

    /**
     * @test
     */
    public function fetchModelWithEmptyDataTest()
    {
        $this->assertNull($this->fixture->fetchModel([], new ResourceType('MyExt-MyModel')));
    }

    /**
     * @test
     */
    public function createNewModelWithEmptyDataTest()
    {
        $data = [];
        $resourceType = 'MyExt-MyModel';

        /** @var MyModel $model */
        $model = $this->fixture->createModel($data, new ResourceType($resourceType));
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
        $testDate = new DateTime();
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
            'date'  => $testDate->format(DateTime::ATOM),
            'child' => [
                'base'  => 'Base',
                'date'  => $testDate->format(DateTime::ATOM),
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
        $testDate = new DateTime();
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
            'date'  => $testDate->format(DateTime::ATOM),
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
                       'date'  => $testDate->format(DateTime::ATOM),
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
        $testDate = new DateTime();
        $model = new MyNestedModel();
        $model->setDate($testDate);

        $properties = $this->fixture->getModelData($model);
        $this->assertEquals(
            [
                'base'  => 'Base',
                'date'  => $testDate->format(DateTime::ATOM),
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
