<?php

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Cundd\Rest\DataProvider\DataProvider;
use Cundd\Rest\Tests\Functional\AbstractCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * Test case for class file related Data Provider functions
 */
class FileDataProviderTest extends AbstractCase
{
    use FileBuilderTrait;
    use DomainModelProphetTrait;

    /**
     * @var \Cundd\Rest\DataProvider\DataProviderInterface
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->objectManager->get(DataProvider::class);
    }

    public function tearDown()
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @param array $fileReferenceProperties
     * @return FileReference
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

        /** @var ResourceFactory|ObjectProphecy $factoryProphecy */
        $factoryProphecy = $this->prophesize(ResourceFactory::class);
        /** @var string|Argument $stringArg */
        $stringArg = Argument::type('string');
        $factoryProphecy->getFileObject($stringArg, Argument::cetera())->willReturn($originalFileMock);

        return new FileReference($fileReferenceProperties, $factoryProphecy->reveal());
    }

    /**
     * @test
     */
    public function getModelDataForModelWithFileReferenceTest()
    {
        $testModel = $this->createDomainModelFixture(
            [
                'title' => 'Test',
                'file'  => $this->createFileReferenceMock(),
            ]
        );

        $result = $this->fixture->getModelData($testModel);
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
    public function getModelDataForModelWithFileReferenceAndDataTest()
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

        $result = $this->fixture->getModelData($testModel);
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
    public function getModelDataForFileReferenceTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileReferenceMock();

        $result = $this->fixture->getModelData($testModel);
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
    public function getModelDataForFileReferenceWithDataTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileReferenceMock(
            [
                'title'       => 'My title',
                'description' => 'File description',
                'uid'         => 0,
            ]
        );

        $result = $this->fixture->getModelData($testModel);
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
    public function getModelDataForFileTest()
    {
        /** @var object $testModel */
        $testModel = $this->createFileMock();

        $result = $this->fixture->getModelData($testModel);
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
    public function getModelDataForModelWithFileTest()
    {
        /** @var object $testModel */
        $testModel = $this->createDomainModelFixture(
            [
                'title' => 'Test',
                'file'  => $this->createFileMock(),
            ]
        );

        $result = $this->fixture->getModelData($testModel);
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
