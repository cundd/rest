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

namespace Cundd\Rest;
use Bullet\HTTP;
use Bullet\Request as BaseRequest;

class Request extends BaseRequest {
	/**
	 * @var \Cundd\Rest\Configuration\TypoScriptConfigurationProvider
	 */
	protected $configurationProvider;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $originalPath = -1;


	/**
	 * @return string
	 */
	public function path() {
		if (!$this->path) {
			$uri = $this->url();
			$this->originalPath = $this->path = strtok($uri, '/');

			// Check for path aliases
			$pathAlias = $this->getAliasForPath($this->path);
			if ($pathAlias) {
				$oldPath = $this->path;

				// Update the URL
				$this->_url = preg_replace('!' . $oldPath . '!', $pathAlias, $this->_url, 1);
				$this->path = $pathAlias;
			}
		}
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function originalPath() {
		if ($this->originalPath === -1) {
			return $this->path();
		}
		return $this->originalPath;
	}

	/**
	 * Format getter/setter
	 *
	 * If no $format is passed, returns the current format
	 *
	 * @param string $format
	 * @return string Format
	 */
	public function format($format = null) {
		if ($format !== NULL) {
			if (!isset($this->_mimeTypes[$format])) {
				$format = NULL;
			}
		}
		return parent::format($format);
	}

	/**
	 * Check for an alias for the given path
	 * @param string $path
	 * @return string
	 */
	public function getAliasForPath($path) {
		if (!$this->configurationProvider) {
			return NULL;
		}
		return $this->configurationProvider->getSetting('aliases.' . $path);
	}

	/**
	 * Returns if the request wants to write data
	 * @return bool
	 */
	public function isWrite() {
		return !$this->isRead();
	}

	/**
	 * Returns if the request wants to read data
	 * @return bool
	 */
	public function isRead() {
		return in_array(strtoupper($this->method()), array('GET', 'HEAD'));
	}

	/**
	 * @param \Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider
	 */
	public function injectConfigurationProvider($configurationProvider) {
		$this->configurationProvider = $configurationProvider;
	}
}
