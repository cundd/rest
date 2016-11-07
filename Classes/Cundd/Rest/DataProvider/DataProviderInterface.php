<?php
/*
 *  Copyright notice
 *
 *  (c) 2016 Daniel Corn <info@cundd.net>, cundd
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

namespace Cundd\Rest\DataProvider;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

interface DataProviderInterface extends SingletonInterface
{
    /**
     * Returns the domain model repository for the models the given API path points to
     *
     * @param string $path API path to get the repository for
     * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
     */
    public function getRepositoryForPath($path);

    /**
     * Returns the domain model repository class name for the given API path
     *
     * @param string $path API path to get the repository for
     * @return string
     */
    public function getRepositoryClassForPath($path);

    /**
     * Returns all domain model for the given API path
     *
     * @param string $path API path to get the repository for
     * @return DomainObjectInterface[]|QueryResultInterface
     */
    public function getAllModelsForPath($path);

    /**
     * Returns a domain model for the given API path and data
     * This method will load existing models.
     *
     * @param array|string|int $data Data of the new model or it's UID
     * @param string $path API path to get the repository for
     * @return DomainObjectInterface
     */
    public function getModelWithDataForPath($data, $path);

    /**
     * Returns a domain model for the given API path and data
     * Even if the data contains an identifier, the existing model will not be loaded.
     *
     * @param array|string|int $data Data of the new model or it's UID
     * @param string $path API path to get the repository for
     * @return DomainObjectInterface
     */
    public function getNewModelWithDataForPath($data, $path);

    /**
     * Returns the domain model class name for the given API path
     *
     * @param string $path API path to get the repository for
     * @return string
     */
    public function getModelClassForPath($path);

    /**
     * Returns the data from the given model
     *
     * @param DomainObjectInterface|object $model
     */
    public function getModelData($model);

    /**
     * Returns the property data from the given model
     *
     * @param DomainObjectInterface $model
     * @param string $propertyKey
     * @return mixed
     */
    public function getModelProperty($model, $propertyKey);

    /**
     * Adds or updates the given model in the repository for the
     * given API path
     *
     * @param DomainObjectInterface $model
     * @param string $path The API path
     * @return void
     */
    public function saveModelForPath($model, $path);

    /**
     * Tells the Data Provider to replace the given old model with the new one
     * in the repository for the given API path
     *
     * @param DomainObjectInterface $oldModel
     * @param DomainObjectInterface $newModel
     * @param string $path The API path
     * @return void
     */
    public function replaceModelForPath($oldModel, $newModel, $path);

    /**
     * Adds or updates the given model in the repository for the
     * given API path
     *
     * @param DomainObjectInterface $model
     * @param string $path The API path
     * @return void
     */
    public function removeModelForPath($model, $path);

    /**
     * Persist all changes to the database
     */
    public function persistAllChanges();
}
