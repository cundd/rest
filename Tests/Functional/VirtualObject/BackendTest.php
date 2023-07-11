<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\VirtualObject;

use Cundd\Rest\Tests\Functional\VirtualObject\Backend\AbstractBackendTest;
use Cundd\Rest\VirtualObject\Persistence\BackendInterface;

class BackendTest extends AbstractBackendTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->fixture = $this->getContainer()->get(BackendInterface::class);
    }
}
