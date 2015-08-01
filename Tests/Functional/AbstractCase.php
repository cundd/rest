<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.12.14
 * Time: 12:45
 */

namespace Cundd\Rest\Test;
require_once __DIR__ . '/../Bootstrap.php';

class AbstractCase extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    public function setUp() {
        parent::setUp();
        $this->objectManager = new \TYPO3\CMS\Extbase\Object\ObjectManager();
    }
}
