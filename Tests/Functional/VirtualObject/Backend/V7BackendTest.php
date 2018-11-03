<?php


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

    public function objectDataByQueryDataProvider()
    {
        return array_merge(
            parent::objectDataByQueryDataProvider(),
            [
                [
                    [
                        'title' => [
                            'doNotEscapeValue' => 'title',
                            'value'            => "'Test entry' and content_time = '1395678480'",
                        ],
                    ],
                    self::$testData,
                ],
            ]
        );
    }

    public function emptyResultQueryDataProvider()
    {
        return array_merge(
            parent::emptyResultQueryDataProvider(),
            [
                [
                    [
                        'title' => [
                            'doNotEscapeValue' => 'title',
                            'value'            => "'Test entry' and content_time = '" . time() . "'",
                        ],
                    ],
                    self::$testData,
                ],
            ]
        );
    }
}
