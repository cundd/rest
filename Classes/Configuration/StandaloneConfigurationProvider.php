<?php

namespace Cundd\Rest\Configuration;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

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
