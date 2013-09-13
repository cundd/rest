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
	 * Access identifier to signal if the current request is allowed
	 */
	const ACCESS = 'ACCESS-CONST';

	/**
	 * Access identifier to signal allowed requests
	 */
	const ACCESS_ALLOW = 'allow';

	/**
	 * Access identifier to signal denied requests
	 */
	const ACCESS_DENY = 'deny';

	/**
	 * Access identifier to signal requests that require a valid login
	 */
	const ACCESS_REQUIRE_LOGIN = 'require';

	/**
	 * Access identifier to signal a missing login
	 */
	const ACCESS_UNAUTHORIZED = 'unauthorized';


	/**
	 * Returns if the current request has access to the requested resource
	 * @return AccessControllerInterface::ACCESS
	 */
	public function getAccess();

	/**
	 * Returns if the given request needs authentication
	 * @return bool
	 */
	public function requestNeedsAuthentication();
}