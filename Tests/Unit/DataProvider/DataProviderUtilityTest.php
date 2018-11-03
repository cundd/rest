<?php

namespace Cundd\Rest\Tests\Unit\DataProvider;

use Cundd\Rest\DataProvider\Utility;

/**
 * Test case for class new \Cundd\Rest\App
 */
class DataProviderUtilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function getClassNamePartsForPathTest()
    {
        $this->assertEquals(['', 'MyExt', 'MyModel'], Utility::getClassNamePartsForResourceType('my_ext-my_model'));
        $this->assertEquals(
            ['Vendor', 'MyExt', 'MyModel'],
            Utility::getClassNamePartsForResourceType('vendor-my_ext-my_model')
        );
        $this->assertEquals(
            ['Vendor', 'MyExt', 'Group\\Model'],
            Utility::getClassNamePartsForResourceType('vendor-my_ext-group-model')
        );
        $this->assertEquals(
            ['Vendor', 'MyExt', 'Group\\MyModel'],
            Utility::getClassNamePartsForResourceType('vendor-my_ext-group-my_model')
        );
        $this->assertEquals(
            ['Vendor', 'MyExt', 'MyGroup\\MyModel'],
            Utility::getClassNamePartsForResourceType('vendor-my_ext-my_group-my_model')
        );
        $this->assertEquals(
            ['MyVendor', 'Ext', 'Group\\Model'],
            Utility::getClassNamePartsForResourceType('my_vendor-ext-group-model')
        );
    }

    /**
     * @test
     */
    public function getPathForClassNameTest()
    {
        $this->assertEquals('my_ext-my_model', Utility::getResourceTypeForClassName('Tx_MyExt_Domain_Model_MyModel'));
        $this->assertEquals('my_ext-my_model', Utility::getResourceTypeForClassName('MyExt\\Domain\\Model\\MyModel'));
        $this->assertEquals(
            'vendor-my_ext-my_model',
            Utility::getResourceTypeForClassName('Vendor\\MyExt\\Domain\\Model\\MyModel')
        );

        $this->assertEquals(
            'my_ext-my_second_model',
            Utility::getResourceTypeForClassName('Tx_MyExt_Domain_Model_MySecondModel')
        );
        $this->assertEquals(
            'my_ext-my_second_model',
            Utility::getResourceTypeForClassName('MyExt\\Domain\\Model\\MySecondModel')
        );
        $this->assertEquals(
            'vendor-my_ext-my_second_model',
            Utility::getResourceTypeForClassName('Vendor\\MyExt\\Domain\\Model\\MySecondModel')
        );

        $this->assertEquals('my_ext-my_model', Utility::getResourceTypeForClassName('MyExt\\MyModel'));
        $this->assertEquals('vendor-my_ext-my_model', Utility::getResourceTypeForClassName('Vendor\\MyExt\\MyModel'));
        $this->assertEquals(
            'vendor-my_ext-group-model',
            Utility::getResourceTypeForClassName('Vendor\\MyExt\\Group\\Model')
        );
        $this->assertEquals(
            'vendor-my_ext-group-my_model',
            Utility::getResourceTypeForClassName('Vendor\\MyExt\\Group\\MyModel')
        );
        $this->assertEquals(
            'vendor-my_ext-my_group-my_model',
            Utility::getResourceTypeForClassName('Vendor\\MyExt\\MyGroup\\MyModel')
        );
        $this->assertEquals(
            'my_vendor-ext-group-model',
            Utility::getResourceTypeForClassName('MyVendor\\Ext\\Group\\Model')
        );
    }

    /**
     * @test
     * @param $resourceType
     * @param $expected
     * @dataProvider normalizeResourceTypeDataProvider
     */
    public function normalizeResourceTypeTest($resourceType, $expected)
    {
        $this->assertEquals($expected, Utility::normalizeResourceType($resourceType));
    }

    /**
     * @return array
     */
    public function normalizeResourceTypeDataProvider()
    {
        return [
            ['Document-MyExt-MyModel', 'document-my_ext-my_model'],
            ['MyExt-MyModel', 'my_ext-my_model'],
            ['MyExt-*', 'my_ext-*'],
            ['GeorgRinger-News-news', 'georg_ringer-news-news'],
            ['GeorgRinger-News-*', 'georg_ringer-news-*'],
        ];
    }

    /**
     * @test
     */
    public function singularizeTest()
    {
        $this->assertEquals('tree', Utility::singularize('trees'));
        $this->assertEquals('friend', Utility::singularize('friends'));
        $this->assertEquals('hobby', Utility::singularize('hobbies'));

        $this->assertEquals('Tree', Utility::singularize('Trees'));
        $this->assertEquals('Friend', Utility::singularize('Friends'));
        $this->assertEquals('Hobby', Utility::singularize('Hobbies'));
    }

    /**
     * @test
     */
    public function registerSingularForPluralTest()
    {
        $singularToPlural = [
            'news'      => 'news',
            'equipment' => 'equipment',
            'species'   => 'species',
            'series'    => 'series',
            'News'      => 'News',
            'Equipment' => 'Equipment',
            'Species'   => 'Species',
            'Series'    => 'Series',
            'Singular'  => 'Plural',
        ];
        foreach ($singularToPlural as $singular => $plural) {
            Utility::registerSingularForPlural($singular, $plural);
        }

        $this->assertEquals('tree', Utility::singularize('trees'));
        $this->assertEquals('friend', Utility::singularize('friends'));
        $this->assertEquals('hobby', Utility::singularize('hobbies'));
        $this->assertEquals('Tree', Utility::singularize('Trees'));
        $this->assertEquals('Friend', Utility::singularize('Friends'));
        $this->assertEquals('Hobby', Utility::singularize('Hobbies'));
        $this->assertEquals('Singular', Utility::singularize('Plural'));

        foreach ($singularToPlural as $singular => $plural) {
            $this->assertEquals($singular, Utility::singularize($plural));
        }
    }
}
