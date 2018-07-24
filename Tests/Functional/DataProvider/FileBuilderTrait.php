<?php


namespace Cundd\Rest\Tests\Functional\DataProvider;

use PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_Stub_Return;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use TYPO3\CMS\Core\Resource\File;

trait FileBuilderTrait
{
    /**
     * @param Prophet|null $prophet
     * @return File|\PHPUnit_Framework_MockObject_MockObject
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