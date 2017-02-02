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

namespace Cundd\Rest\Tests\Functional\Core;


use Cundd\Rest\Access\ConfigurationBasedAccessController;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\ObjectManager;
use Cundd\Rest\Tests\Functional\AbstractCase;

class ConfigurationBasedAccessControllerTest extends AbstractCase
{
    /**
     * @var \Cundd\Rest\Access\ConfigurationBasedAccessController
     */
    protected $fixture;

    public function setUp()
    {
        parent::setUp();
        /** @var TypoScriptConfigurationProvider $configurationProvider */
        $configurationProvider = $this->objectManager->get(
            'Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider'
        );
        $settings = [
            'paths' => [
                'all'             => [
                    'path'  => 'all',
                    'read'  => 'allow',
                    'write' => 'deny',
                ],
                'my_ext-my_model' => [
                    'path'  => 'my_ext-my_model',
                    'read'  => 'allow',
                    'write' => 'allow',
                ],
                'my_secondext-*'  => [
                    'path'  => 'my_secondext-*',
                    'read'  => 'deny',
                    'write' => 'allow',
                ],
                'vendor-my_third_ext-model'  => [
                    'read'  => 'deny',
                    'write' => 'allow',
                ],
                'vendor-my_fourth_ext-model.'  => [
                    'read'  => 'deny',
                    'write' => 'allow',
                ],
            ],
        ];
        $configurationProvider->setSettings($settings);

        /** @var ObjectManager $restObjectManager */
        $restObjectManager = $this->objectManager->get(ObjectManager::class);
        $this->fixture = new ConfigurationBasedAccessController($configurationProvider, $restObjectManager);
    }

    /**
     * @test
     */
    public function getDefaultConfigurationForPathTest()
    {
        $uri = 'my_ext-my_default_model/1/';
        $request = $this->buildRequestWithUri($uri);
        $testConfiguration = array(
            'path'  => 'all',
            'read'  => 'allow',
            'write' => 'deny',
        );
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutWildcardTest()
    {
        $uri = 'my_ext-my_model/3/';
        $request = $this->buildRequestWithUri($uri);
        $testConfiguration = array(
            'path'  => 'my_ext-my_model',
            'read'  => 'allow',
            'write' => 'allow',
        );
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationTest()
    {
        $uri = 'vendor-my_third_ext-model/3/';
        $request = $this->buildRequestWithUri($uri);
        $testConfiguration = array(
            'path'  => 'vendor-my_third_ext-model',
            'read'  => 'deny',
            'write' => 'allow',
        );
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithoutExplicitPathConfigurationWithDotTest()
    {
        $uri = 'vendor-my_fourth_ext-model/3/';
        $request = $this->buildRequestWithUri($uri);
        $testConfiguration = array(
            'path'  => 'vendor-my_fourth_ext-model',
            'read'  => 'deny',
            'write' => 'allow',
        );
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType($request->getResourceType()));
        $this->assertEquals($testConfiguration, $configuration);
    }

    /**
     * @test
     */
    public function getConfigurationForPathWithWildcardTest()
    {
        $testConfiguration = array(
            'path'  => 'my_secondext-*',
            'read'  => 'deny',
            'write' => 'allow',
        );
        $configuration = $this->fixture->getConfigurationForResourceType(new ResourceType('my_secondext-my_model'));
        $this->assertEquals($testConfiguration, $configuration);
    }
}
