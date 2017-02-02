<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Cundd\Rest\Tests\MyModel;
use Cundd\Rest\Tests\MyNestedJsonSerializeModel;
use Cundd\Rest\Tests\MyNestedModel;
use Cundd\Rest\Tests\MyNestedModelWithObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;



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

        $this->fixture = $this->objectManager->get('Cundd\\Rest\\DataProvider\\DataProvider');
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
        $data = array('some' => 'Data');

        $propertyMapperMock = $this->getMockBuilder('\\TYPO3\\CMS\\Extbase\\Property\\PropertyMapper')
            ->setMethods(array('convert'))
            ->getMock();
        $propertyMapperMock
            ->expects($this->once())
            ->method('convert')
            ->with(
                $this->equalTo($data),
                $this->equalTo('AVendor\\AnotherExt\\Domain\\Model\\MyModel'),
                $this->isInstanceOf('\\TYPO3\\CMS\\Extbase\\Property\\PropertyMappingConfigurationInterface')
            );

        $this->injectPropertyIntoObject($propertyMapperMock, 'propertyMapper', $this->fixture);
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

        $this->createClass(
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
        $model = $this->fixture->getModelWithDataForResourceType(array(), new ResourceType('MyExt-MyModel'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModel', $model);

        $model = $this->fixture->getModelWithDataForResourceType(array(), new ResourceType('my_ext-my_model'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModel', $model);
    }

    /**
     * @test
     */
    public function getNamespacedModelForPathTest()
    {
        $model = $this->fixture->getModelWithDataForResourceType(array(), new ResourceType('MyExt-MySecondModel'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModel', $model);

        $model = $this->fixture->getModelWithDataForResourceType(array(), new ResourceType('my_ext-my_second_model'));
        $this->assertInstanceOf('\\Cundd\\Rest\\Tests\\MyModel', $model);
    }

    /**
     * @test
     */
    public function getNamespacedModelForPathWithVendorTest()
    {
        $model = $this->fixture->getModelWithDataForResourceType(array(), new ResourceType('Vendor-MyExt-MyModel'));
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);

        $model = $this->fixture->getModelWithDataForResourceType(array(), new ResourceType('vendor-my_ext-my_model'));
        $this->assertInstanceOf('\\Vendor\\MyExt\\Domain\\Model\\MyModel', $model);
    }

    /**
     * @test
     */
    public function getModelWithEmptyDataTest()
    {
        $data = array();
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
            array(
                'name'    => 'Initial value',
                'uid'     => null,
                'pid'     => null,
            ),
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

        $expectedOutput = array(
            'base'  => 'Base',
            'date'  => $testDate,
            'child' => array(
                'base'    => 'Base',
                'date'    => $testDate,
                'child'   => 'http://rest.cundd.net/rest/cundd-rest-tests-my_nested_model/1/child',
                'uid'     => 2,
                'pid'     => null,
            ),

            'uid'     => 1,
            'pid'     => null,
        );

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

        $expectedOutput = array(
            'base'  => 'Base',
            'date'  => $testDate,
            'child' => array(
                'uid'     => null,
                'pid'     => null,
                'name'    => 'Initial value',
            ),

            'uid'      => 1,
            'pid'      => null,
            'children' => array(
                0 => 'http://rest.cundd.net/rest/cundd-rest-tests-my_nested_model_with_object_storage/1/', // <- This is $model
                1 => array( // <- This is $childModel
                    'base'    => 'Base',
                    'date'    => $testDate,
                    'uid'     => 2,
                    'pid'     => null,
                    'child'   => array(
                        'name'    => 'Initial value',
                        'uid'     => null,
                        'pid'     => null,

                    ),
                ),
            ),
        );

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
            array(
                'base'    => 'Base',
                'date'    => $testDate,
                'uid'     => null,
                'pid'     => null,
                'child'   => array(
                    'name'    => 'Initial value',
                    'uid'     => null,
                    'pid'     => null,
                ),
            ),
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
            array(
                'base'    => 'Base',
                'child'   => array(
                    'name'    => 'Initial value',
                    'uid'     => null,
                    'pid'     => null,
                ),
            ),
            $properties
        );
    }
}
