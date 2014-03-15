<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 12.09.13
 * Time: 20:44
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest;


use Cundd\Rest\DataProvider\Utility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use \TYPO3\CMS\Extbase\Object\ObjectManager as BaseObjectManager;



class ObjectManager extends BaseObjectManager implements ObjectManagerInterface, SingletonInterface {
	/**
	 * @var \Cundd\Rest\App
	 */
	protected $dispatcher;

	/**
	 * @var \Cundd\Rest\DataProvider\DataProviderInterface
	 */
	protected $dataProvider;

	/**
	 * @var \Cundd\Rest\Authentication\AuthenticationProviderInterface
	 */
	protected $authenticationProvider;

	/**
	 * Configuration provider
	 * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
	 */
	protected $configurationProvider;

	/**
	 * @var \Cundd\Rest\Access\AccessControllerInterface
	 */
	protected $accessController;

	/**
	 * Injects the dispatcher
	 * @param \Cundd\Rest\App $dispatcher
	 */
	public function setDispatcher($dispatcher) {
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Returns the configuration provider
	 * @return \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
	 */
	public function getConfigurationProvider() {
		if (!$this->configurationProvider) {
			$this->configurationProvider = $this->get('Cundd\\Rest\\Configuration\\TypoScriptConfigurationProvider');
		}
		return $this->configurationProvider;
	}

	/**
	 * Returns the data provider
	 *
	 * @throws Exception if the dispatcher is not set
	 * @return \Cundd\Rest\DataProvider\DataProviderInterface
	 */
	public function getDataProvider() {
		if (!$this->dataProvider) {
			/** @var App $dispatcher */
			$dispatcher = $this->dispatcher ? $this->dispatcher : App::getSharedDispatcher();
			list($vendor, $extension,) = Utility::getClassNamePartsForPath($dispatcher->getPath());

			// Check if an extension provides a Data Provider
			$dataProviderClass  = 'Tx_' . $extension . '_Rest_DataProvider';
			if (!class_exists($dataProviderClass)) {
				$dataProviderClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\DataProvider';
			}
			// Get the specific builtin Data Provider
			if (!class_exists($dataProviderClass)) {
				$dataProviderClass = 'Cundd\\Rest\\DataProvider\\' . $extension . 'DataProvider';
				// Get the default Data Provider
				if (!class_exists($dataProviderClass)) {
					$dataProviderClass = 'Cundd\\Rest\\DataProvider\\DataProviderInterface';
				}
			}
			$this->dataProvider = $this->get($dataProviderClass);
		}
		return $this->dataProvider;
	}

	/**
	 * Returns the Authentication Provider
	 * @return \Cundd\Rest\Authentication\AuthenticationProviderInterface
	 */
	public function getAuthenticationProvider() {
		if (!$this->authenticationProvider) {
			/** @var App $dispatcher */
			$dispatcher = $this->dispatcher ? $this->dispatcher : App::getSharedDispatcher();
			list($vendor, $extension,) = Utility::getClassNamePartsForPath($dispatcher->getPath());

			// Check if an extension provides a Authentication Provider
			$authenticationProviderClass  = 'Tx_' . $extension . '_Rest_AuthenticationProvider';
			if (!class_exists($authenticationProviderClass)) {
				$authenticationProviderClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AuthenticationProvider';
			}

			// Use the configuration based Authentication Provider
			if (!class_exists($authenticationProviderClass)) {
				$authenticationProviderClass = 'Cundd\\Rest\\Authentication\\BasicAuthenticationProvider';
			}
			$this->authenticationProvider = $this->get($authenticationProviderClass);
			$this->authenticationProvider->setRequest($dispatcher->getRequest());
		}
		return $this->authenticationProvider;
	}

	/**
	 * Returns teh Access Controller
	 * @return \Cundd\Rest\Access\AccessControllerInterface
	 */
	public function getAccessController() {
		if (!$this->accessController) {
			list($vendor, $extension,) = Utility::getClassNamePartsForPath($this->dispatcher->getPath());

			// Check if an extension provides a Authentication Provider
			$accessControllerClass  = 'Tx_' . $extension . '_Rest_AccessController';
			if (!class_exists($accessControllerClass)) {
				$accessControllerClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Rest\\AccessController';
			}

			// Use the configuration based Authentication Provider
			if (!class_exists($accessControllerClass)) {
				$accessControllerClass = 'Cundd\\Rest\\Access\\ConfigurationBasedAccessController';
			}
			$this->accessController = $this->get($accessControllerClass);
			$this->accessController->setRequest($this->dispatcher->getRequest());
		}
		return $this->accessController;
	}

	/**
	 * Returns the correct class name of the Persistence Manager for the current TYPO3 version
	 *
	 * @return string
	 */
	static public function getPersistenceManagerClassName() {
		if (version_compare(TYPO3_version, '6.0.0') < 0) {
			return 'Tx_Extbase_Persistence_Manager';
		}
		return 'TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface';
	}

	/**
	 * Returns the Cache instance
	 *
	 * @return \Cundd\Rest\Cache\Cache
	 */
	public function getCache() {
		return $this->get('Cundd\\Rest\\Cache\\Cache');
	}

	/**
	 * Resets the managed objects
	 */
	public function reassignRequest() {
		$request = $this->dispatcher->getRequest();
		if ($this->authenticationProvider) {
			$this->authenticationProvider->setRequest($request);
		}
		if ($this->accessController) {
			$this->accessController->setRequest($request);
		}
	}
}