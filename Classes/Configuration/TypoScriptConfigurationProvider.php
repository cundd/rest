<?php

namespace Cundd\Rest\Configuration;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * Class TypoScriptConfigurationProvider
 */
class TypoScriptConfigurationProvider extends AbstractConfigurationProvider
{
    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager;

    /**
     * Returns the settings read from the TypoScript
     *
     * @return array
     */
    public function getSettings()
    {
        if ($this->settings === null) {
            $this->settings = [];

            $typoScript = $this->configurationManager->getConfiguration(
                ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            if (isset($typoScript['plugin.'])
                && isset($typoScript['plugin.']['tx_rest.'])
                && isset($typoScript['plugin.']['tx_rest.']['settings.'])
            ) {
                $this->settings = $typoScript['plugin.']['tx_rest.']['settings.'];
            }
        }

        return $this->settings;
    }
}
