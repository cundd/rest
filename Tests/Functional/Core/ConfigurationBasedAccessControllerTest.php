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

namespace Cundd\Rest\Test\Core;

require_once __DIR__ . '/../AbstractCase.php';
use Cundd\Rest\Request;
use Cundd\Rest\Test\AbstractCase;


class ConfigurationBasedAccessControllerTest extends AbstractCase {
    /**
     * @var \Cundd\Rest\Access\ConfigurationBasedAccessController
     */
    protected $fixture;

    public function setUp() {
        parent::setUp();
        $configurationProvider = $this->objectManager->get('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider');
        $this->fixture = $this->objectManager->get('Cundd\\Rest\\Access\\ConfigurationBasedAccessController');

        $settings = array(
            'paths' =>
                array(
                    '1.' =>
                        array(
                            'path' => 'all',
                            'read' => 'allow',
                            'write' => 'deny',
                        ),
                    '2.' =>
                        array(
                            'path' => 'my_ext-my_model',
                            'read' => 'allow',
                            'write' => 'allow'
                        ),
                    '3.' =>
                        array(
                            'path' => 'my_secondext-*',
                            'read' => 'deny',
                            'write' => 'allow',
                        )
                )
        );
        $configurationProvider->setSettings($settings);

        $request = new Request(NULL, 'my_ext-my_model/4/usergroup');
        $this->fixture->setRequest($request);
    }

    /**
     * @test
     */
    public function getDefaultConfigurationForPathTest() {
        $uri = 'my_ext-my_default_model/1/';
        $request = new Request(NULL, $uri);
        $testConfiguration = array(
            'path' => 'all',
            'read' => 'allow',
            'write' => 'deny',
        );
        $configuration = $this->fixture->getConfigurationForPath($request->path());
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutWildcardTest() {
        $uri = 'my_ext-my_model/3/';
        $request = new Request(NULL, $uri);
        $testConfiguration = array(
            'path' => 'my_ext-my_model',
            'read' => 'allow',
            'write' => 'allow'
        );
        $configuration = $this->fixture->getConfigurationForPath($request->path());
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithWildcardTest() {
        $uri = 'my_secondext-my_model/34/';
        $request = new Request(NULL, $uri);
        $testConfiguration = array(
            'path' => 'my_secondext-*',
            'read' => 'deny',
            'write' => 'allow'
        );
        $configuration = $this->fixture->getConfigurationForPath($request->path());
        $this->assertEquals($testConfiguration, $configuration);
    }

}
