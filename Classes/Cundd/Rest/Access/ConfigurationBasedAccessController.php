<?php

namespace Cundd\Rest\Access;
use Cundd\Rest\Access\Exception\InvalidConfigurationException;

/**
 * The class determines the access for the current request
 *
 * @package Cundd\Rest\Access
 */
class ConfigurationBasedAccessController extends AbstractAccessController {
	/**
	 * The request want's to read data
	 */
	const ACCESS_METHOD_READ = 'read';

	/**
	 * The request want's to write data
	 */
	const ACCESS_METHOD_WRITE = 'write';

	/**
	 * Specifies if the request wants to write data
	 * @var boolean
	 */
	protected $write = -1;

	/**
	 * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
	 */
	protected $configurationProvider;

	/**
	 * Inject the configuration provider
	 * @param \Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider
	 */
	public function injectConfigurationProvider(\Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider) {
		$this->configurationProvider = $configurationProvider;
	}

	/**
	 * Returns if the current request has access to the requested resource
	 * @return AccessControllerInterface::ACCESS
	 */
	public function getAccess() {
		$configurationKey = self::ACCESS_METHOD_READ;
		$configuration = $this->getConfigurationForCurrentPath();
		if ($this->isWrite()) {
			$configurationKey = self::ACCESS_METHOD_WRITE;
		}

		// Throw an exception if the configuration is not complete
		if (!isset($configuration[$configurationKey])) {
			throw new InvalidConfigurationException($configurationKey . ' configuration not set', 1376826223);
		}

		$access = $configuration[$configurationKey];
		if ($access === AccessControllerInterface::ACCESS_REQUIRE_LOGIN) {
			return $this->checkAuthentication();
		}
		return $access;
	}

	/**
	 * Returns if the given request needs authentication
	 *
	 * @return bool
	 * @throws Exception\InvalidConfigurationException
	 */
	public function requestNeedsAuthentication() {
		$configurationKey = self::ACCESS_METHOD_READ;
		$configuration = $this->getConfigurationForCurrentPath();
		if ($this->isWrite()) {
			$configurationKey = self::ACCESS_METHOD_WRITE;
		}

		// Throw an exception if the configuration is not complete
		if (!isset($configuration[$configurationKey])) {
			throw new InvalidConfigurationException($configurationKey . ' configuration not set', 1376826223);
		}

		$access = $configuration[$configurationKey];
		if ($access === AccessControllerInterface::ACCESS_REQUIRE_LOGIN) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Sets the current request
	 *
	 * @param \Cundd\Rest\Request $request
	 */
	public function setRequest(\Cundd\Rest\Request $request) {
		parent::setRequest($request);
		$this->write = -1;
	}


	/**
	 * Returns if the request wants to write data
	 * @return bool
	 */
	public function isWrite() {
		if ($this->write === -1) {
			$this->write = !in_array(strtoupper($this->request->method()), array('GET', 'HEAD'));
//			$this->write = in_array(strtoupper($this->request->method()), array('POST', 'PUT', 'DELETE', 'PATCH'));
		}
		return $this->write;
	}

	/**
	 * Returns the configuration matching the current request's path
	 * @return string
	 * @throws \UnexpectedValueException if the request is not set
	 */
	public function getConfigurationForCurrentPath() {
		if (!$this->request) {
			throw new \UnexpectedValueException('The request isn\'t set', 1376816053);
		}
		return $this->getConfigurationForPath($this->request->path());
	}

	/**
	 * Returns the configuration matching the given request path
	 * @param string $path
	 * @return string
	 */
	public function getConfigurationForPath($path) {
		$configuredPaths = $this->getConfiguredPaths();
		$matchingConfiguration = array();

		foreach ($configuredPaths as $configuration) {
			$currentPath = $configuration['path'];

			$currentPathPattern = str_replace('*', '\w*', str_replace('?', '\w', $currentPath));
			$currentPathPattern = "!$currentPathPattern!";
			if ($currentPath === 'all' && !$matchingConfiguration) {
				$matchingConfiguration = $configuration;
			} else if (preg_match($currentPathPattern, $path)) {
				$matchingConfiguration = $configuration;
			}
		}
		return $matchingConfiguration;
	}

	/**
	 * Returns the paths configured in the settings
	 * @return array
	 */
	public function getConfiguredPaths() {
		$settings = $this->configurationProvider->getSettings();
		if (isset($settings['paths']) && is_array($settings['paths'])) {
			return $settings['paths'];
		}
		return isset($settings['paths.']) ? $settings['paths.'] : array();
	}
}