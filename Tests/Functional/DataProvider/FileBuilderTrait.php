<?php


namespace Cundd\Rest\Tests\Functional\DataProvider;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use TYPO3\CMS\Core\Resource\File;

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
}