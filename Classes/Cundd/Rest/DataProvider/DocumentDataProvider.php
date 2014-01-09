<?php
namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Domain\Model\Document;
use Cundd\Rest\Domain\Repository\DocumentRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	public function getAllModelsForPath($path) {
		$documentDatabase = substr($path, 9); // Strip 'Document-'

		/** @var DocumentRepository $documentRepository */
		$documentRepository = $this->getRepositoryForPath($path);
		$documentRepository->setDatabase($documentDatabase);
		return $documentRepository->findAll();
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
			if ($model instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
				$properties = $model->_getProperties();
			}
			$properties['_meta'] = array(
				'db' => $model->_getDb(),
				'guid' => $model->getGuid(),
				'tstamp' => $model->valueForKey('tstamp'),
				'crdate' => $model->valueForKey('crdate'),
			);


			$content = $model->_unpackContent();

			// Remove hidden fields
			unset($content['tstamp']);
			unset($content['crdate']);
			unset($content['cruser_id']);
			unset($content['deleted']);
			unset($content['hidden']);
			unset($content['starttime']);
			unset($content['endtime']);

			$properties = array_merge($content, $properties);
		}

		return $properties;
	}
}