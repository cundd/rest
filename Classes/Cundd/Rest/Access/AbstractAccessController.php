<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 12.09.13
 * Time: 22:30
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest\Access;


class AbstractAccessController {
	/**
	 * The current request
	 * @var \Cundd\Rest\Request
	 */
	protected $request;

	/**
	 * @param \Bullet\Request|\Cundd\Rest\Request $request
	 * @return mixed|void
	 */
	public function setRequest(\Cundd\Rest\Request $request) {
		$this->request = $request;
	}

	/**
	 * @return \Bullet\Request
	 */
	public function getRequest() {
		return $this->request;
	}
}