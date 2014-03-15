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
 * @author COD
 * Created 06.12.13 12:34
 */


namespace Cundd\Rest\Cache;

use Bullet\Response;
use Cundd\Rest\DataProvider\Utility;
use TYPO3\CMS\Core\Cache\CacheManager;

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
	 * Concrete cache instance
	 * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
	 */
	protected $cacheInstance;

	/**
	 * Cache life time
	 *
	 * @var integer
	 */
	protected $cacheLifeTime = NULL;

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
		$cacheLifeTime = $this->getCacheLifeTime();

		/*
		 * Use caching if the cache life time configuration is not -1, an API
		 * path is given and the request is a read request
		 */
		$useCaching = ($cacheLifeTime !== -1) && $request->path();
		if (!$useCaching) {
			return NULL;
		}

		$cacheInstance = $this->_getCacheInstance();
		$responseArray = $cacheInstance->get($this->_getCacheKey());
		if (!$responseArray) {
			return NULL;
		}

		if (!$request->isRead()) {
			$this->_clearCache();
			return NULL;
		}

		$response = new Response($responseArray['content'], $responseArray['status']);
		$response->contentType($responseArray['content-type']);
		$response->encoding($responseArray['encoding']);
		$response->header('Last-Modified', $responseArray['last-modified']);
		$response->header('Expires', $this->getHttpDate(time() + $cacheLifeTime));

		$response->header('cundd-rest-cached', 'true');
		return $response;
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

		$cacheLifeTime = $this->getCacheLifeTime();

		/*
		 * Use caching if the cache life time configuration is not -1, an API
		 * path is given and the request is a read request
		 */
		$useCaching = ($cacheLifeTime !== -1) && $request->path();
		if (!$useCaching) {
			return;
		}

		$cacheInstance = $this->_getCacheInstance();
		$cacheInstance->set($this->_getCacheKey(), array(
			'content' => (string)$response,
			'status' => $response->status(),
			'encoding' => $response->encoding(),
			'content-type' => $response->contentType(),
			'last-modified' => $this->getHttpDate(time()),
		), $this->_getTags(), $cacheLifeTime);
	}

	/**
	 * Returns the cache key for the given request
	 *
	 * @param \Cundd\Rest\Request $request
	 * @return string
	 */
	public function getCacheKeyForRequest(\Cundd\Rest\Request $request) {
		$this->currentRequest = $request;
		return $this->_getCacheKey();
	}


	/**
	 * Sets the cache life time
	 *
	 * @param int $cacheLifeTime
	 * @return $this
	 */
	public function setCacheLifeTime($cacheLifeTime) {
		$this->cacheLifeTime = $cacheLifeTime;
		return $this;
	}

	/**
	 * Returns the cache life time
	 *
	 * @return int
	 */
	public function getCacheLifeTime() {
		if ($this->cacheLifeTime === NULL) {
			$this->cacheLifeTime = intval($this->objectManager->getConfigurationProvider()->getSetting('cacheLifeTime'));
		}
		return $this->cacheLifeTime;
	}

	/**
	 * Returns a date in the format for a HTTP header
	 *
	 * @param $date
	 * @return string
	 */
	protected function getHttpDate($date) {
		return gmdate('D, d M Y H:i:s \G\M\T', $date);
	}

	/**
	 * Returns the cache instance
	 *
	 * @return \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
	 */
	protected function _getCacheInstance() {
		if (!$this->cacheInstance) {
			/** @var CacheManager $cacheManager */
			$cacheManager = $GLOBALS['typo3CacheManager'];
			$this->cacheInstance = $cacheManager->getCache('cundd_rest_cache');
		}
		return $this->cacheInstance;
	}

	/**
	 * Sets the concrete Cache instance
	 *
	 * @param \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend $cacheInstance
	 */
	public function setCacheInstance($cacheInstance) {
		$this->cacheInstance = $cacheInstance;
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
		return sha1($this->currentRequest->url() . '_' . $this->currentRequest->format() . '_' . $this->currentRequest->method());
	}
}