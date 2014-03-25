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
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 12.09.13
 * Time: 22:30
 * To change this template use File | Settings | File Templates.
 */

namespace Cundd\Rest\Access;

use Cundd\Rest\Dispatcher;

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
			Dispatcher::getSharedDispatcher()->logException($exception);
			$isAuthenticated = FALSE;

			throw $exception;
		}
		if ($isAuthenticated === FALSE) {
			return self::ACCESS_UNAUTHORIZED;
		}
		return self::ACCESS_ALLOW;
	}
}
