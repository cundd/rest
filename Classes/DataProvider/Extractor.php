<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Exception\InvalidArgumentException;
use DateTime;
use DateTimeInterface;
use Prophecy\Exception\Call\UnexpectedCallException as ProphecyUnexpectedCallException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Traversable;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

use function is_callable;

/**
 * Class to prepare/extract the data to be sent from objects
 */
class Extractor implements ExtractorInterface
{
    /**
     * Logger instance
     */
    protected ?LoggerInterface $logger;

    /**
     * The current depth when preparing model data for output
     */
    protected int $depthOfObjectTreeTraversal = 0;

    /**
     * The maximum depth when preparing model data for output
     */
    protected int $maxDepthOfObjectTreeTraversal;

    /**
     * Dictionary of handled models to their count
     */
    protected static array $handledModels = [];

    /**
     * Extractor constructor
     */
    public function __construct(
        ?LoggerInterface $logger = null,
        int $maxDepthOfObjectTreeTraversal = 6,
    ) {
        $this->logger = $logger;
        $this->maxDepthOfObjectTreeTraversal = $maxDepthOfObjectTreeTraversal;
    }

    public function extract(mixed $input): string|int|bool|float|array|null
    {
        return $this->extractData($input, null, null);
    }

    /**
     * Returns the data from the given input
     *
     * @param string|int|null $key
     *
     * @return string|int|bool|float|array|null
     */
    private function extractData(
        $input,
        $key,
        ?object $owner,
    ) {
        InvalidArgumentException::assertObjectOrNull($owner);
        $this->assertValidKey($key);

        $this->assertExtractableType($input);

        if (is_null($input)) {
            return null;
        }

        if (is_scalar($input)) {
            return $input;
        }

        // Indexed array and dictionary
        if (is_array($input)) {
            return $this->transformCollection($input);
        }

        // Traversable
        if ($input instanceof Traversable && !method_exists($input, 'jsonSerialize')) {
            return $this->transformCollection(array_values(iterator_to_array($input)));
        }

        // Proxy
        if ($input instanceof LazyLoadingProxy) {
            return $this->extractData($input->_loadRealInstance(), $key, $owner);
        }

        // DateTime
        if ($input instanceof DateTimeInterface) {
            return $input->format(DateTime::ATOM);
        }

        // General object
        if (is_object($input)) {
            return $this->extractObjectDataIfNotRecursion($input, $key, $owner);
        }

        throw new \InvalidArgumentException(sprintf('Can not extract data from type %s', gettype($input)));
    }

    /**
     * @param string|int|null $key
     *
     * @return array|string
     */
    private function extractObjectDataIfNotRecursion(
        object $input,
        $key,
        ?object $owner,
    ) {
        InvalidArgumentException::assertObject($input);
        InvalidArgumentException::assertObjectOrNull($owner);
        $this->assertValidKey($key);
        $this->increaseObjectRecursionValue($input);

        // Check for recursion
        if ($this->getObjectRecursionValue($input) < 2
            && $this->getDepthOfObjectTreeTraversal() < $this->maxDepthOfObjectTreeTraversal
        ) {
            $this->increaseDepthOfObjectTreeTraversal();
            $result = $this->extractObjectData($input, $key);
            $this->decreaseDepthOfObjectTreeTraversal();
        } else {
            // Object is processed recursively, so we only return a URI
            if ($key && $owner) {
                // If a key and owner are given, this is a nested resource and we return an URI relative to the
                // owner/parent object
                $result = $this->getUriToNestedResource((string) $key, $owner);
            } else {
                $result = $this->getUriToResource($input);
            }
        }
        $this->decreaseObjectRecursionValue($input);

        return $result;
    }

    /**
     * @param string|int|null $key
     *
     * @return mixed
     */
    private function extractObjectData(object $input, $key): array
    {
        InvalidArgumentException::assertObject($input);
        $this->assertValidKey($key);
        if (method_exists($input, 'jsonSerialize')) {
            // jsonSerialize() can return anything but `resource`
            $properties = $input->jsonSerialize();
        } elseif ($input instanceof FileInterface) {
            return $this->transformFileReference($input);
        } elseif ($input instanceof AbstractFile) {
            return $this->transformFileReference($input->getOriginalResource());
        } elseif ($input instanceof DomainObjectInterface) {
            $properties = $input->_getProperties();
        } else {
            $properties = get_object_vars($input);
        }

        return $this->transformObjectProperties($input, $properties);
    }

    /**
     * Transform the properties
     *
     * @param DomainObjectInterface|object $model
     */
    private function transformObjectProperties(
        object $model,
        array $properties,
    ): array {
        assert(is_object($model), sprintf('Input must be an object %s given', gettype($model)));
        $transformedCollection = [];

        // Transform objects recursive
        foreach ($properties as $propertyKey => $propertyValue) {
            $transformedCollection[$propertyKey] = $this->extractData($propertyValue, $propertyKey, $model);
        }

        return $transformedCollection;
    }

    /**
     * Transform the values of a collection type
     */
    private function transformCollection(array $collection): array
    {
        $transformedCollection = [];

        foreach ($collection as $propertyKey => $propertyValue) {
            $transformedCollection[$propertyKey] = $this->extractData($propertyValue, $propertyKey, null);
        }

        return $transformedCollection;
    }

    /**
     * Returns the URI of a nested resource
     *
     * @param object|DomainObjectInterface $model
     */
    private function getUriToNestedResource(
        string $resourceKey,
        object $model,
    ): string {
        return $this->getUriToResource($model) . $resourceKey;
    }

    private function getUriRequestBase(): string
    {
        if (getenv('CUNDD_TEST') || !class_exists(GeneralUtility::class, false)) {
            $host = filter_var((isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''), FILTER_SANITIZE_URL);
            $protocol = ((!isset($_SERVER['HTTPS']) || 'on' != strtolower($_SERVER['HTTPS'])) ? 'http' : 'https');

            return $protocol . '://' . $host . '/';
        }

        return GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
    }

    /**
     * Returns the URI of a resource
     *
     * @param object|DomainObjectInterface $model
     */
    private function getUriToResource(object $model): string
    {
        $modelListingUri = $this->getUriRequestBase()
            . 'rest/'
            . Utility::getResourceTypeForClassName(get_class($model))
            . '/';

        $methodGetUidExists = method_exists($model, 'getUid') || is_callable([$model, 'getUid']);
        assert($methodGetUidExists, 'The URI to a resource without an UID is requested. This URI can not be generated');

        if ($methodGetUidExists) {
            return $modelListingUri . intval($model->getUid()) . '/';
        } else {
            return $modelListingUri;
        }
    }

    /**
     * Retrieve data from a file reference
     *
     * @param ResourceInterface|Folder|AbstractFile $originalResource
     */
    protected function transformFileReference($originalResource): array
    {
        static $depth = 0;
        if ($originalResource instanceof AbstractFileFolder) {
            ++$depth;
            if ($depth > 10) {
                throw new RuntimeException('Max nesting level');
            }
            $result = $this->transformFileReference($originalResource->getOriginalResource());
            --$depth;

            return $result;
        }

        try {
            if ($originalResource instanceof Folder) {
                $filesInFolder = [];
                foreach ($originalResource->getFiles() as $currentFile) {
                    $filesInFolder[] = $this->transformFileReference($currentFile);
                }

                return $filesInFolder;
            }

            if ($originalResource instanceof FileReference) {
                // This would expose all data
                // return $originalResource->getProperties();

                [$title, $description] = $this->getTitleAndDescription($originalResource);

                return [
                    'uid'          => intval($originalResource->getReferenceProperty('uid_local')),
                    'referenceUid' => $originalResource->getUid(),
                    'name'         => $originalResource->getName(),
                    'mimeType'     => $originalResource->getMimeType(),
                    'url'          => $originalResource->getPublicUrl(),
                    'size'         => $originalResource->getSize(),
                    'title'        => $title,
                    'description'  => $description,
                ];
            }

            if ($originalResource instanceof FileInterface) {
                return [
                    'name'     => $originalResource->getName(),
                    'mimeType' => $originalResource->getMimeType(),
                    'url'      => $originalResource->getPublicUrl(),
                    'size'     => $originalResource->getSize(),
                ];
            }

            return [
                'name' => $originalResource->getName(),
            ];
        } catch (ProphecyUnexpectedCallException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            return [];
        }
    }

    /**
     * Get the title and description of a File
     */
    private function getTitleAndDescription(FileReference $fileReference): array
    {
        $title = '';
        $description = '';
        try {
            $title = $fileReference->getTitle();
        } catch (\InvalidArgumentException $exception) {
            $message = 'An invalid argument for the title has been passed!';
            $this->getLogger()->log(LogLevel::ERROR, $message, ['exception' => $exception]);
        }
        try {
            $description = $fileReference->getDescription();
        } catch (\InvalidArgumentException $exception) {
            $message = 'An invalid argument for the description has been passed!';
            $this->getLogger()->log(LogLevel::ERROR, $message, ['exception' => $exception]);
        }

        return [$title, $description];
    }

    /**
     * Returns the logger
     */
    protected function getLogger(): LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * Returns the recursion value of the object
     *
     * @return int Returns 0 if the object has not been processed before
     */
    private function getObjectRecursionValue(object $object): int
    {
        InvalidArgumentException::assertObject($object);
        $objectHash = spl_object_hash($object);

        return static::$handledModels[$objectHash] ?? 0;
    }

    /**
     * Increase the recursion value for the given object
     */
    private function increaseObjectRecursionValue(object $object): int
    {
        InvalidArgumentException::assertObject($object);
        $objectHash = spl_object_hash($object);

        $value = static::$handledModels[$objectHash] ?? 0;
        ++$value;
        static::$handledModels[$objectHash] = $value;

        return $value;
    }

    /**
     * Decrease the recursion value for the given object
     */
    private function decreaseObjectRecursionValue(object $object): int
    {
        InvalidArgumentException::assertObject($object);
        $objectHash = spl_object_hash($object);

        $value = static::$handledModels[$objectHash] ?? 0;
        --$value;
        static::$handledModels[$objectHash] = $value;

        return $value;
    }

    /**
     * Returns the current depth of object tree traversal
     */
    private function getDepthOfObjectTreeTraversal(): int
    {
        return $this->depthOfObjectTreeTraversal;
    }

    /**
     * Increases the current depth of object tree traversal
     */
    private function increaseDepthOfObjectTreeTraversal(): int
    {
        ++$this->depthOfObjectTreeTraversal;

        return $this->depthOfObjectTreeTraversal;
    }

    /**
     * Decreases the current depth of object tree traversal
     */
    private function decreaseDepthOfObjectTreeTraversal(): int
    {
        --$this->depthOfObjectTreeTraversal;

        return $this->depthOfObjectTreeTraversal;
    }

    /**
     * Tests if the given input can be transformed
     */
    private function assertExtractableType($input)
    {
        if (is_resource($input)) {
            throw new \InvalidArgumentException('Can not extract data from resources');
        }
    }

    private function assertValidKey($key): void
    {
        if (false === (is_null($key) || is_scalar($key))) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Key must be either NULL or scalar, %s given',
                    is_object($key) ? get_class($key) : gettype($key)
                )
            );
        }
    }
}
