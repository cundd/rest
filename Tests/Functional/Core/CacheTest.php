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

namespace Cundd\Rest\Tests\Functional\Core;

use Cundd\Rest\Cache\Cache;
use Cundd\Rest\Tests\Functional\AbstractCase;

require_once __DIR__ . '/../AbstractCase.php';

/**
 * Tests for the Caching interface
 *
 * @package Cundd\Rest\Test\Core
 */
class CacheTest extends AbstractCase {
    /**
     * @var \Cundd\Rest\Cache\Cache
     */
    protected $fixture;

    public function setUp() {
        parent::setUp();

        /** @var Cache $fixture */
        $fixture = $this->objectManager->get('Cundd\\Rest\\Cache\\Cache');
        $fixture->setCacheLifeTime(10);
        $fixture->setExpiresHeaderLifeTime(5);
        $this->fixture = $fixture;
    }

    protected function tearDown() {
        unset($this->fixture);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getCacheKeyForRequestTest() {
        $uri = 'MyExt-MyModel/1';
        $request = $this->buildRequestWithUri($uri);
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('44a16b7f79c92d97a55281bbfb4439ff310607ec', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriMyExtMyModel1BlurRequestTest() {

        $uri = 'MyExt-MyModel/1';
        $request = $this->buildRequestWithUri($uri, 'blur');
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('102fa34f947e0cf64a430626f374ae2dfea9074d', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriMyExtMyModel1JsonRequestTest() {
        $uri = 'MyExt-MyModel/1';
        $request = $this->buildRequestWithUri($uri, 'json');
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('102fa34f947e0cf64a430626f374ae2dfea9074d', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriMyExtMyModel1JsonLowerCasedRequestTest() {
        $uri = 'my_ext-my_model/1';
        $request = $this->buildRequestWithUri($uri, 'json');
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('5c82b501dbbff50f5d15ddad1e3f68c86431bbc8', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriMyExtMyModelRequestTest() {
        $uri = 'my_ext-my_model';
        $request = $this->buildRequestWithUri($uri, 'json');
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('6216096e7394211b2d35fe9787d252b10963cf04', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriVendorMySecondExtMyModel1LowerCasedRequestTest() {
        $uri = 'vendor-my_second_ext-my_model/1';
        $request = $this->buildRequestWithUri($uri);
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('5f498749f876b6653099272efe7b827acfbc1ca6', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriVendorMySecondExtMyModel1RequestTest() {
        $uri = 'Vendor-MySecondExt-MyModel/1';
        $request = $this->buildRequestWithUri($uri);
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('3715e64cc29448acdc0df19777da794da2804d19', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriVendorNotExistingExtMyModel1RequestTest() {
        $uri = 'Vendor-NotExistingExt-MyModel/1';
        $request = $this->buildRequestWithUri($uri);
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('b40dc716cf22179ebab528dd365f87afd3a4ffa7', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriVendorNotExistingExtMyModel1JsonRequestTest() {
        $uri      = 'Vendor-NotExistingExt-MyModel/1';
        $request  = $this->buildRequestWithUri($uri, 'json');
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('edc589820622a8d127f335b6439d34f6b37016cf', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCacheKeyForUriMyAliasedModelTest() {
        $uri = 'MyAliasedModel';
        $request = $this->buildRequestWithUri($uri);
        $cacheKey = $this->fixture->getCacheKeyForRequest($request);
        $this->assertEquals('1eb5c867cb67a0c4f7eada2e5b1f3ed8f1c93350', $cacheKey, 'Failed for URI ' . $uri);
    }

    /**
     * @test
     */
    public function getCachedInitialValueForRequestTest() {
        $uri = 'MyAliasedModel' . time();
        $request = $this->buildRequestWithUri($uri);

        /** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend|\PHPUnit_Framework_MockObject_MockObject $cacheInstance */
        $cacheInstance = $this->getMock('TYPO3\\CMS\\Core\\Cache\\Frontend\\AbstractFrontend', array('getIdentifier', 'set', 'get', 'getByTag', 'has', 'remove', 'flush', 'flushByTag'), array(), '', FALSE);
        $this->fixture->setCacheInstance($cacheInstance);
        $cachedValue = $this->fixture->getCachedValueForRequest($request);
        $this->assertNull($cachedValue);
    }

    /**
     * @test
     */
    public function getCachedValueForRequestTest() {
        $uri = 'MyAliasedModel' . time();
        $responseArray = array(
            'content' => 'the content',
            'status' => 200,
            'content-type' => null,
            'encoding' => null,
            'last-modified' => null,
        );

        $request = $this->buildRequestWithUri($uri);

        /** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend|\PHPUnit_Framework_MockObject_MockObject $cacheInstance */
        $cacheInstance = $this->getMock('TYPO3\\CMS\\Core\\Cache\\Frontend\\AbstractFrontend', array('getIdentifier', 'set', 'get', 'getByTag', 'has', 'remove', 'flush', 'flushByTag'), array(), '', FALSE);
        $cacheInstance->expects($this->atLeastOnce())->method('get')->will($this->returnValue($responseArray));
        $this->fixture->setCacheInstance($cacheInstance);
        $response = $this->fixture->getCachedValueForRequest($request);
        $this->assertInstanceOf('Bullet\\Response', $response);
        $this->assertSame($responseArray['content'], $response->content());
        $this->assertSame($responseArray['status'], $response->status());
    }

    /**
     * @test
     */
    public function setCachedValueForRequestTest() {
        $response = new \Bullet\Response();
        $response->content('Test content');
        $uri = 'MyAliasedModel';
        $request = $this->buildRequestWithUri($uri);

        /** @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend|\PHPUnit_Framework_MockObject_MockObject $cacheInstance */
        $cacheInstance = $this->getMock('TYPO3\\CMS\\Core\\Cache\\Frontend\\AbstractFrontend', array('getIdentifier', 'set', 'get', 'getByTag', 'has', 'remove', 'flush', 'flushByTag'), array(), '', FALSE);
        $cacheInstance->expects($this->atLeastOnce())->method('set')->will($this->returnValue(''));
        $this->fixture->setCacheInstance($cacheInstance);
        $this->fixture->setCachedValueForRequest($request, $response);
    }
}
