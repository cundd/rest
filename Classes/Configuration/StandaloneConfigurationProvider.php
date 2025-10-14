<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

/**
 * Standalone Configuration Provider
 *
 * @phpstan-import-type Settings from AbstractConfigurationProvider
 */
class StandaloneConfigurationProvider extends AbstractConfigurationProvider
{
    /**
     * Standalone Configuration Provider constructor
     *
     * @param Settings $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }
}
