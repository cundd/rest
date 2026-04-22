<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\DataProvider;

use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Request\ResourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test case for class new \Cundd\Rest\App
 */
class DataProviderUtilityTest extends TestCase
{
    #[Test]
    public function getClassNamePartsForPathTest(): void
    {
        $this->assertEquals(
            ['', 'MyExt', 'MyModel'],
            Utility::getClassNamePartsForResourceType(new ResourceType('my_ext-my_model'))
        );
        $this->assertEquals(
            ['Vendor', 'MyExt', 'MyModel'],
            Utility::getClassNamePartsForResourceType(new ResourceType('vendor-my_ext-my_model'))
        );
        $this->assertEquals(
            ['Vendor', 'MyExt', 'Group\\Model'],
            Utility::getClassNamePartsForResourceType(new ResourceType('vendor-my_ext-group-model'))
        );
        $this->assertEquals(
            ['Vendor', 'MyExt', 'Group\\MyModel'],
            Utility::getClassNamePartsForResourceType(new ResourceType('vendor-my_ext-group-my_model'))
        );
        $this->assertEquals(
            ['Vendor', 'MyExt', 'MyGroup\\MyModel'],
            Utility::getClassNamePartsForResourceType(new ResourceType('vendor-my_ext-my_group-my_model'))
        );
        $this->assertEquals(
            ['MyVendor', 'Ext', 'Group\\Model'],
            Utility::getClassNamePartsForResourceType(new ResourceType('my_vendor-ext-group-model'))
        );
    }

    #[Test]
    public function getPathForClassNameTest(): void
    {
        $this->assertEquals(
            'my_ext-my_model',
            Utility::getResourceTypeForClassName('Tx_MyExt_Domain_Model_MyModel')
        );
        $this->assertEquals(
            'my_ext-my_model',
            Utility::getResourceTypeForClassName('MyExt\\Domain\\Model\\MyModel')
        );
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

        $this->assertEquals(
            'my_ext-my_model',
            Utility::getResourceTypeForClassName('MyExt\\MyModel')
        );
        $this->assertEquals(
            'vendor-my_ext-my_model',
            Utility::getResourceTypeForClassName('Vendor\\MyExt\\MyModel')
        );
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

    #[Test]
    #[DataProvider('normalizeResourceTypeDataProvider')]
    public function normalizeResourceTypeTest(string $resourceType, string $expected): void
    {
        $this->assertEquals($expected, Utility::normalizeResourceType($resourceType));
    }

    /**
     * @return array{0:string,1:string}[]
     */
    public static function normalizeResourceTypeDataProvider(): array
    {
        return [
            ['Document-MyExt-MyModel', 'document-my_ext-my_model'],
            ['MyExt-MyModel', 'my_ext-my_model'],
            ['MyExt-*', 'my_ext-*'],
            ['GeorgRinger-News-news', 'georg_ringer-news-news'],
            ['GeorgRinger-News-*', 'georg_ringer-news-*'],
            ['georgRinger-News-*', 'georg_ringer-news-*'],
            ['dCorn-Test-*', 'd_corn-test-*'],
            ['D', 'd'],
            ['d', 'd'],
        ];
    }
}
