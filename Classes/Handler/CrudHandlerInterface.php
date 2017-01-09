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

namespace Cundd\Rest\Handler;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for handlers of API requests
 */
interface CrudHandlerInterface extends HandlerInterface
{
    /**
     * Returns the given property of the currently matching Model
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @param string               $propertyKey
     * @return mixed
     */
    public function getProperty(RestRequestInterface $request, $identifier, $propertyKey);

    /**
     * Returns the data of the current Model
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function show(RestRequestInterface $request, $identifier);

    /**
     * Replaces the currently matching Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function replace(RestRequestInterface $request, $identifier);

    /**
     * Updates the currently matching Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function update(RestRequestInterface $request, $identifier);

    /**
     * Deletes the currently matching Model
     *
     * @param RestRequestInterface $request
     * @param int|string           $identifier
     * @return int|ResponseInterface Returns 200 an success
     */
    public function delete(RestRequestInterface $request, $identifier);

    /**
     * Creates a new Model with the data from the request
     *
     * @param RestRequestInterface $request
     * @return array|int|ResponseInterface Returns the Model's data on success, otherwise a descriptive error code
     */
    public function create(RestRequestInterface $request);

    /**
     * List all Models
     *
     * @param RestRequestInterface $request
     * @return array Returns all Models
     */
    public function listAll(RestRequestInterface $request);
}
