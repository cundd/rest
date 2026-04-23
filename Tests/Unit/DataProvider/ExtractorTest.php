<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\DataProvider;

use ArrayIterator;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\Extractor;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\DataProvider\FileExtractor;
use Cundd\Rest\Tests\ClassBuilderTrait;
use Cundd\Rest\Tests\Fixtures\MyBackedIntEnum;
use Cundd\Rest\Tests\Fixtures\MyBackedStringEnum;
use Cundd\Rest\Tests\MyModel;
use Cundd\Rest\Tests\MyModelRepository;
use Cundd\Rest\Tests\MyNestedJsonSerializeModel;
use Cundd\Rest\Tests\MyNestedModel;
use Cundd\Rest\Tests\MyNestedModelWithObjectStorage;
use Cundd\Rest\Tests\RequestBuilderUtility;
use Cundd\Rest\Tests\SimpleClass;
use Cundd\Rest\Tests\SimpleClassJsonSerializable;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use SplObjectStorage;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;

use function class_exists;

/**
 * Test case for class new \Cundd\Rest\App
 */
final class ExtractorTest extends TestCase
{
    use ProphecyTrait;
    use ClassBuilderTrait;

    protected ExtractorInterface $fixture;

    public function setUp(): void
    {
        parent::setUp();

        $configurationProvider = $this->prophesize(
            ConfigurationProviderInterface::class
        )->reveal();
        $logger = $this->prophesize(LoggerInterface::class)->reveal();

        $this->fixture = new Extractor(new FileExtractor($logger));
    }

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @param array<int,mixed> $expected
     */
    #[Test]
    #[DataProvider('extractSimpleDataProvider')]
    public function extractSimpleTest(mixed $input, array $expected): void
    {
        $this->assertEquals($expected, $this->fixture->extract(
            self::buildTestUri(),
            $input
        ));
    }

    /**
     * @return array<int,mixed>
     */
    public static function extractSimpleDataProvider(): array
    {
        self::prepareClasses();
        $exampleData = ['firstName' => 'Daniel', 'lastName' => 'Corn'];
        $exampleDataWithPidAndUid = $exampleData + ['uid' => 1, 'pid' => 2];

        return [
            [$exampleDataWithPidAndUid, $exampleDataWithPidAndUid],
            [new SimpleClass($exampleData), $exampleData],
            [new SimpleClassJsonSerializable($exampleDataWithPidAndUid), $exampleDataWithPidAndUid],
            [new MyModel($exampleDataWithPidAndUid), ['uid' => 1, 'pid' => 2, 'name' => 'Initial value']],
        ];
    }

    /**
     * @param array<int,mixed> $expected
     */
    #[Test]
    #[DataProvider('extractCollectionDataProvider')]
    public function extractCollectionTest(mixed $input, array $expected): void
    {
        $this->assertEquals($expected, $this->fixture->extract(
            self::buildTestUri(),
            $input
        ));
    }

    /**
     * @return array<int,mixed>
     */
    public static function extractCollectionDataProvider(): array
    {
        $testSets = [];

        foreach (self::extractSimpleDataProvider() as $simpleTestSet) {
            $input = $simpleTestSet[0];
            $expected = [$simpleTestSet[1]];

            $testSets[] = [[$input], $expected];

            $testSets[] = [new ArrayIterator([$input]), $expected];

            // Use the Object Storage only if the input is an object
            if (is_object($input)) {
                $os = new SplObjectStorage();
                $os->offsetSet($input, null);
                $testSets[] = [$os, $expected];

                $os = new ObjectStorage();
                $os->offsetSet($input, null);
                $testSets[] = [$os, $expected];
            }
        }

        return $testSets;
    }

    /**
     * @param array<int,mixed> $expected
     */
    #[Test]
    #[DataProvider('extractCollectionDataProvider')]
    public function extractModelWithCollectionPropertyTest(
        mixed $input,
        array $expected,
    ): void {
        $model = new MyNestedModel();
        $model->setChild($input);

        $result = $this->fixture->extract(
            self::buildTestUri(),
            $model
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('child', $result);

        $this->assertEquals($expected, $result['child']);
    }

    #[Test]
    public function extractRecursiveTest(): void
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

        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));
    }

    protected function buildNestedModels(
        int $currentDepth,
        int $maxDepth,
        DateTime $testDate,
    ): MyNestedModel {
        $model = new MyNestedModel();
        $model->_setProperty('uid', $currentDepth + 1);
        $model->setDate($testDate);

        if ($currentDepth + 1 < $maxDepth) {
            $model->setChild($this->buildNestedModels($currentDepth + 1, $maxDepth, $testDate));
        }

        return $model;
    }

    #[Test]
    public function extractShouldRespectDepthLimitTest(): void
    {
        $maxDepth = 20;
        $currentDepth = 0;
        $testDate = new DateTime();

        $model = $this->buildNestedModels($currentDepth, $maxDepth, $testDate);

        $expectedOutput = [
            'base'  => 'Base',
            'date'  => $testDate->format(DateTime::ATOM),
            'child' => [
                'base'  => 'Base',
                'date'  => $testDate->format(DateTime::ATOM),
                'child' => [
                    'base'  => 'Base',
                    'date'  => $testDate->format(DateTime::ATOM),
                    'child' => 'http://rest.cundd.net/rest/cundd-rest-tests-my_nested_model/3/child',
                    'uid'   => 3,
                    'pid'   => null,
                ],
                'uid' => 2,
                'pid' => null,
            ],

            'uid' => 1,
            'pid' => null,
        ];

        $configurationProviderProphecy = $this->prophesize(ConfigurationProviderInterface::class);
        /** @var ConfigurationProviderInterface $configurationProvider */
        $configurationProvider = $configurationProviderProphecy->reveal();
        $logger = $this->prophesize(LoggerInterface::class)->reveal();

        $this->fixture = new Extractor(
            new FileExtractor($logger),
            3
        );

        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));
    }

    #[Test]
    public function extractSelfReferencingRecursiveTest(): void
    {
        $testDate = new DateTime();
        $model = new MyNestedModel();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);
        $model->setChild($model);

        $expectedOutput = [
            'base'  => 'Base',
            'date'  => $testDate->format(DateTime::ATOM),
            'child' => 'http://rest.cundd.net/rest/cundd-rest-tests-my_nested_model/1/child',
            'uid'   => 1,
            'pid'   => null,
        ];

        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));
    }

    #[Test]
    public function extractRecursiveWithObjectStorageTest(): void
    {
        $testDate = new DateTime();
        $model = new MyNestedModelWithObjectStorage();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);

        $childModel = new MyNestedModel();
        $childModel->setDate($testDate);
        $childModel->_setProperty('uid', 2);

        /** @var ObjectStorage<MyNestedModel> */
        $children = new ObjectStorage();
        $children->attach($model);
        $children->attach($childModel);
        $model->setChildren($children);

        $expectedOutput = $this->getExpectedOutputForRecursion($testDate);
        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));
    }

    #[Test]
    public function extractRecursiveWithArrayTest(): void
    {
        $testDate = new DateTime();
        $model = new MyNestedModelWithObjectStorage();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);

        $childModel = new MyNestedModel();
        $childModel->setDate($testDate);
        $childModel->_setProperty('uid', 2);

        $model->setChildren([$model, $childModel]);

        $expectedOutput = $this->getExpectedOutputForRecursion($testDate);

        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));
    }

    #[Test]
    public function extractRecursiveWithArrayIteratorTest(): void
    {
        $testDate = new DateTime();
        $model = new MyNestedModelWithObjectStorage();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);

        $childModel = new MyNestedModel();
        $childModel->setDate($testDate);
        $childModel->_setProperty('uid', 2);

        $model->setChildren(new ArrayIterator([$model, $childModel]));

        $expectedOutput = $this->getExpectedOutputForRecursion($testDate);

        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract(
            self::buildTestUri(),
            $model
        ));
    }

    #[Test]
    public function getNestedModelDataTest(): void
    {
        $testDate = new DateTime();
        $model = new MyNestedModel();
        $model->setDate($testDate);

        $properties = $this->fixture->extract(
            self::buildTestUri(),
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
        $properties = $this->fixture->extract(
            self::buildTestUri(),
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

    /**
     * @return array<string,mixed>
     */
    protected function getExpectedOutputForRecursion(DateTimeInterface $testDate): array
    {
        return [
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
    }

    #[Test]
    #[DataProvider('extractEnumDataProvider')]
    public function extractEnumTest(
        MyBackedIntEnum|MyBackedStringEnum $input,
        int|string $expected,
    ): void {
        $result = $this->fixture->extract(
            self::buildTestUri(),
            $input
        );

        $this->assertEquals($expected, $result);
    }

    #[Test]
    #[DataProvider('extractEnumDataProvider')]
    public function extractEnumChildTest(
        MyBackedIntEnum|MyBackedStringEnum $input,
        int|string $expected,
    ): void {
        $model = new MyNestedModel();
        $model->setChild($input);

        $result = $this->fixture->extract(
            self::buildTestUri(),
            $model
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('child', $result);

        $this->assertEquals($expected, $result['child']);
    }

    /**
     * @return array{0:MyBackedStringEnum|MyBackedIntEnum,1:string|int}[]
     */
    public static function extractEnumDataProvider(): array
    {
        return [
            [MyBackedStringEnum::A, MyBackedStringEnum::A->value],
            [MyBackedStringEnum::B, MyBackedStringEnum::B->value],
            [MyBackedStringEnum::C, MyBackedStringEnum::C->value],

            [MyBackedIntEnum::A, MyBackedIntEnum::A->value],
            [MyBackedIntEnum::B, MyBackedIntEnum::B->value],
            [MyBackedIntEnum::C, MyBackedIntEnum::C->value],
        ];
    }

    private static function prepareClasses(): void
    {
        self::buildClassIfNotExists(AbstractDomainObject::class);
        self::buildClassIfNotExists(Repository::class);
        self::buildClassIfNotExists(ObjectStorage::class, SplObjectStorage::class);
        self::buildInterfaceIfNotExists(DomainObjectInterface::class);

        require_once __DIR__ . '/../../FixtureClasses.php';

        if (!class_exists('Tx_MyExt_Domain_Model_MyModel', false)) {
            class_alias(MyModel::class, 'Tx_MyExt_Domain_Model_MyModel');
        }
        if (!class_exists('Tx_MyExt_Domain_Repository_MyModelRepository', false)) {
            class_alias(MyModelRepository::class, 'Tx_MyExt_Domain_Repository_MyModelRepository');
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
    }

    private static function buildTestUri(): UriInterface
    {
        return RequestBuilderUtility::buildTestUri('http://rest.cundd.net/');
    }
}
