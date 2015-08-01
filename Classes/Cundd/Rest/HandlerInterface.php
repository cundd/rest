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
 * Created by PhpStorm.
 * User: daniel
 * Date: 25.03.14
 * Time: 14:37
 */

namespace Cundd\Rest;

/**
 * Interface for handlers of API requests
 *
 * @package Cundd\Rest
 */
interface HandlerInterface {
    /**
     * Sets the current request
     *
     * @param \Cundd\Rest\Request $request
     * @return $this
     */
    public function setRequest($request);

    /**
     * Returns the current request
     *
     * @return \Cundd\Rest\Request
     */
    public function getRequest();

    /**
     * Configures which method is responsible for handling the different request paths
     */
    public function configureApiPaths();

} 