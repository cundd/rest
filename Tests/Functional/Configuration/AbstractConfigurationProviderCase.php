<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Configuration;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;

abstract class AbstractConfigurationProviderCase extends AbstractCase
{
    /**
     * @var ConfigurationProviderInterface
     */
    protected $fixture;

    protected $settings = [
        'paths' => [
            'all'             => [
                'path'  => 'all',
                'read'  => 'allow',
                'write' => 'deny',
            ],
            'my_ext-my_model' => [
                'path'  => 'my_ext-my_model',
                'read'  => 'require',
                'write' => 'allow',
            ],
            'my_secondext-*'  => [
                'path'  => 'my_secondext-*',
                'read'  => 'deny',
                'write' => 'require',
            ],
        ],
    ];

    public function tearDown(): void
    {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getSettingsTest()
    {
        $settings = $this->fixture->getSettings();
        $this->assertIsArray($settings);

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
        $this->assertIsArray($settings);
        if (count($settings) > 0) {
            $this->assertIsArray($this->fixture->getSetting('paths'));
            $this->assertIsArray($this->fixture->getSetting('paths.all'));
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
