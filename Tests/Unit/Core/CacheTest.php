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
 * Created 10.12.13 09:21
 */

namespace Cundd\Rest\Test\Core;
use Cundd\Rest\Cache\Cache;

/**
 * Tests for the Caching interface
 *
 * @package Cundd\Rest\Test\Core
 */
class CacheTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \Cundd\Rest\Cache\Cache
	 */
	protected $fixture;

	protected function setUp() {
		/** @var Cache $fixture */
		$fixture = $this->objectManager->get('Cundd\\Rest\\Cache\\Cache');
		$fixture->setCacheLifeTime(10);
		$this->fixture = $fixture;
	}

	protected function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getCacheKeyForRequestTest() {
		$uri = 'MyExt-MyModel/1';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('44a16b7f79c92d97a55281bbfb4439ff310607ec', $cacheKey, 'Failed for URI ' . $uri);

		$uri = 'MyExt-MyModel/1.blur';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('44a16b7f79c92d97a55281bbfb4439ff310607ec', $cacheKey, 'Failed for URI ' . $uri);

		$uri = 'MyExt-MyModel/1.json';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('102fa34f947e0cf64a430626f374ae2dfea9074d', $cacheKey, 'Failed for URI ' . $uri);

		$uri = 'my_ext-my_model/1.json';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('5c82b501dbbff50f5d15ddad1e3f68c86431bbc8', $cacheKey, 'Failed for URI ' . $uri);


		$uri = 'my_ext-my_model.json';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('6216096e7394211b2d35fe9787d252b10963cf04', $cacheKey, 'Failed for URI ' . $uri);

		$uri = 'vendor-my_second_ext-my_model/1';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('5f498749f876b6653099272efe7b827acfbc1ca6', $cacheKey, 'Failed for URI ' . $uri);

		$uri = 'Vendor-MySecondExt-MyModel/1';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('3715e64cc29448acdc0df19777da794da2804d19', $cacheKey, 'Failed for URI ' . $uri);

		$uri = 'Vendor-NotExistingExt-MyModel/1';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('b40dc716cf22179ebab528dd365f87afd3a4ffa7', $cacheKey, 'Failed for URI ' . $uri);

		$uri = 'Vendor-NotExistingExt-MyModel/1.json';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('edc589820622a8d127f335b6439d34f6b37016cf', $cacheKey, 'Failed for URI ' . $uri);

		$uri = 'MyAliasedModel';
		$request = $this->buildRequestWithUri($uri);
		$cacheKey = $this->fixture->getCacheKeyForRequest($request);
		$this->assertEquals('1eb5c867cb67a0c4f7eada2e5b1f3ed8f1c93350', $cacheKey, 'Failed for URI ' . $uri);
	}

	/**
	 * @test
	 */
	public function getCachedValueForRequestTest() {
		$uri = 'MyAliasedModel' . time();
		$request = $this->buildRequestWithUri($uri);
		$cachedValue = $this->fixture->getCachedValueForRequest($request);
		$this->assertNull($cachedValue);
	}

	/**
	 * @test
	 */
	public function setCachedValueForRequestTest() {
		$response = new \Bullet\Response();
		$response->content('Test content');
		$uri = 'MyAliasedModel';
		$request = $this->buildRequestWithUri($uri);

		$this->fixture->setCachedValueForRequest($request, $response);


		$cachedResponse = $this->fixture->getCachedValueForRequest($request);
		$this->assertInstanceOf('Bullet\\Response', $cachedResponse);
		$this->assertEquals('Test content', $cachedResponse);
	}

	public function buildRequestWithUri($uri) {
		$format = '';
		$uri = filter_var($uri, FILTER_SANITIZE_URL);

		// Strip the format from the URI
		$resourceName = basename($uri);
		$lastDotPosition = strrpos($resourceName, '.');
		if ($lastDotPosition !== FALSE) {
			$newUri = '';
			if ($uri !== $resourceName) {
				$newUri = dirname($uri) . '/';
			}
			$newUri .= substr($resourceName, 0, $lastDotPosition);
			$uri = $newUri;

			$format = substr($resourceName, $lastDotPosition + 1);
		}


		$request = new \Cundd\Rest\Request(NULL, $uri);
		$request->injectConfigurationProvider($this->objectManager->get('Cundd\\Rest\\ObjectManager')->getConfigurationProvider());
		if ($format) {
			$request->format($format);
		}
		return $request;
	}
}
