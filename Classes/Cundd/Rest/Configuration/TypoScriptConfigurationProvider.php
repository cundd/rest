<?php
namespace Cundd\Rest\Configuration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * Class TypoScriptConfigurationProvider
 * @package Cundd\Rest\Configuration
 */
class TypoScriptConfigurationProvider implements SingletonInterface{
	/**
	 * Settings read from the TypoScript
	 * @var array
	 */
	protected $settings = NULL;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * Returns the setting with the given key
	 * @param string $keyPath
	 * @return mixed
	 */
	public function getSetting($keyPath) {
		$matchingSetting = $this->getSettings();

		$keyPathParts = explode('.', $keyPath);
		foreach ($keyPathParts as $key) {
			if (is_array($matchingSetting)) {
				if (isset($matchingSetting[$key . '.'])) {
					$matchingSetting = $matchingSetting[$key . '.'];
				} else if (isset($matchingSetting[$key])) {
					$matchingSetting = $matchingSetting[$key];
				} else {
					$matchingSetting = NULL;
				}
			} else {
				$matchingSetting = NULL;
			}
		}
		return $matchingSetting;
	}

	/**
	 * Returns the settings read from the TypoScript
	 * @return array
	 */
	public function getSettings() {
		if ($this->settings === NULL) {
			$this->settings = array();

			$typoScript = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			if (isset($typoScript['plugin.'])
				&& isset($typoScript['plugin.']['tx_rest.'])
				&& isset($typoScript['plugin.']['tx_rest.']['settings.'])) {
				$this->settings = $typoScript['plugin.']['tx_rest.']['settings.'];
			}
		}
		return $this->settings;
	}

	/**
	 * Overwrites the settings
	 * @param array $settings
	 * @internal
	 */
	public function setSettings($settings) {
		$this->settings = $settings;
	}
}