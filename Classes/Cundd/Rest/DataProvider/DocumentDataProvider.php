<?php
namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Exception\InvalidIdException;
use Cundd\Rest\Domain\Model\Document;
use Cundd\Rest\Domain\Repository\DocumentRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Core\Log\LogLevel;

class DocumentDataProvider extends DataProvider {

	/**
	 * Returns the domain model repository class name for the given API path
	 *
	 * @param string $path API path to get the repository for
	 * @return string
	 */
	public function getRepositoryClassForPath($path) {
		return 'Cundd\\Rest\\Domain\\Repository\\DocumentRepository';
	}

	/**
	 * Returns the domain model class name for the given API path
	 *
	 * @param string $path API path to get the repository for
	 * @return string
	 */
	public function getModelClassForPath($path) {
		return 'Cundd\\Rest\\Domain\\Model\\Document';
	}

	/**
	 * Returns all domain model for the given API path
	 *
	 * @param string $path API path to get the repository for
	 * @return DomainObjectInterface
	 */
	public function getAllModelsForPath($path) {
		$documentDatabase = $this->getDatabaseNameFromPath($path);

		/** @var DocumentRepository $documentRepository */
		$documentRepository = $this->getRepositoryForPath($path);
		$documentRepository->setDatabase($documentDatabase);
		return $documentRepository->findAll();
	}

	/**
	 * Adds or updates the given model in the repository for the
	 * given API path
	 * @param Document $model
	 * @param string $path The API path
	 * @return void
	 */
	public function saveModelForPath($model, $path) {
		$documentDatabase = $this->getDatabaseNameFromPath($path);

		/** @var DocumentRepository $repository */
		$repository = $this->getRepositoryForPath($path);
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
	public function getModelData($model) {
		$properties = NULL;
		if (is_object($model)) {
			// Get the data from the model
			if ($model instanceof DomainObjectInterface) {
				$properties = $model->_getProperties();
			}
			$properties['_meta'] = array(
				'db' => $model->_getDb(),
				'guid' => $model->getGuid(),
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

			$properties = array_merge($documentData, $properties);
		}

		return $properties;
	}

	/**
	 * Returns the Document database name for the given path
	 *
	 * @param string $path
	 * @return string
	 */
	public function getDatabaseNameFromPath($path) {
		return Utility::singularize(strtolower(substr($path, 9))); // Strip 'Document-' and singularize
	}

	/**
	 * Returns a domain model for the given API path and data
	 * This method will load existing models.
	 *
	 * @param array|string|int $data Data of the new model or it's UID
	 * @param string $path API path to get the repository for
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getModelWithDataForPath($data, $path) {
		// If no data is given return NULL
		if (!$data) {
			return NULL;
		} else if (is_scalar($data)) { // If it is a scalar treat it as identity
			return $this->getModelWithIdentityForPath($data, $path);
		}

		$data = $this->prepareModelData($data);
		try {
			if (!isset($data['id']) || !$data['id']) {
				throw new InvalidIdException('Missing object ID', 1390319238);
			}
			$documentDatabase = $this->getDatabaseNameFromPath($path);

			/** @var DocumentRepository $repository */
			$repository = $this->getRepositoryForPath($path);
			$repository->setDatabase($documentDatabase);

			$model = $repository->convertToDocument($data);
			$model->_setDb($documentDatabase);
		} catch (\Exception $exception) {
			$model = NULL;

			$message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
			$this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
		}
		return $model;
	}
}