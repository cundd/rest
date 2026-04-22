<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\DataProvider;

use Prophecy\Argument;
use Prophecy\Prophet;
use TYPO3\CMS\Core\Resource\File;

trait FileBuilderTrait
{
    public function createFileMock(?Prophet $prophet = null): File
    {
        if (null === $prophet) {
            $prophet = new Prophet();
        }
        $originalFileProperties = [
            'identifier' => sha1('testFile' . time()),
            'name'       => 'Original file name',
            'mimeType'   => 'MimeType',
        ];

        $fileProphecy = $prophet->prophesize(File::class);
        $fileProphecy->getProperties()->willReturn($originalFileProperties);
        $fileProphecy->getName()->willReturn($originalFileProperties['name']);
        $fileProphecy->getMimeType()->willReturn($originalFileProperties['mimeType']);
        $fileProphecy->getPublicUrl(Argument::cetera())->willReturn('http://url');
        $fileProphecy->getSize()->willReturn(10);

        return $fileProphecy->reveal();
    }
}
