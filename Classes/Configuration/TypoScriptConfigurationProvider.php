<?php

namespace Cundd\Rest\Configuration;

use Cundd\Rest\SingletonInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * Class TypoScriptConfigurationProvider
 */
class TypoScriptConfigurationProvider implements SingletonInterface, ConfigurationProviderInterface
{
    /**
     * Settings read from the TypoScript
     *
     * @var array
     */
    protected $settings = null;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager;

    /**
     * Returns the setting with the given key
     *
     * @param string $keyPath
     * @param mixed  $defaultValue
     * @return mixed
     */
    public function getSetting($keyPath, $defaultValue = null)
    {
        $matchingSetting = $this->getSettings();

        $keyPathParts = explode('.', $keyPath);
        foreach ($keyPathParts as $key) {
            if (is_array($matchingSetting)) {
                if (isset($matchingSetting[$key . '.'])) {
                    $matchingSetting = $matchingSetting[$key . '.'];
                } elseif (isset($matchingSetting[$key])) {
                    $matchingSetting = $matchingSetting[$key];
                } else {
                    $matchingSetting = null;
                }
            } else {
                $matchingSetting = null;
            }
        }
        if (is_null($matchingSetting) && !is_null($defaultValue)) {
            return $defaultValue;
        }

        return $matchingSetting;
    }

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

    /**
     * Overwrites the settings
     *
     * @param array $settings
     * @internal
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}
