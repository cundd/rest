<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Configuration;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Tests\Functional\AbstractCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @phpstan-import-type Settings from \Cundd\Rest\Configuration\AbstractConfigurationProvider
 */
abstract class AbstractConfigurationProviderCase extends AbstractCase
{
    protected ConfigurationProviderInterface $fixture;

    /**
     * @var Settings
     */
    protected array $settings = [
        'paths' => [
            'all' => [
                'path'  => 'all',
                'read'  => 'allow',
                'write' => 'deny',
            ],
            'my_ext-my_model' => [
                'path'  => 'my_ext-my_model',
                'read'  => 'require',
                'write' => 'allow',
            ],
            'my_secondext-*' => [
                'path'  => 'my_secondext-*',
                'read'  => 'deny',
                'write' => 'require',
            ],
        ],
    ];

    public function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function getSettingsTest(): void
    {
        $settings = $this->fixture->getSettings();

        if (0 !== count($this->fixture->getSettings())) {
            $this->assertTrue(isset($settings['paths']) || isset($settings['paths.']));
        }
    }

    #[Test]
    public function getSettingTest(): void
    {
        $settings = $this->fixture->getSettings();
        if (count($settings) > 0) {
            $this->assertIsArray($this->fixture->getSetting('paths'));
            $this->assertIsArray($this->fixture->getSetting('paths.all'));
            $this->assertEquals('all', $this->fixture->getSetting('paths.all.path'));
        }
    }

    #[Test]
    public function getSettingDefaultTest(): void
    {
        $this->assertEquals('defaultValue', $this->fixture->getSetting('paths.NO.path', 'defaultValue'));
    }
}
