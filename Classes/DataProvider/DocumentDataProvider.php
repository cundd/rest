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

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Exception\InvalidIdException;
use Cundd\Rest\Domain\Model\Document;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Domain\Repository\DocumentRepository;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class DocumentDataProvider extends DataProvider
{

    /**
     * Returns the domain model repository class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getRepositoryClassForResourceType(ResourceType $resourceType)
    {
        return DocumentRepository::class;
    }

    /**
     * Returns the domain model class name for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return string
     */
    public function getModelClassForResourceType(ResourceType $resourceType)
    {
        return Document::class;
    }

    /**
     * Returns all domain model for the given API resource type
     *
     * @param ResourceType $resourceType API resource type to get the repository for
     * @return DomainObjectInterface[]|QueryResultInterface
     */
    public function getAllModelsForResourceType(ResourceType $resourceType)
    {
        $documentDatabase = $this->getDatabaseNameFromResourceType($resourceType);

        /** @var DocumentRepository $documentRepository */
        $documentRepository = $this->getRepositoryForResourceType($resourceType);
        $documentRepository->setDatabase($documentDatabase);

        return $documentRepository->findAll();
    }

    /**
     * Adds or updates the given model in the repository for the
     * given API resource type
     *
     * @param Document     $model
     * @param ResourceType $resourceType The API resource type
     * @return void
     */
    public function saveModelForResourceType($model, ResourceType $resourceType)
    {
        $documentDatabase = $this->getDatabaseNameFromResourceType($resourceType);

        /** @var DocumentRepository $repository */
        $repository = $this->getRepositoryForResourceType($resourceType);
        $repository->setDatabase($documentDatabase);
        $model->_setDb($documentDatabase);
        if ($repository) {
            if ($model->_isNew()) {
                $repository->add($model);
            } else {
                $repository->update($model);
            }
            $this->persistAllChanges();
        }
    }

    /**
     * Returns the data from the given model
     *
     * @param Document $model
     * @return array<mixed>
     */
    public function getModelData($model)
    {
        if (!is_object($model)) {
            return null;
        }
        // Get the data from the model
        if ($model instanceof DomainObjectInterface) {
            $properties = $model->_getProperties();
        } else {
            $properties = [];
        }
        $properties['_meta'] = array(
            'db'     => $model->_getDb(),
            'guid'   => $model->getGuid(),
            'tstamp' => $model->valueForKey('tstamp'),
            'crdate' => $model->valueForKey('crdate'),
        );


        $documentData = $model->_getUnpackedData();

        // Remove hidden fields
        unset($documentData['tstamp']);
        unset($documentData['crdate']);
        unset($documentData['cruser_id']);
        unset($documentData['cruserId']);
        unset($documentData['deleted']);
        unset($documentData['hidden']);
        unset($documentData['starttime']);
        unset($documentData['endtime']);

        // Remove the already assigned entries
        unset($properties[Document::DATA_PROPERTY_NAME]);
        unset($properties['db']);

        return array_merge($documentData, $properties);
    }

    /**
     * Returns the Document database name for the given resource type
     *
     * @param ResourceType $resourceType
     * @return string
     */
    public function getDatabaseNameFromResourceType(ResourceType $resourceType)
    {
        var_dump($resourceType);

        return Utility::singularize(strtolower(substr($resourceType, 9))); // Strip 'Document-' and singularize
    }

    /**
     * Returns a domain model for the given API resource type and data
     * This method will load existing models.
     *
     * @param array|string|int $data         Data of the new model or it's UID
     * @param ResourceType     $resourceType API resource type to get the repository for
     * @return DomainObjectInterface
     */
    public function getModelWithDataForResourceType($data, ResourceType $resourceType)
    {
        // If no data is given return NULL
        if (!$data) {
            return null;
        } elseif (is_scalar($data)) { // If it is a scalar treat it as identity
            return $this->getModelWithIdentityForResourceType($data, $resourceType);
        }

        $data = $this->prepareModelData($data);
        try {
            if (!isset($data['id']) || !$data['id']) {
                throw new InvalidIdException('Missing object ID', 1390319238);
            }
            $documentDatabase = $this->getDatabaseNameFromResourceType($resourceType);

            /** @var DocumentRepository $repository */
            $repository = $this->getRepositoryForResourceType($resourceType);
            $repository->setDatabase($documentDatabase);

            $model = $repository->convertToDocument($data);
            $model->_setDb($documentDatabase);

        } catch (\Exception $exception) {
            $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));

            return null;
        }

        return $model;
    }
}
