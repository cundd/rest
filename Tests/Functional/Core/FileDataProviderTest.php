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

use Cundd\Rest\Tests\Functional\AbstractCase;
use TYPO3\CMS\Core\Resource\FileReference;

require_once __DIR__ . '/../AbstractCase.php';

/**
 * Test case for class file related Data Provider functions
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author Daniel Corn <cod@(c) 2014 Daniel Corn <info@cundd.net>, cundd.li>
 */
class FileDataProviderTest extends AbstractCase
{
    /**
     * @var \Cundd\Rest\DataProvider\DataProviderInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->objectManager->get('Cundd\\Rest\\DataProvider\\DataProvider');
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @param array $properties
     * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
     */
    protected function createDomainModelFixture(array $properties = array())
    {
        /** @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface|object $fixture */
        $fixture = $this->getMockBuilder('TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface')
            ->setMockClassName('Mock_Test_Class')
            ->setMethods(array('_getProperties'))
            ->getMockForAbstractClass();

        $fixture->method('_getProperties')->willReturn($properties);
        return $fixture;
    }

    /**
     * @param array $fileReferenceProperties
     * @return \PHPUnit_Framework_MockObject_MockObject|FileReference|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected function createFileReferenceMock(array $fileReferenceProperties = array())
    {
        $fileReferenceProperties = array(
            'uid_local' => '1467702760',
            'name' => 'Test name',
        ) + $fileReferenceProperties;
        $originalFileMock = $this->createFileMock();

        $factoryMock = $this->getMock('\TYPO3\CMS\Core\Resource\ResourceFactory', array('getFileObject'));
        $factoryMock->expects($this->any())
            ->method('getFileObject')->will(
                $this->returnValue($originalFileMock)
            );

        return new FileReference($fileReferenceProperties, $factoryMock);
    }


    /**
     * @return \TYPO3\CMS\Core\Resource\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFileMock()
    {
        $originalFileProperties = array(
            'identifier' => sha1('testFile' . time()),
            'name' => 'Original file name',
            'mimeType' => 'MimeType',
        );
        $originalFileMock = $this->getMock('\TYPO3\CMS\Core\Resource\File', array(), array(), 'Mock_TYPO3_CMS_Core_Resource_File', false);
        $originalFileMock->expects($this->any())
            ->method('getProperties')
            ->will(
                $this->returnValue($originalFileProperties)
            );
        $originalFileMock->expects($this->any())
            ->method('getName')
            ->will(
                $this->returnValue($originalFileProperties['name'])
            );
        $originalFileMock->expects($this->any())
            ->method('getMimeType')
            ->will(
                $this->returnValue($originalFileProperties['mimeType'])
            );
        $originalFileMock->expects($this->any())
            ->method('getPublicUrl')
            ->will(
                $this->returnValue('http://url')
            );
        $originalFileMock->expects($this->any())
            ->method('getSize')
            ->will(
                $this->returnValue(10)
            );
        return $originalFileMock;
    }


    /**
     * @test
     */
    public function getModelDataForModelWithFileReferenceTest()
    {
        $testModel = $this->createDomainModelFixture(array(
            'title' => 'Test',
            'file' => $this->createFileReferenceMock()
        ));

        $result = $this->fixture->getModelData($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            array(
                'title' => 'Test',
                'file' => array(
                    'name' => 'Original file name',
                    'mimeType' => 'MimeType',
                    'url' => 'http://url',
                    'size' => 10,
                    'title' => '',
                    'description' => '',
                    'uid' => 1467702760,
                    'referenceUid' => 0,
                    '__class' => 'TYPO3\CMS\Core\Resource\FileReference',
                ),
                '__class' => 'Mock_Test_Class'
            ), $result
        );
    }

    /**
     * @test
     */
    public function getModelDataForModelWithFileReferenceAndDataTest()
    {
        $testModel = $this->createDomainModelFixture(array(
            'title' => 'Test',
            'file' => $this->createFileReferenceMock(array(
                'title' => 'My title',
                'description' => 'File description',
                'uid' => 0,
            ))
        ));

        $result = $this->fixture->getModelData($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            array(
                'title' => 'Test',
                'file' => array(
                    'name' => 'Original file name',
                    'mimeType' => 'MimeType',
                    'url' => 'http://url',
                    'size' => 10,
                    'title' => 'My title',
                    'description' => 'File description',
                    'uid' => 1467702760,
                    'referenceUid' => 0,
                    '__class' => 'TYPO3\CMS\Core\Resource\FileReference',
                ),
                '__class' => 'Mock_Test_Class'
            ), $result
        );
    }

    /**
     * @test
     */
    public function getModelDataForFileReferenceTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileReferenceMock();

        $result = $this->fixture->getModelData($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            array(
                'name' => 'Original file name',
                'mimeType' => 'MimeType',
                'url' => 'http://url',
                'size' => 10,
                'title' => '',
                'description' => '',
                'uid' => 1467702760,
                'referenceUid' => 0,
                '__class' => 'TYPO3\CMS\Core\Resource\FileReference',
            ), $result
        );
    }

    /**
     * @test
     */
    public function getModelDataForFileReferenceWithDataTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileReferenceMock(array(
            'title' => 'My title',
            'description' => 'File description',
            'uid' => 0,
        ));

        $result = $this->fixture->getModelData($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            array(
                'name' => 'Original file name',
                'mimeType' => 'MimeType',
                'url' => 'http://url',
                'size' => 10,
                'title' => 'My title',
                'description' => 'File description',
                'uid' => 1467702760,
                'referenceUid' => 0,
                '__class' => 'TYPO3\CMS\Core\Resource\FileReference',
            ), $result
        );
    }

    /**
     * @test
     */
    public function getModelDataForFileTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileMock();

        $result = $this->fixture->getModelData($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            array(
                'name' => 'Original file name',
                'mimeType' => 'MimeType',
                'url' => 'http://url',
                'size' => 10,
                '__class' => 'Mock_TYPO3_CMS_Core_Resource_File',
            ), $result
        );
    }

    /**
     * @test
     */
    public function getModelDataForModelWithFileTest()
    {
        /** @var object $testModel */
        $testModel = $this->createDomainModelFixture(array(
            'title' => 'Test',
            'file' => $this->createFileMock()
        ));


        $result = $this->fixture->getModelData($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            array(
                'title' => 'Test',
                'file' => array(
                    'name' => 'Original file name',
                    'mimeType' => 'MimeType',
                    'url' => 'http://url',
                    'size' => 10,
                    '__class' => 'Mock_TYPO3_CMS_Core_Resource_File',
                ),
                '__class' => 'Mock_Test_Class',
            ), $result
        );
    }
}
