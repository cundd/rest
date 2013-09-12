<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 12.09.13
 * Time: 22:15
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest\Access;


interface AccessControllerInterface {
	/**
	 * Returns if the given request needs authentication
	 * @return bool
	 */
	public function requestNeedsAuthentication();
}