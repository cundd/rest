<?php

namespace Cundd\Rest\Configuration;

use Cundd\Rest\DataProvider\Utility;
use Cundd\Rest\Domain\Model\ResourceType;
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

    /**
     * Returns the paths configured in the settings
     *
     * @return ResourceConfiguration[]
     */
    public function getConfiguredResourceTypes()
    {
        $configurationCollection = [];
        foreach ($this->getRawConfiguredResourceTypes() as $path => $configuration) {
            // If no explicit path is configured use the current key
            $resourceType = isset($configuration['path']) ? $configuration['path'] : trim($path, '.');
            $normalizeResourceType = Utility::normalizeResourceType($resourceType);
            $configuration['path'] = $normalizeResourceType;

            $readAccess = isset($configuration[self::ACCESS_METHOD_READ])
                ? new Access($configuration[self::ACCESS_METHOD_READ])
                : Access::denied();
            $writeAccess = isset($configuration[self::ACCESS_METHOD_WRITE])
                ? new Access($configuration[self::ACCESS_METHOD_WRITE])
                : Access::denied();
            $configurationCollection[$normalizeResourceType] = new ResourceConfiguration(
                new ResourceType($normalizeResourceType),
                $readAccess,
                $writeAccess,
                isset($configuration['cacheLifeTime']) ? intval($configuration['cacheLifeTime']) : -1
            );
        }

        return $configurationCollection;
    }

    /**
     * @return array
     */
    private function getRawConfiguredResourceTypes()
    {
        $settings = $this->getSettings();
        if (isset($settings['paths']) && is_array($settings['paths'])) {
            return $settings['paths'];
        }

        return isset($settings['paths.']) ? $settings['paths.'] : [];
    }
}
