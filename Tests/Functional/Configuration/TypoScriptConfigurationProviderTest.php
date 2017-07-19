<?php


/**
 * @author COD
 * Created 14.09.15 15:49
 */

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
        if (count($this->fixture->getSettings()) > 0) {
            $this->assertInternalType('array', $this->fixture->getSetting('paths'));
            $this->assertInternalType('array', $this->fixture->getSetting('paths.1'));
            $this->assertEquals('all', $this->fixture->getSetting('paths.1.path'));
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
