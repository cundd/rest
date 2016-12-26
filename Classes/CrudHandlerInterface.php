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
interface CrudHandlerInterface extends HandlerInterface
{

    /**
     * Returns the unique identifier of the currently matching Domain Model
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Sets the unique identifier of the currently matching Domain Model
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier);

    /**
     * Returns the given property of the currently matching Model
     *
     * @param string $propertyKey
     * @return mixed
     */
    public function getProperty($propertyKey);

    /**
     * Returns the data of the current Model
     *
     * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
     */
    public function show();

    /**
     * Replaces the currently matching Model with the data from the request
     *
     * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
     */
    public function replace();

    /**
     * Updates the currently matching Model with the data from the request
     *
     * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
     */
    public function update();

    /**
     * Deletes the currently matching Model
     *
     * @return integer Returns 200 an success
     */
    public function delete();

    /**
     * Creates a new Model with the data from the request
     *
     * @return array|integer Returns the Model's data on success, otherwise a descriptive error code
     */
    public function create();

    /**
     * List all Models
     *
     * @return array Returns all Models
     */
    public function listAll();
}
