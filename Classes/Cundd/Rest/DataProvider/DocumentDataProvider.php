<?php
namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\Document;
use Cundd\Rest\Domain\Repository\DocumentRepository;
use Iresults\Core\Iresults;
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
		$documentDatabase = substr($path, 9); // Strip 'Document-'

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
		$documentDatabase = substr($path, 9); // Strip 'Document-'

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

//			Iresults::forceDebug();
//
//
//			Iresults::pd('DOC DATA');
//			Iresults::pd($documentData);
//			Iresults::pd($model);
////			Iresults::pd($model->_getDataProtected());
//
//			Iresults::say('');
//			Iresults::say('');
//			Iresults::say('');

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
}