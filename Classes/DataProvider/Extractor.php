<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 09/02/2017
 * Time: 19:35
 */

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use DateTime;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;
use TYPO3\CMS\Extbase\Domain\Model\Category as Typo3CoreCategory;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;

/**
 * Class to prepare/extract the data to be sent from objects
 */
class Extractor implements ExtractorInterface
{
    /**
     * @var ConfigurationProviderInterface
     */
    protected $configurationProvider;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;

    /**
     * The current depth when preparing model data for output
     *
     * @var int
     */
    protected $currentModelDataDepth = 0;

    /**
     * Dictionary of handled models to their count
     *
     * @var array
     */
    protected static $handledModels = array();

    /**
     * Extractor constructor
     *
     * @param ConfigurationProviderInterface $configurationProvider
     * @param LoggerInterface                $logger
     */
    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        LoggerInterface $logger = null
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->logger = $logger;
    }

    /**
     * Returns the data from the given input
     *
     * @param mixed $input
     * @return mixed
     * @throws \Exception
     */
    public function extract($input)
    {
        $this->assertExtractableType($input);

        if (is_null($input)) {
            return null;
        }

        if (is_scalar($input)) {
            return $input;
        }

        // Indexed array
        if (is_array($input) && $this->isIndexedArray($input)) {
            return $this->transformTraversable($input);
        }

        // Traversable
        if ($input instanceof \Traversable && !method_exists($input, 'jsonSerialize')) {
            return $this->transformTraversable($input);
        }

        // Dictionary/associative array
        if (is_array($input) && $this->isAssociativeArray($input)) {
            return $this->transformArrayProperties($input);
        }

        // DateTime
        if (is_object($input) && $input instanceof DateTimeInterface) {
            return $input->format(DateTime::ATOM);
        }

        // General object
        if (is_object($input)) {
            return $this->extractObjectDataIfNotRecursion($input);
        }

        throw new \InvalidArgumentException(sprintf('Can not extract data from type %s', gettype($input)));
    }

    /**
     * @param $input
     * @return array
     */
    private function extractObjectDataIfNotRecursion($input)
    {
        assert(is_object($input), sprintf('Input must be an object %s given', gettype($input)));

        $modelHash = spl_object_hash($input);
        if (isset(static::$handledModels[$modelHash])) {
            static::$handledModels[$modelHash] += 1;
        } else {
            static::$handledModels[$modelHash] = 1;
        }

        if (static::$handledModels[$modelHash] < 2) {
            $result = $this->extractObjectData($input);
        } else {
            $result = $this->getUriToResource($input);
        }
        static::$handledModels[$modelHash] -= 1;

        return $result;
    }

    /**
     * @param object $input
     * @return mixed
     */
    private function extractObjectData($input)
    {
        assert(is_object($input), sprintf('Input must be an object %s given', gettype($input)));

        if (method_exists($input, 'jsonSerialize')) {
            // jsonSerialize() can return anything but `resource`
            $properties = $input->jsonSerialize();
        } elseif ($input instanceof FileInterface) {
            return $this->addClassProperty($input, $this->transformFileReference($input));
        } elseif ($input instanceof AbstractFileFolder) {
            return $this->addClassProperty($input, $this->transformFileReference($input->getOriginalResource()));
        } elseif ($input instanceof DomainObjectInterface) {
            $properties = $input->_getProperties();
        } else {
            $properties = get_object_vars($input);
        }

        if (!is_array($properties)) {
            return $properties;
        }

        $properties = $this->transformObjectProperties($input, $properties);

        return $this->addClassProperty($input, $properties);
    }

    /**
     * Transform the properties
     *
     * @param DomainObjectInterface|object $model
     * @param array                        $properties
     * @return array
     */
    private function transformObjectProperties($model, array $properties)
    {
        assert(is_object($model), sprintf('Input must be an object %s given', gettype($model)));

        // Transform objects recursive
        foreach ($properties as $propertyKey => $propertyValue) {
            $this->assertExtractableType($propertyValue);
            if (is_scalar($propertyValue) || $propertyValue === null) {
                // Go on with the next value
                continue;
            }

            if (is_object($propertyValue)) {
                $properties[$propertyKey] = $this->transformObjectProperty($propertyValue, $propertyKey, $model);
            } elseif (is_array($propertyValue)) {
                $properties[$propertyKey] = $this->transformArrayProperties($propertyValue);
            } else {
                assert(false, sprintf('Property value is of type %s', gettype($propertyValue)));
            }
        }

        return $properties;
    }

    /**
     * Transform the properties
     *
     * @param array $properties
     * @return array
     */
    private function transformArrayProperties(array $properties)
    {
        // Transform array recursive
        foreach ($properties as $propertyKey => $propertyValue) {
            $this->assertExtractableType($propertyValue);
            if (is_scalar($propertyValue) || $propertyValue === null) {
                // Go on with the next value
                continue;
            }

            if (is_object($propertyValue)) {
                $properties[$propertyKey] = $this->transformObjectProperty($propertyValue, $propertyKey);
            } elseif (is_array($propertyValue)) {
                $properties[$propertyKey] = $this->transformArrayProperties($propertyValue);
            } else {
                assert(false, sprintf('Property value is of type %s', gettype($propertyValue)));
            }
        }

        return $properties;
    }

    private function isAssociativeArray(array $input)
    {
        if (array() === $input) {
            return false;
        }

        return array_keys($input) !== range(0, count($input) - 1);
    }

    private function isIndexedArray(array $input)
    {
        return !$this->isAssociativeArray($input);
    }

    /**
     * Adds the __class property to the export data if configured
     *
     * @param mixed $model
     * @param array $properties
     * @return mixed
     */
    protected function addClassProperty($model, array $properties)
    {
        if (isset($properties['__class'])) {
            return $properties;
        }

        if (true === (bool)$this->configurationProvider->getSetting('addClass', 0)) {
            $properties['__class'] = is_object($model) ? get_class($model) : gettype($model);
        }

        return $properties;
    }


    /**
     * Returns the URI of a nested resource
     *
     * @param string                       $resourceKey
     * @param object|DomainObjectInterface $model
     * @return string
     */
    private function getUriToNestedResource($resourceKey, $model)
    {
        $currentUri = 'rest/'
            . Utility::getResourceTypeForClassName(get_class($model))
            . '/' . intval($model->getUid()) . '/';

        if ($resourceKey !== null) {
            $currentUri .= $resourceKey;
        }

        return $this->getUriRequestBase() . $currentUri;
    }

    /**
     * @return string
     */
    private function getUriRequestBase()
    {
        if (class_exists(GeneralUtility::class) && false === getenv('CUNDD_TEST')) {
            return GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        }

        $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);
        $protocol = ((!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? 'http' : 'https');

        return $protocol . '://' . $host . '/';
    }

    /**
     * Returns the URI of a resource
     *
     * @param object|DomainObjectInterface $model
     * @return string
     */
    private function getUriToResource($model)
    {
        return $this->getUriToNestedResource(null, $model);
    }


    /**
     * Returns the data for the given lazy object storage
     *
     * @param LazyObjectStorage            $lazyObjectStorage
     * @param string                       $propertyKey
     * @param object|DomainObjectInterface $model
     * @return array|string
     */
    private function extractFromLazyObjectStorage(LazyObjectStorage $lazyObjectStorage, $propertyKey, $model = null)
    {
        // Get the first level of nested objects
        if ($this->currentModelDataDepth < 1) {
            $this->currentModelDataDepth += 1;
            $returnData = array();

            // Collect each object of the lazy object storage
            foreach ($lazyObjectStorage as $subObject) {
                $returnData[] = $this->extract($subObject);
            }
            $this->currentModelDataDepth -= 1;

            return $returnData;
        }

        if (!$model) {
            return [];
        }

        return $this->getUriToNestedResource($propertyKey, $model);
    }

    /**
     * Returns the data for the given lazy object storage
     *
     * @param LazyLoadingProxy $proxy
     * @param boolean          $forceExtract
     * @return array
     */
    private function extractFromLazyLoadingProxy(LazyLoadingProxy $proxy, $forceExtract)
    {
        //Get only the first level of nested objects
        if ($this->currentModelDataDepth >= 1 && !$forceExtract) {
            return [];
        }

        $this->currentModelDataDepth += 1;
        $returnData = $this->extract($proxy->_loadRealInstance());
        $this->currentModelDataDepth -= 1;

        return $returnData;
    }

    /**
     * Returns the property data from the given model
     *
     * @param object|DomainObjectInterface $model
     * @param string                       $propertyKey
     * @return mixed
     */
    private function getModelProperty($model, $propertyKey)
    {
        $propertyValue = $model->_getProperty($propertyKey);
        if (is_object($propertyValue)) {
            if ($propertyValue instanceof LazyObjectStorage) {
                $propertyValue = iterator_to_array($propertyValue);

                // Transform objects recursive
                foreach ($propertyValue as $childPropertyKey => $childPropertyValue) {
                    if (is_object($childPropertyValue)) {
                        $propertyValue[$childPropertyKey] = $this->extract($childPropertyValue);
                    }
                }
                $propertyValue = array_values($propertyValue);
            } else {
                $propertyValue = $this->extract($propertyValue);
            }
        } elseif (!$propertyValue) {
            return null;
        }

        return $propertyValue;
    }

    /**
     * Transform traversable
     *
     * @param \Traversable|array $traversable
     * @return array
     */
    protected function transformTraversable($traversable)
    {
        return array_values(
            array_map(array($this, 'extract'), is_array($traversable) ? $traversable : iterator_to_array($traversable))
        );
    }

    /**
     * Retrieve data from a file reference
     *
     * @param \TYPO3\CMS\Core\Resource\ResourceInterface|Folder|\TYPO3\CMS\Core\Resource\AbstractFile $originalResource
     * @return array
     */
    protected function transformFileReference($originalResource)
    {
        static $depth = 0;
        if ($originalResource instanceof AbstractFileFolder) {
            $depth += 1;
            if ($depth > 10) {
                throw new \RuntimeException('Max nesting level');
            }
            $result = $this->transformFileReference($originalResource->getOriginalResource());
            $depth -= 1;

            return $result;
        }

        try {
            if ($originalResource instanceof Folder) {
                $filesInFolder = array();
                foreach ($originalResource->getFiles() as $currentFile) {
                    $filesInFolder[] = $this->transformFileReference($currentFile);
                }

                return $filesInFolder;
            }

            if ($originalResource instanceof FileReference) {
                // This would expose all data
                // return $originalResource->getProperties();

                list($title, $description) = $this->getTitleAndDescription($originalResource);

                return array(
                    'uid'          => intval($originalResource->getReferenceProperty('uid_local')),
                    'referenceUid' => $originalResource->getUid(),
                    'name'         => $originalResource->getName(),
                    'mimeType'     => $originalResource->getMimeType(),
                    'url'          => $originalResource->getPublicUrl(),
                    'size'         => $originalResource->getSize(),
                    'title'        => $title,
                    'description'  => $description,
                );
            }

            if ($originalResource instanceof FileInterface) {
                return array(
                    'name'     => $originalResource->getName(),
                    'mimeType' => $originalResource->getMimeType(),
                    'url'      => $originalResource->getPublicUrl(),
                    'size'     => $originalResource->getSize(),
                );
            }

            return array(
                'name' => $originalResource->getName(),
            );
        } catch (\RuntimeException $exception) {
            return array();
        }
    }

    /**
     * Get the title and description of a File
     *
     * @param FileReference $fileReference
     * @return array
     */
    private function getTitleAndDescription(FileReference $fileReference)
    {
        $title = '';
        $description = '';
        try {
            $title = $fileReference->getTitle();
        } catch (\InvalidArgumentException $exception) {
            $message = 'An invalid argument for the title has been passed!';
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }
        try {
            $description = $fileReference->getDescription();
        } catch (\InvalidArgumentException $exception) {
            $message = 'An invalid argument for the description has been passed!';
            $this->getLogger()->log(LogLevel::ERROR, $message, array('exception' => $exception));
        }

        return array($title, $description);
    }

    /**
     * Returns the logger
     *
     * @return Logger
     */
    protected function getLogger()
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * Returns the recursion value of the object
     *
     * @param object $propertyValue
     * @return int Returns 0 if the object has not been processed before
     */
    private function getObjectRecursionValue($propertyValue)
    {
        assert(is_object($propertyValue), sprintf('Input must be an object %s given', gettype($propertyValue)));
        $propertyValueHash = spl_object_hash($propertyValue);

        return isset(static::$handledModels[$propertyValueHash])
            ? static::$handledModels[$propertyValueHash]
            : 0;
    }

    /**
     * @param mixed  $value
     * @param string $key
     * @param object $model
     * @return array|mixed|string
     */
    private function transformObjectProperty($value, $key, $model = null)
    {
        assert(is_object($value), sprintf('Value must be an object %s given', gettype($value)));
        assert(is_scalar($key), sprintf('Property key must either be a string or int, %s given', gettype($key)));
        assert(
            is_null($model) || is_object($model),
            sprintf('Model must either be Null or an object, %s given', gettype($model))
        );

        if ($this->getObjectRecursionValue($value) > 0) { // Recursion detected
            if (method_exists($value, 'getUid')) {
                return $this->getUriToNestedResource($key, $value);
            }

            return $value;
        }

        // No recursion detected
        if ($value instanceof LazyLoadingProxy) {
            return $this->extractFromLazyLoadingProxy(
                $value,
                $model && ($model instanceof Typo3CoreCategory) // Force extraction of TYPO3 Categories
            );
        } elseif ($value instanceof LazyObjectStorage) {
            return $this->extractFromLazyObjectStorage($value, $key, $model);
        }

        return $this->extract($value);
    }

//    /**
//     * @param mixed  $value
//     * @param string $key
//     * @return array|mixed|string
//     */
//    private function transformArrayProperty($value, $key)
//    {
//        assert(is_object($value), sprintf('Value must be an object %s given', gettype($value)));
//        assert(is_string($key), sprintf('Property key must be a string %s given', gettype($key)));
//
//        if ($this->getObjectRecursionValue($value) > 0) { // Recursion detected
//            if (method_exists($value, 'getUid')) {
//                return $this->getUriToNestedResource($key, $value);
//            }
//
//            return $value;
//        }
//
//        if ($value instanceof LazyLoadingProxy) {
//            return $this->extractFromLazyLoadingProxy($value, false);
//        } elseif ($value instanceof LazyObjectStorage) {
//            return $this->extractFromLazyObjectStorage($value, $key);
//        }
////        if ($value instanceof LazyLoadingProxy || $value instanceof LazyObjectStorage) {
////            throw new \InvalidArgumentException(
////                'Value must not be a Lazy Proxy instance because it\'s owner is not known'
////            );
////        }
//
//        // No recursion detected
//        return $this->extract($value);
//    }

    private function assertExtractableType($input)
    {
        if (is_resource($input)) {
            new \InvalidArgumentException('Can not extract data from resources');
        }
    }
}
