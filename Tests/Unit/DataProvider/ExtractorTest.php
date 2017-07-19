<?php

namespace Cundd\Rest\Tests\Unit\DataProvider;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\DataProvider\Extractor;
use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\Tests\ClassBuilderTrait;
use Cundd\Rest\Tests\MyModel;
use Cundd\Rest\Tests\MyModelRepository;
use Cundd\Rest\Tests\MyNestedJsonSerializeModel;
use Cundd\Rest\Tests\MyNestedModel;
use Cundd\Rest\Tests\MyNestedModelWithObjectStorage;
use Cundd\Rest\Tests\SimpleClass;
use Cundd\Rest\Tests\SimpleClassJsonSerializable;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;


/**
 * Test case for class new \Cundd\Rest\App
 *
 * @version   $Id$
 * @copyright Copyright belongs to the respective authors
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author    Daniel Corn <cod@(c) 2014 Daniel Corn <info@cundd.net>, cundd.li>
 */
class ExtractorTest extends \PHPUnit_Framework_TestCase
{
    use ClassBuilderTrait;

    /**
     * @var ExtractorInterface
     */
    protected $fixture;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $_SERVER['HTTP_HOST'] = 'rest.cundd.net';
    }

    public function setUp()
    {
        parent::setUp();

        /** @var ObjectProphecy|ConfigurationProviderInterface $configurationProviderProphecy */
        $configurationProviderProphecy = $this->prophesize(ConfigurationProviderInterface::class);

        $this->fixture = new Extractor(
            $configurationProviderProphecy->reveal(),
            $this->prophesize(LoggerInterface::class)->reveal()

        );
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     * @param mixed $input
     * @param array $expected
     * @dataProvider extractSimpleDataProvider
     */
    public function extractSimpleTest($input, $expected)
    {
        $this->assertEquals($expected, $this->fixture->extract($input));
    }

    /**
     * @return array
     */
    public function extractSimpleDataProvider()
    {
        $this->prepareClasses();
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
     * @test
     * @param mixed $input
     * @param array $expected
     * @dataProvider extractCollectionDataProvider
     */
    public function extractCollectionTest($input, $expected)
    {
        $this->assertEquals($expected, $this->fixture->extract($input));
    }

    /**
     * @return array
     */
    public function extractCollectionDataProvider()
    {
        $this->setUpBeforeClass();

        $testSets = [];

        foreach ($this->extractSimpleDataProvider() as $simpleTestSet) {
            $input = $simpleTestSet[0];
            $expected = [$simpleTestSet[1]];

            $testSets[] = [[$input], $expected,];

            $testSets[] = [new \ArrayIterator([$input]), $expected,];

            // Use the Object Storage only if the input is an object
            if (is_object($input)) {
                $os = new \SplObjectStorage();
                $os->attach($input);
                $testSets[] = [$os, $expected,];

                $os = new ObjectStorage();
                $os->attach($input);
                $testSets[] = [$os, $expected,];
            }
        }

        return $testSets;
    }

    /**
     * @test
     * @param mixed $input
     * @param array $expected
     * @dataProvider extractCollectionDataProvider
     */
    public function extractModelWithCollectionPropertyTest($input, $expected)
    {
        $model = new MyNestedModel();
        $model->setChild($input);

        $result = $this->fixture->extract($model);
        $this->assertArrayHasKey('child', $result);

        $this->assertEquals($expected, $result['child']);
    }

    /**
     * @test
     */
    public function extractRecursiveTest()
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

        $this->assertEquals($expectedOutput, $this->fixture->extract($model));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract($model));
    }

    /**
     * @param int       $currentDepth
     * @param int       $maxDepth
     * @param \DateTime $testDate
     * @return MyNestedModel
     */
    protected function buildNestedModels($currentDepth, $maxDepth, $testDate)
    {
        $model = new MyNestedModel();
        $model->_setProperty('uid', $currentDepth + 1);
        $model->setDate($testDate);

        if ($currentDepth + 1 < $maxDepth) {
            $model->setChild($this->buildNestedModels($currentDepth + 1, $maxDepth, $testDate));
        }

        return $model;
    }

    /**
     * @test
     */
    public function extractShouldRespectDepthLimitTest()
    {
        $maxDepth = 20;
        $currentDepth = 0;
        $testDate = new \DateTime();

        $model = $this->buildNestedModels($currentDepth, $maxDepth, $testDate);

        $expectedOutput = [
            'base'  => 'Base',
            'date'  => $testDate->format(\DateTime::ATOM),
            'child' => [
                'base'  => 'Base',
                'date'  => $testDate->format(\DateTime::ATOM),
                'child' => [
                    'base'  => 'Base',
                    'date'  => $testDate->format(\DateTime::ATOM),
                    'child' => 'http://rest.cundd.net/rest/cundd-rest-tests-my_nested_model/3/child',
                    'uid'   => 3,
                    'pid'   => null,
                ],
                'uid'   => 2,
                'pid'   => null,
            ],

            'uid' => 1,
            'pid' => null,
        ];


        /** @var ObjectProphecy|ConfigurationProviderInterface $configurationProviderProphecy */
        $configurationProviderProphecy = $this->prophesize(ConfigurationProviderInterface::class);

        $this->fixture = new Extractor(
            $configurationProviderProphecy->reveal(),
            $this->prophesize(LoggerInterface::class)->reveal(),
            3
        );

        $this->assertEquals($expectedOutput, $this->fixture->extract($model));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract($model));
    }

    /**
     * @test
     */
    public function extractSelfReferencingRecursiveTest()
    {
        $testDate = new \DateTime();
        $model = new MyNestedModel();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);
        $model->setChild($model);

        $expectedOutput = [
            'base'  => 'Base',
            'date'  => $testDate->format(\DateTime::ATOM),
            'child' => 'http://rest.cundd.net/rest/cundd-rest-tests-my_nested_model/1/child',
            'uid'   => 1,
            'pid'   => null,
        ];

        $this->assertEquals($expectedOutput, $this->fixture->extract($model));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract($model));
    }

    /**
     * @test
     */
    public function extractRecursiveWithObjectStorageTest()
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

        $expectedOutput = $this->getExpectedOutputForRecursion($testDate);
        $this->assertEquals($expectedOutput, $this->fixture->extract($model));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract($model));
    }

    /**
     * @test
     */
    public function extractRecursiveWithArrayTest()
    {
        $testDate = new \DateTime();
        $model = new MyNestedModelWithObjectStorage();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);

        $childModel = new MyNestedModel();
        $childModel->setDate($testDate);
        $childModel->_setProperty('uid', 2);

        $model->setChildren([$model, $childModel]);

        $expectedOutput = $this->getExpectedOutputForRecursion($testDate);

        $this->assertEquals($expectedOutput, $this->fixture->extract($model));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract($model));
    }

    /**
     * @test
     */
    public function extractRecursiveWithArrayIteratorTest()
    {
        $testDate = new \DateTime();
        $model = new MyNestedModelWithObjectStorage();
        $model->setDate($testDate);
        $model->_setProperty('uid', 1);

        $childModel = new MyNestedModel();
        $childModel->setDate($testDate);
        $childModel->_setProperty('uid', 2);

        $model->setChildren(new \ArrayIterator([$model, $childModel]));

        $expectedOutput = $this->getExpectedOutputForRecursion($testDate);

        $this->assertEquals($expectedOutput, $this->fixture->extract($model));

        // Make sure the same result is returned if extract() is invoked again
        $this->assertEquals($expectedOutput, $this->fixture->extract($model));
    }

    /**
     * @test
     */
    public function getNestedModelDataTest()
    {
        $testDate = new \DateTime();
        $model = new MyNestedModel();
        $model->setDate($testDate);

        $properties = $this->fixture->extract($model);
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
        $properties = $this->fixture->extract($model);
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
     * @param $testDate
     * @return array
     */
    protected function getExpectedOutputForRecursion(\DateTimeInterface $testDate)
    {
        return [
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
    }

    private static function prepareClasses()
    {
        self::buildClassIfNotExists(AbstractDomainObject::class);
        self::buildClassIfNotExists(Repository::class);
        self::buildClassIfNotExists(ObjectStorage::class, \SplObjectStorage::class);
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
}
