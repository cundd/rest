<?php
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
	 * @param \Cundd\Rest\Configuration\TypoScriptConfigurationProvider $configurationProvider
	 */
	public function injectConfigurationProvider($configurationProvider) {
		$this->configurationProvider = $configurationProvider;
	}
}