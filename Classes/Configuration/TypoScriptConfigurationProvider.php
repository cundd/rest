<?php

declare(strict_types=1);

namespace Cundd\Rest\Configuration;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * Class TypoScriptConfigurationProvider
 */
class TypoScriptConfigurationProvider extends AbstractConfigurationProvider
{
    protected ConfigurationManager $configurationManager;

    public function injectConfigurationManager(ConfigurationManager $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Returns the settings read from the TypoScript
     *
     * @return array
     */
    public function getSettings(): array
    {
        if ($this->settings === null) {
            $this->settings = [];

            $typoScript = $this->configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            if (isset($typoScript['plugin.']['tx_rest.']['settings.'])) {
                $this->settings = $typoScript['plugin.']['tx_rest.']['settings.'];
            }
        }

        return $this->settings;
    }
}
