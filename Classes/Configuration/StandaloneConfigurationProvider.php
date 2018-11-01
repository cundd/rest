<?php

namespace Cundd\Rest\Configuration;

/**
 * Standalone Configuration Provider
 */
class StandaloneConfigurationProvider extends AbstractConfigurationProvider
{
    /**
     * Standalone Configuration Provider constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }
}
