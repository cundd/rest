<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 26.03.14
 * Time: 20:34
 */

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\VirtualObject\ConfigurationInterface;
use Cundd\Rest\VirtualObject\Exception\MissingConfigurationException;
use Cundd\Rest\VirtualObject\Persistence\RepositoryInterface;
use Cundd\Rest\VirtualObject\VirtualObject;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * Data Provider for Virtual Objects
 *
 * @package Cundd\Rest\DataProvider
 */
class VirtualObjectDataProvider extends DataProvider {
	/**
	 * @var array<\Cundd\Rest\VirtualObject\ObjectConverter>
	 */
	protected $objectConverterMap = array();

	/**
	 * @var \Cundd\Rest\VirtualObject\ConfigurationFactory
	 * @inject
	 */
	protected $configurationFactory;

	/**
	 * Returns the Object Converter with the currently matching configuration
	 *
	 * @param string $path
	 * @return \Cundd\Rest\VirtualObject\ObjectConverter
	 */
	public function getObjectConverterForPath($path) {
		if (!isset($this->objectConverterMap[$path])) {
			$objectConverter = $this->objectManager->get('Cundd\\Rest\\VirtualObject\\ObjectConverter');
			$objectConverter->setConfiguration($this->getConfigurationForPath($path));

			$this->objectConverterMap[$path] = $objectConverter;
			return $objectConverter;
		}
		return $this->objectConverterMap[$path];
	}

	/**
	 * Returns the Configuration for the given path
	 *
	 * @param string $path
	 * @throws \Cundd\Rest\VirtualObject\Exception\MissingConfigurationException
	 * @return ConfigurationInterface
	 */
	public function getConfigurationForPath($path) {
		$path = substr($path, strpos($path, '-') + 1); // Strip the "VirtualObject-" from the path
		if (!$path) {
			throw new MissingConfigurationException('Could not get configuration for empty path', 1395932408);
		}
		return $this->configurationFactory->createFromTypoScriptForPath($path);
	}

	/**
	 * Returns the domain model repository class name for the given API path
	 *
	 * @param string $path API path to get the repository for
	 * @return string
	 */
	public function getRepositoryClassForPath($path) {
		return 'Cundd\\Rest\\VirtualObject\\Persistence\\Repository';
	}

	/**
	 * Returns the domain model repository for the models the given API path points to
	 *
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
	 */
	public function getRepositoryForPath($path) {
		$repositoryClass = $this->getRepositoryClassForPath($path);
		/** @var \Cundd\Rest\VirtualObject\Persistence\RepositoryInterface $repository */
		$repository = $this->objectManager->get($repositoryClass);
		$repository->setConfiguration($this->getConfigurationForPath($path));
		return $repository;
	}

	/**
	 * Returns a domain model for the given API path and data
	 * This method will load existing models.
	 *
	 * @param array|string|int $data Data of the new model or it's UID
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithDataForPath($data, $path) {
		// If no data is given return a new instance
		if (!$data) {
			return $this->getEmptyModelForPath($path);
		} else if (is_scalar($data)) { // If it is a scalar treat it as identity
			return $this->getModelWithIdentityForPath($data, $path);
		}

		$data = $this->prepareModelData($data);
		try {
			$objectConverter = $this->getObjectConverterForPath($path);
			$modelData = $objectConverter->convertFromVirtualObject($data);
			$model = $objectConverter->convertToVirtualObject($modelData);

		} catch (\TYPO3\CMS\Extbase\Property\Exception $exception) {
			$model = NULL;

			$message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
			$this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
		}
		return $model;
	}

	/**
	 * Returns a new domain model for the given API path points to
	 *
	 * @param string $path API path to get the model for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getEmptyModelForPath($path) {
		return new VirtualObject();
	}

	/**
	 * Returns the property data from the given model
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $propertyKey
	 * @return mixed
	 */
	public function getModelProperty($model, $propertyKey) {
		/** @var VirtualObject $model */
		$modelData = $model->getData();
		if (isset($modelData[$propertyKey])) {
			return $modelData[$propertyKey];
		}
		return NULL;
	}

	/**
	 * Adds or updates the given model in the repository for the
	 * given API path
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $model
	 * @param string $path The API path
	 * @return void
	 */
	public function saveModelForPath($model, $path) {
		/** @var VirtualObject $model */
		/** @var RepositoryInterface $repository */
		$repository = $this->getRepositoryForPath($path);
		if ($repository) {
			$repository->registerObject($model);
			$this->persistAllChanges();
		}
	}

	/**
	 * Persist all changes to the database
	 */
	public function persistAllChanges() {
		// We don't have to do anything because changes are persisted live
	}
}