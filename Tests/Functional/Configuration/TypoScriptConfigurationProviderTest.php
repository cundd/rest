<?php

namespace Cundd\Rest\Tests\Functional\Configuration;

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
        $this->fixture = $this->objectManager->get(TypoScriptConfigurationProvider::class);
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
            $this->assertTrue(isset($settings['paths']) || isset($settings['paths.']));
        }
    }

    /**
     * @test
     */
    public function getSettingTest()
    {
        $settings = $this->fixture->getSettings();
        $this->assertInternalType('array', $settings);
        if (count($settings) > 0) {
            $this->assertInternalType('array', $this->fixture->getSetting('paths'));
            $this->assertInternalType('array', $this->fixture->getSetting('paths.all'));
            $this->assertEquals('all', $this->fixture->getSetting('paths.all.path'));
        }
    }

    /**
     * @test
     */
    public function getSettingDefaultTest()
    {
        $this->assertEquals('defaultValue', $this->fixture->getSetting('paths.NO.path', 'defaultValue'));
    }
}
