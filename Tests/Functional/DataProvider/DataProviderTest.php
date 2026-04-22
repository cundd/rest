<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Cundd\Rest\DataProvider\ClassLoadingInterface;
use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\DataProvider\DataProviderInterface;
use Cundd\Rest\DataProvider\Extractor;
use Cundd\Rest\DataProvider\FileExtractor;
use Cundd\Rest\DataProvider\IdentityProviderInterface;
use Cundd\Rest\ObjectManagerInterface;
use Cundd\Rest\Request\ResourceType;
use Cundd\Rest\Tests\BaseModel;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\MyModel;
use Cundd\Rest\Tests\MyModelRepository;
use Cundd\Rest\Tests\MyNestedJsonSerializeModel;
use Cundd\Rest\Tests\MyNestedModel;
use Cundd\Rest\Tests\MyNestedModelWithObjectStorage;
use DateTime;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;

/**
 * @method void assertInstanceOf(string $expected, mixed $actual)
 */
final class DataProviderTest extends AbstractCase
{
    protected DataProviderInterface&ClassLoadingInterface $fixture;

    protected bool $initializeDatabase = false;

    public function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../FixtureClasses.php';
        if (!class_exists('MyExt\\Domain\\Model\\MyModel', false)) {
            class_alias(MyModel::class, 'MyExt\\Domain\\Model\\MyModel');
        }
        if (!class_exists('MyExt\\Domain\\Repository\\MyModelRepository', false)) {
            class_alias(
                MyModelRepository::class,
                'MyExt\\Domain\\Repository\\MyModelRepository'
            );
        }

        if (!class_exists('MyExt\\Domain\\Model\\MySecondModel', false)) {
            class_alias(MyModel::class, 'MyExt\\Domain\\Model\\MySecondModel');
        }
        if (!class_exists('MyExt\\Domain\\Repository\\MySecondModelRepository', false)) {
            class_alias(
                MyModelRepository::class,
                'MyExt\\Domain\\Repository\\MySecondModelRepository'
            );
        }

        if (!class_exists('Vendor\\MyExt\\Domain\\Model\\MyModel', false)) {
            class_alias(MyModel::class, 'Vendor\\MyExt\\Domain\\Model\\MyModel');
        }
        if (!class_exists('Vendor\\MyExt\\Domain\\Repository\\MyModelRepository', false)) {
            class_alias(
                MyModelRepository::class,
                'Vendor\\MyExt\\Domain\\Repository\\MyModelRepository'
            );
        }

        /** @var Container $container */
        $container = $this->getContainer();
        $container->set(
            'MyExt\\Domain\\Repository\\MyModelRepository',
            new MyModelRepository()
        );
        $container->set(
            'MyExt\\Domain\\Repository\\MySecondModelRepository',
            new MyModelRepository()
        );
        $container->set(
            'Vendor\\MyExt\\Domain\\Repository\\MyModelRepository',
            new MyModelRepository()
        );

        $dataProvider = $container->get(DataProvider::class);
        assert($dataProvider instanceof DataProviderInterface);
        assert($dataProvider instanceof ClassLoadingInterface);
        $this->fixture = $dataProvider;
    }

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    #[Test]
    public function convertTest(): void
    {
        $concreteObjectManager = $this->getContainer();
        $data = ['some' => 'Data'];

        $propertyMapperMock = $this->prophesize(PropertyMapper::class);

        $uniqueTestId = time();
        $modelClassSuffix = 'MyModel' . $uniqueTestId;
        $classNamespace = 'AVendor\\AnotherExt\\Domain\\Model';
        $this->buildClassIfNotExists($modelClassSuffix, $classNamespace);
        $modelClass = $classNamespace . '\\' . $modelClassSuffix;

        /** @var MethodProphecy $methodProphecy */
        $methodProphecy = $propertyMapperMock->convert(
            Argument::exact($data),
            Argument::exact($modelClass),
            Argument::type(PropertyMappingConfigurationInterface::class)
        );

        $methodProphecy->shouldBeCalled();
        $methodProphecy->will(fn ($args) => (object) $args[0]);

        $objectManagerProphecy = $this->prophesize(ObjectManagerInterface::class);

        $propertyMapper = $propertyMapperMock->reveal();
        /* @var MethodProphecy $methodProphecy */
        $objectManagerProphecy->get(Argument::type('string'))->will(
            function ($args) use ($propertyMapper, $concreteObjectManager) {
                if (PropertyMapper::class === $args[0]) {
                    return $propertyMapper;
                } else {
                    return $concreteObjectManager->get($args[0]);
                }
            }
        );

        /** @var ObjectManagerInterface $objectManager */
        $objectManager = $objectManagerProphecy->reveal();

        /** @var IdentityProviderInterface $identityProvider */
        $identityProvider = $this->prophesize(IdentityProviderInterface::class)
            ->reveal();
        $this->fixture = new DataProvider(
            $objectManager,
            new Extractor(new FileExtractor()),
            $identityProvider
        );

        $resourceType = new ResourceType('a_vendor-another_ext-my_model' . $uniqueTestId);
        $request = self::buildTestRequest(self::getUriRequestBase())
            ->withResourceType($resourceType);

        $this->fixture->createModel(
            $request,
            $data
        );
    }

    #[Test]
    public function getRepositoryForPathTest(): void
    {
        $repository = $this->fixture->getRepositoryForResourceType(
            new ResourceType('MyExt-MyModel')
        );
        $this->assertInstanceOf(MyModelRepository::class, $repository);

        $repository = $this->fixture->getRepositoryForResourceType(
            new ResourceType('my_ext-my_model')
        );
        $this->assertInstanceOf(MyModelRepository::class, $repository);
    }

    #[Test]
    public function getNamespacedRepositoryForPathTest(): void
    {
        $repository = $this->fixture->getRepositoryForResourceType(
            new ResourceType('MyExt-MySecondModel')
        );
        $this->assertInstanceOf(MyModelRepository::class, $repository);

        $repository = $this->fixture->getRepositoryForResourceType(
            new ResourceType('my_ext-my_second_model')
        );
        $this->assertInstanceOf(MyModelRepository::class, $repository);
    }

    #[Test]
    public function getNamespacedRepositoryForPathWithVendorTest(): void
    {
        $repository = $this->fixture->getRepositoryForResourceType(
            new ResourceType('Vendor-MyExt-MyModel')
        );
        $this->assertInstanceOf(
            '\\Vendor\\MyExt\\Domain\\Repository\\MyModelRepository',
            $repository
        );

        $repository = $this->fixture->getRepositoryForResourceType(new ResourceType('vendor-my_ext-my_model'));
        $this->assertInstanceOf(
            '\\Vendor\\MyExt\\Domain\\Repository\\MyModelRepository',
            $repository
        );

        $this->buildClass(
            'MyModelRepository',
            'Vendor\\MyExt\\Domain\\Repository\\Group',
            '\\TYPO3\\CMS\\Extbase\\Persistence\\Repository'
        );

        $groupRepositoryClass = 'Vendor\\MyExt\\Domain\\Repository\\Group\\MyModelRepository';
        $container = $this->getContainer();
        assert($container instanceof Container);
        $container->set(
            $groupRepositoryClass,
            new $groupRepositoryClass() // @phpstan-ignore class.notFound
        );
        $repository = $this->fixture->getRepositoryForResourceType(
            new ResourceType('vendor-my_ext-group-my_model')
        );
        $this->assertInstanceOf($groupRepositoryClass, $repository);
    }

    #[Test]
    public function createNewModelForPathTest(): void
    {
        $model = $this->fixture->createModel(
            self::buildTestRequest(self::getUriRequestBase())
                ->withResourceType(new ResourceType('MyExt-MyModel')),
            []
        );
        $this->assertInstanceOf(MyModel::class, $model);

        $model = $this->fixture->createModel(
            self::buildTestRequest(self::getUriRequestBase())
                ->withResourceType(new ResourceType('my_ext-my_model')),
            []
        );
        $this->assertInstanceOf(MyModel::class, $model);
    }

    #[Test]
    public function createNamespacedModelForPathTest(): void
    {
        $model = $this->fixture->createModel(
            self::buildTestRequest(self::getUriRequestBase())
                ->withResourceType(new ResourceType('MyExt-MySecondModel')),
            []
        );
        $this->assertInstanceOf(MyModel::class, $model);

        $model = $this->fixture->createModel(
            self::buildTestRequest(self::getUriRequestBase())
                ->withResourceType(new ResourceType('my_ext-my_second_model')),
            []
        );
        $this->assertInstanceOf(MyModel::class, $model);
    }

    #[Test]
    public function createNamespacedModelForPathWithVendorTest(): void
    {
        $model = $this->fixture->createModel(
            self::buildTestRequest(self::getUriRequestBase())
                ->withResourceType(new ResourceType('Vendor-MyExt-MyModel')),
            []
        );
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);

        $model = $this->fixture->createModel(
            self::buildTestRequest(self::getUriRequestBase())
                ->withResourceType(new ResourceType('vendor-my_ext-my_model')),
            []
        );
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);
    }

    #[Test]
    public function fetchModelWithEmptyDataTest(): void
    {
        $this->assertNull($this->fixture->fetchModel(
            self::buildTestRequest(self::getUriRequestBase())
                ->withResourceType(new ResourceType('MyExt-MyModel')),
            []
        ));
    }

    #[Test]
    public function createNewModelWithEmptyDataTest(): void
    {
        $data = [];
        $resourceType = 'MyExt-MyModel';

        /** @var MyModel $model */
        $model = $this->fixture->createModel(
            self::buildTestRequest(self::getUriRequestBase())
                ->withResourceType(new ResourceType($resourceType)),
            $data
        );
        $this->assertEquals('Initial value', $model->getName());
    }

    #[Test]
    public function getModelDataTest(): void
    {
        $model = new MyModel();
        $properties = $this->fixture->getModelData(
            self::buildTestRequest(self::getUriRequestBase()),
            $model
        );
        $this->assertEquals(
            [
                'name' => 'Initial value',
                'uid'  => null,
                'pid'  => null,
            ],
            $properties
        );
    }

    #[Test]
    public function getModelDataRecursiveTest(): void
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
                'child' => self::getUriRequestBase() . 'rest/cundd-rest-tests-my_nested_model/2/child',
                'uid'   => 2,
                'pid'   => null,
            ],

            'uid' => 1,
            'pid' => null,
        ];

        $this->assertEquals($expectedOutput, $this->fixture->getModelData(
            self::buildTestRequest(self::getUriRequestBase()),
            $model
        ));

        // Make sure the same result is returned if getModelData() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->getModelData(
            self::buildTestRequest(self::getUriRequestBase()),
            $model
        ));
    }

    #[Test]
    public function getModelDataRecursiveWithObjectStorageTest(): void
    {
        $testDate = new DateTime();
        $model = new MyNestedModelWithObjectStorage();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);

        $childModel = new MyNestedModel();
        $childModel->setDate($testDate);
        $childModel->_setProperty('uid', 2);

        /** @var ObjectStorage<BaseModel> $children */
        $children = new ObjectStorage();
        $children->offsetSet($model, null);
        $children->offsetSet($childModel, null);
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
                0 => $this->getUriRequestBase() . 'rest/cundd-rest-tests-my_nested_model_with_object_storage/1/',
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

        $this->assertEquals($expectedOutput, $this->fixture->getModelData(
            self::buildTestRequest(self::getUriRequestBase()),
            $model
        ));

        // Make sure the same result is returned if getModelData() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->getModelData(
            self::buildTestRequest(self::getUriRequestBase()),
            $model
        ));
    }

    #[Test]
    public function getNestedModelDataTest(): void
    {
        $testDate = new DateTime();
        $model = new MyNestedModel();
        $model->setDate($testDate);

        $properties = $this->fixture->getModelData(
            self::buildTestRequest(self::getUriRequestBase()),
            $model
        );
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

    #[Test]
    public function getJsonSerializeNestedModelDataTest(): void
    {
        $model = new MyNestedJsonSerializeModel();
        $properties = $this->fixture->getModelData(
            self::buildTestRequest(self::getUriRequestBase()),
            $model
        );
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

    private static function getUriRequestBase(): string
    {
        return 'https://rest.cundd.net/';
    }
}
