<?php
/*
 *  Copyright notice
 *
 *  (c) 2015 Andreas Thurnheer-Meier <tma@iresults.li>, iresults
 *  Daniel Corn <cod@iresults.li>, iresults
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

/**
 * @author COD
 * Created 14.09.15 15:49
 */

namespace Cundd\Rest\Tests\Functional\Configuration;

require_once __DIR__ . '/../AbstractCase.php';

use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Tests\Functional\AbstractCase;

class TypoScriptConfigurationProviderTest extends AbstractCase
{
    /**
     * @var TypoScriptConfigurationProvider
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        $this->fixture = $this->objectManager->get('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider');
    }

    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function getSettingsTest()
    {
        $settings = $this->fixture->getSettings();
        $this->assertInternalType('array', $settings);

        if (count($this->fixture->getSettings()) !== 0) {
            $this->assertArrayHasKey('paths.', $settings);
        }
    }

    /**
     * @test
     */
    public function getSettingTest()
    {
        if (count($this->fixture->getSettings()) === 0) {
            $this->markTestSkipped('ext_typoscript_setup.txt not loaded');
        }

        $this->assertInternalType('array', $this->fixture->getSetting('paths'));
        $this->assertInternalType('array', $this->fixture->getSetting('paths.1'));
        $this->assertEquals('all', $this->fixture->getSetting('paths.1.path'));
    }

    /**
     * @test
     */
    public function getSettingDefaultTest()
    {
        $this->assertEquals('defaultValue', $this->fixture->getSetting('paths.NO.path', 'defaultValue'));
    }
}
