<?php
namespace Cundd\Rest;
use Bullet\Request as BaseRequest;

class Request extends BaseRequest {
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @return string
	 */
	public function path() {
		if (!$this->path) {
			$uri = $this->url();
			$this->path = strtok($uri, '/');
		}
		return $this->path;
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


}