<?php

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Cundd\Rest\DataProvider\ExtractorInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use TYPO3\CMS\Core\Resource\FileReference;

/**
 * Test case for class file related Data Provider functions
 *
 * @version   $Id$
 * @copyright Copyright belongs to the respective authors
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author    Daniel Corn <cod@(c) 2014 Daniel Corn <info@cundd.net>, cundd.li>
 */
class ExtractorFileTest extends AbstractCase
{
    /**
     * @var \Cundd\Rest\DataProvider\ExtractorInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->objectManager->get(ExtractorInterface::class);
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
    protected function createDomainModelFixture(array $properties = [])
    {
        /** @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface|object $fixture */
        $fixture = $this->getMockBuilder('TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface')
            ->setMockClassName('Mock_Test_Class')
            ->setMethods(['_getProperties'])
            ->getMockForAbstractClass();

        $fixture->method('_getProperties')->willReturn($properties);

        return $fixture;
    }

    /**
     * @param array $fileReferenceProperties
     * @return \PHPUnit_Framework_MockObject_MockObject|FileReference|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected function createFileReferenceMock(array $fileReferenceProperties = [])
    {
        $fileReferenceProperties = array_merge(
            [
                'uid_local'   => '1467702760',
                'name'        => 'Test name',
                'title'       => 'Test title',
                'description' => 'The original files description',
            ],
            $fileReferenceProperties
        );
        $originalFileMock = $this->createFileMock();

        $factoryMock = $this->getMockObjectGenerator()->getMock(
            '\TYPO3\CMS\Core\Resource\ResourceFactory',
            ['getFileObject']
        );
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
        $originalFileProperties = [
            'identifier' => sha1('testFile' . time()),
            'name'       => 'Original file name',
            'mimeType'   => 'MimeType',
        ];
        /** @var  \TYPO3\CMS\Core\Resource\File|\PHPUnit_Framework_MockObject_MockObject $originalFileMock */
        $originalFileMock = $this->getMockObjectGenerator()->getMock(
            '\TYPO3\CMS\Core\Resource\File',
            [],
            [],
            'Mock_TYPO3_CMS_Core_Resource_File',
            false
        );
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
    public function extractForModelWithFileReferenceTest()
    {
        $testModel = $this->createDomainModelFixture(
            [
                'title' => 'Test',
                'file'  => $this->createFileReferenceMock(),
            ]
        );

        $result = $this->fixture->extract($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            [
                'title' => 'Test',
                'file'  => [
                    'name'         => 'Original file name',
                    'mimeType'     => 'MimeType',
                    'url'          => 'http://url',
                    'size'         => 10,
                    'title'        => 'Test title',
                    'description'  => 'The original files description',
                    'uid'          => 1467702760,
                    'referenceUid' => 0,
                ],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function extractForModelWithFileReferenceAndDataTest()
    {
        $testModel = $this->createDomainModelFixture(
            [
                'title' => 'Test',
                'file'  => $this->createFileReferenceMock(
                    [
                        'title'       => 'My title',
                        'description' => 'File description',
                        'uid'         => 0,
                    ]
                ),
            ]
        );

        $result = $this->fixture->extract($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            [
                'title' => 'Test',
                'file'  => [
                    'name'         => 'Original file name',
                    'mimeType'     => 'MimeType',
                    'url'          => 'http://url',
                    'size'         => 10,
                    'title'        => 'My title',
                    'description'  => 'File description',
                    'uid'          => 1467702760,
                    'referenceUid' => 0,
                ],
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function extractForFileReferenceTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileReferenceMock();

        $result = $this->fixture->extract($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            [
                'name'         => 'Original file name',
                'mimeType'     => 'MimeType',
                'url'          => 'http://url',
                'size'         => 10,
                'title'        => 'Test title',
                'description'  => 'The original files description',
                'uid'          => 1467702760,
                'referenceUid' => 0,
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function extractForFileReferenceWithDataTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileReferenceMock(
            [
                'title'       => 'My title',
                'description' => 'File description',
                'uid'         => 0,
            ]
        );

        $result = $this->fixture->extract($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            [
                'name'         => 'Original file name',
                'mimeType'     => 'MimeType',
                'url'          => 'http://url',
                'size'         => 10,
                'title'        => 'My title',
                'description'  => 'File description',
                'uid'          => 1467702760,
                'referenceUid' => 0,
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function extractForFileTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileMock();

        $result = $this->fixture->extract($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            [
                'name'     => 'Original file name',
                'mimeType' => 'MimeType',
                'url'      => 'http://url',
                'size'     => 10,
            ],
            $result
        );
    }

    /**
     * @test
     */
    public function extractForModelWithFileTest()
    {
        /** @var object $testModel */
        $testModel = $this->createDomainModelFixture(
            [
                'title' => 'Test',
                'file'  => $this->createFileMock(),
            ]
        );

        $result = $this->fixture->extract($testModel);
        $this->assertNotEmpty($result);
        $this->assertEquals(
            [
                'title' => 'Test',
                'file'  => [
                    'name'     => 'Original file name',
                    'mimeType' => 'MimeType',
                    'url'      => 'http://url',
                    'size'     => 10,
                ],
            ],
            $result
        );
    }
}
