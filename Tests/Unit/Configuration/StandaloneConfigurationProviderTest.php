<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Configuration;

use Cundd\Rest\Configuration\AbstractConfigurationProvider;
use Cundd\Rest\Configuration\StandaloneConfigurationProvider;

/**
 * @phpstan-import-type Settings from AbstractConfigurationProvider
 */
class StandaloneConfigurationProviderTest extends AbstractConfigurationProviderCase
{
    /**
     * @param Settings $settings
     */
    public function getConfigurationProviderToTest(array $settings): StandaloneConfigurationProvider
    {
        return new StandaloneConfigurationProvider($settings);
    }
}
