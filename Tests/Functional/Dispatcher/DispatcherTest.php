<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest\Tests\Functional\Dispatcher;

use Cundd\Rest\Dispatcher;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\RequestFactoryInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;


/**
 * Test case for class new \Cundd\Rest\App
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 *
 * @author Daniel Corn <cod@(c) 2014 Daniel Corn <info@cundd.net>, cundd.li>
 */
class DispatcherTest extends AbstractCase
{
    /**
     * @var \Cundd\Rest\Dispatcher
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        require_once __DIR__ . '/../../FixtureClasses.php';
        $restObjectManager = $this->objectManager->get(ObjectManager::class);
        $this->fixture = new Dispatcher($restObjectManager, false);
    }

    public function tearDown()
    {
        /** @var RequestFactoryInterface $requestFactory */
        if ($this->objectManager) {
            $requestFactory = $this->objectManager->get('Cundd\\Rest\\RequestFactory');
            $requestFactory->resetRequest();
        }
        unset($this->fixture);
        unset($_GET['u']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function dummyTest()
    {
        $this->markTestIncomplete();
    }
}
