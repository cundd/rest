<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.12.14
 * Time: 12:45
 */

namespace Cundd\Rest\Test;
require_once __DIR__ . '/Bootstrap.php';

class AbstractCase extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
    }
}
