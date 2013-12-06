<?php
/*
 *  Copyright notice
 *
 *  (c) 2013 Andreas Thurnheer-Meier <tma@iresults.li>, iresults
 *  Daniel Corn <cod@iresults.li>, iresults
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
 * @author COD
 * Created 06.12.13 12:34
 */


namespace Cundd\Rest\Cache;

use Bullet\Response;
use Cundd\Rest\DataProvider\Utility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * The class caches responses of requests
 *
 * @package Cundd\Rest\Cache
 */
class Cache {
	/**
	 * @var \Cundd\Rest\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Current request
	 *
	 * @var \Cundd\Rest\Request
	 */
	protected $currentRequest;

	/**
	 * Returns the cached value for the given request or NULL if it is not
	 * defined
	 *
	 * @param \Cundd\Rest\Request $request
	 * @return string
	 */
	public function getCachedValueForRequest(\Cundd\Rest\Request $request) {
		$this->currentRequest = $request;

		/** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend $cacheInstance */
		$cacheInstance = NULL;
		$cacheLifetime = intval($this->objectManager->getConfigurationProvider()->getSetting('cacheLifetime'));

		/*
		 * Use caching if the cache life time configuration is not -1, an API
		 * path is given and the request is a read request
		 */
		$useCaching = ($cacheLifetime !== -1) && $request->path();
		if (!$useCaching) {
			return NULL;
		}

		$cacheInstance = $this->_getCacheInstance();
		$responseString = $cacheInstance->get($this->_getCacheKey());
		if (!$responseString) {
			return NULL;
		}

		if ($request->isRead()) {
			return $responseString;
		}
		$this->_clearCache();
		return NULL;
	}

	/**
	 * Sets the cache value for the given request
	 *
	 * @param \Cundd\Rest\Request $request
	 * @param \Bullet\Response    $response
	 */
	public function setCachedValueForRequest(\Cundd\Rest\Request $request, Response $response) {
		$this->currentRequest = $request;

		/** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend $cacheInstance */
		$cacheInstance = NULL;

		// Don't cache exceptions
		if ($response->content() instanceof \Exception) {
			return;
		}

		// Don't cache write requests
		if ($request->isWrite()) {
			return;
		}

		$cacheLifetime = intval($this->objectManager->getConfigurationProvider()->getSetting('cacheLifetime'));

		/*
		 * Use caching if the cache life time configuration is not -1, an API
		 * path is given and the request is a read request
		 */
		$useCaching = ($cacheLifetime !== -1) && $request->path();
		if (!$useCaching) {
			return;
		}

		$cacheInstance = $this->_getCacheInstance();
		$cacheInstance->set($this->_getCacheKey(), (string)$response, $this->_getTags(), $cacheLifetime);
	}

	/**
	 * Returns the cache instance
	 *
	 * @return \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
	 */
	protected function _getCacheInstance() {
		/** @var CacheManager $cacheManager */
		$cacheManager = $GLOBALS['typo3CacheManager'];
		return $cacheManager->getCache('cundd_rest_cache');
	}

	/**
	 * Clears the cache for the current request
	 */
	protected function _clearCache() {
		$allTags = $this->_getTags();
		$firstTag = $allTags[0];
		$this->_getCacheInstance()->flushByTag($firstTag);
	}

	/**
	 * Returns the tags for the current request
	 *
	 * @return array[string]
	 */
	protected function _getTags() {
		$currentPath = $this->currentRequest->path();
		list($vendor, $extension, $model) = Utility::getClassNamePartsForPath($currentPath);
		return array(
			$vendor . '_' . $extension . '_' . $model,
			$extension . '_' . $model,
			$currentPath
		);
	}

	/**
	 * Returns the cache key for the current request
	 *
	 * @return string
	 */
	protected function _getCacheKey() {
		return sha1($this->currentRequest->originalPath() . '_' . $this->currentRequest->method());
	}
}