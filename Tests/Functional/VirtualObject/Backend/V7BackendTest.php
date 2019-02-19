<?php
declare(strict_types=1);


namespace Cundd\Rest\Tests\Functional\VirtualObject\Backend;

use Cundd\Rest\VirtualObject\Persistence\Backend\V7Backend;

class V7BackendTest extends AbstractBackendTest
{
    public function setUp()
    {
        parent::setUp();
        if (isset($GLOBALS['TYPO3_DB'])) {
            $this->fixture = new V7Backend($GLOBALS['TYPO3_DB']);
        } else {
            $this->markTestSkipped('`$GLOBALS[\'TYPO3_DB\']` is not set');
        }
    }
}
