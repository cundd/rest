<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;

trait FileBuilderTrait
{
    /**
     * @param Prophet|null $prophet
     * @return File
     */
    public function createFileMock(Prophet $prophet = null)
    {
        if (null === $prophet) {
            $prophet = new Prophet();
        }
        $originalFileProperties = [
            'identifier' => sha1('testFile' . time()),
            'name'       => 'Original file name',
            'mimeType'   => 'MimeType',
        ];

        /** @var File|ObjectProphecy $fileProphecy */
        $fileProphecy = $prophet->prophesize(File::class);
        $fileProphecy->getProperties()->willReturn($originalFileProperties);
        $fileProphecy->getName()->willReturn($originalFileProperties['name']);
        $fileProphecy->getMimeType()->willReturn($originalFileProperties['mimeType']);
        $fileProphecy->getPublicUrl(Argument::cetera())->willReturn('http://url');
        $fileProphecy->getSize()->willReturn(10);

        return $fileProphecy->reveal();
    }

    /**
     * @param array        $fileReferenceProperties
     * @param Prophet|null $prophet
     * @return FileReference
     */
    public function createFileReferenceMock(array $fileReferenceProperties = [], Prophet $prophet = null)
    {
        if (null === $prophet) {
            $prophet = new Prophet();
        }
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
        $factoryProphecy = $prophet->prophesize(ResourceFactory::class);
        $factoryProphecy->getFileObject(Argument::cetera())->willReturn($originalFileMock);

        return new FileReference($fileReferenceProperties, $factoryProphecy->reveal());
    }
}
