<?php

namespace Cundd\Rest\Tests\Functional\VirtualObject;

use Cundd\Rest\Tests\Functional\VirtualObject\Backend\AbstractBackendTest;
use Cundd\Rest\VirtualObject\Persistence\BackendInterface;

class BackendTest extends AbstractBackendTest
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = $this->objectManager->get(BackendInterface::class);
    }
}
