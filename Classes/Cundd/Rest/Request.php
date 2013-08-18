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
	public function getPath() {
		if (!$this->path) {
			$uri = $this->url();
			$this->path = strtok($uri, '/');
		}
		return $this->path;
	}
}