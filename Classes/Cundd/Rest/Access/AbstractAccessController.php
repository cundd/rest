<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 12.09.13
 * Time: 22:30
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest\Access;

use Cundd\Rest\App;

abstract class AbstractAccessController implements AccessControllerInterface {
	/**
	 * The current request
	 * @var \Cundd\Rest\Request
	 */
	protected $request;

	/**
	 * @var \Cundd\Rest\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Sets the current request
	 *
	 * @param \Cundd\Rest\Request $request
	 */
	public function setRequest(\Cundd\Rest\Request $request) {
		$this->request = $request;
	}

	/**
	 * Returns the current request
	 *
	 * @return \Bullet\Request
	 */
	public function getRequest() {
		return $this->request;
	}


	/**
	 * Checks if a valid user is logged in
	 *
	 * @throws \Exception
	 * @return AccessControllerInterface::ACCESS
	 */
	protected function checkAuthentication() {
		try {
			$isAuthenticated = $this->objectManager->getAuthenticationProvider()->authenticate();
		} catch (\Exception $exception) {
			App::getSharedDispatcher()->logException($exception);
			$isAuthenticated = FALSE;

			throw $exception;
		}
		if ($isAuthenticated === FALSE) {
			return self::ACCESS_UNAUTHORIZED;
		}
		return self::ACCESS_ALLOW;
	}
}