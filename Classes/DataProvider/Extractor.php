<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use BackedEnum;
use Cundd\Rest\Exception\InvalidArgumentException;
use DateTime;
use DateTimeInterface;
use Psr\Http\Message\UriInterface;
use Traversable;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder as CoreFolder;
use TYPO3\CMS\Extbase\Domain\Model\File as ExtbaseFile;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Domain\Model\Folder as ExtbaseFolder;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

use function is_callable;

/**
 * Class to prepare/extract the data to be sent from objects
 *
 * @phpstan-import-type Data from ExtractorInterface
 */
class Extractor implements ExtractorInterface
{
    /**
     * Current depth when preparing model data for output
     */
    protected int $depthOfObjectTreeTraversal = 0;

    /**
     * Maximum depth when preparing model data for output
     */
    protected int $maxDepthOfObjectTreeTraversal;

    /**
     * Dictionary of handled models to their count
     *
     * @var array<string,int>
     */
    protected static array $handledModels = [];

    public function __construct(
        protected readonly FileExtractor $fileExtractor,
        int $maxDepthOfObjectTreeTraversal = 6,
    ) {
        $this->maxDepthOfObjectTreeTraversal = $maxDepthOfObjectTreeTraversal;
    }

    public function extract(
        UriInterface $baseUri,
        mixed $input,
    ): string|int|bool|float|array|null {
        return $this->extractData($baseUri, $input, null, null);
    }

    /**
     * Returns the data from the given input
     *
     * @return Data
     */
    private function extractData(
        UriInterface $baseUri,
        mixed $input,
        string|int|null $key,
        ?object $owner,
    ): string|int|bool|float|array|null {
        $this->assertExtractableType($input);

        if (is_null($input)) {
            return null;
        }

        if (is_scalar($input)) {
            return $input;
        }

        // Indexed array and dictionary
        if (is_array($input)) {
            return $this->transformCollection($baseUri, $input);
        }

        // Traversable
        if ($input instanceof Traversable && !method_exists($input, 'jsonSerialize')) {
            return $this->transformCollection($baseUri, array_values(iterator_to_array($input)));
        }

        // Proxy
        if ($input instanceof LazyLoadingProxy) {
            return $this->extractData($baseUri, $input->_loadRealInstance(), $key, $owner);
        }

        // DateTime
        if ($input instanceof DateTimeInterface) {
            return $input->format(DateTime::ATOM);
        }
        if ($input instanceof BackedEnum) {
            return $input->value;
        }

        // General object
        if (is_object($input)) {
            return $this->extractObjectDataIfNotRecursion($baseUri, $input, $key, $owner);
        }

        throw new InvalidArgumentException(
            sprintf('Can not extract data from type %s', gettype($input))
        );
    }

    /**
     * @return array<mixed,mixed>|string
     */
    private function extractObjectDataIfNotRecursion(
        UriInterface $baseUri,
        object $input,
        int|string|null $key,
        ?object $owner,
    ): array|string {
        $this->increaseObjectRecursionValue($input);

        // Check for recursion
        if ($this->getObjectRecursionValue($input) < 2
            && $this->getDepthOfObjectTreeTraversal() < $this->maxDepthOfObjectTreeTraversal
        ) {
            $this->increaseDepthOfObjectTreeTraversal();
            $result = $this->extractObjectData($baseUri, $input, $key);
            $this->decreaseDepthOfObjectTreeTraversal();
        } else {
            // Object is processed recursively, so we only return a URI
            if ($key && $owner) {
                // If a key and owner are given, this is a nested resource and
                // we return an URI relative to the owner/parent object
                $result = $this->getUriToNestedResource($baseUri, (string) $key, $owner);
            } else {
                $result = $this->getUriToResource($baseUri, $input);
            }
        }
        $this->decreaseObjectRecursionValue($input);

        return $result;
    }

    /**
     * @return array<string,Data>|list<array<string,mixed>>
     */
    private function extractObjectData(UriInterface $baseUri, object $input, string|int|null $key): array
    {
        $isFalObject = $input instanceof FileInterface
            || $input instanceof ExtbaseFile
            || $input instanceof ExtbaseFileReference
            || $input instanceof ExtbaseFolder
            || $input instanceof CoreFolder;

        if (method_exists($input, 'jsonSerialize')) { // TODO: Use \JsonSerializable
            // jsonSerialize() can return anything but `resource`
            $properties = (array) $input->jsonSerialize();
        } elseif ($isFalObject) {
            return $this->fileExtractor->extract($input);
        } elseif ($input instanceof DomainObjectInterface) {
            $properties = $input->_getProperties();
        } else {
            $properties = get_object_vars($input);
        }

        return $this->transformObjectProperties($baseUri, $input, $properties);
    }

    /**
     * Transform the properties
     *
     * @param DomainObjectInterface|object $model
     * @param array<string, mixed>         $properties
     *
     * @return array<string,Data>
     */
    private function transformObjectProperties(
        UriInterface $baseUri,
        object $model,
        array $properties,
    ): array {
        $transformedCollection = [];

        // Transform objects recursive
        foreach ($properties as $propertyKey => $propertyValue) {
            $transformedCollection[(string) $propertyKey] = $this->extractData(
                $baseUri,
                $propertyValue,
                $propertyKey,
                $model
            );
        }

        return $transformedCollection;
    }

    /**
     * Transform the values of a collection type
     *
     * @param array<string|int,mixed> $collection
     *
     * @return array<string|int,Data>
     */
    private function transformCollection(UriInterface $baseUri, array $collection): array
    {
        $transformedCollection = [];

        foreach ($collection as $propertyKey => $propertyValue) {
            $transformedCollection[$propertyKey] = $this->extractData(
                $baseUri,
                $propertyValue,
                $propertyKey,
                null
            );
        }

        return $transformedCollection;
    }

    /**
     * Return the URI of a nested resource
     *
     * @template T of object|DomainObjectInterface
     *
     * @param T $model
     *
     * @return non-empty-string
     */
    private function getUriToNestedResource(
        UriInterface $baseUri,
        string $resourceKey,
        object $model,
    ): string {
        return $this->getUriToResource($baseUri, $model) . $resourceKey;
    }

    /**
     * Return the URI of a resource
     *
     * @template T of object|DomainObjectInterface
     *
     * @param T $model
     *
     * @return non-empty-string
     */
    private function getUriToResource(UriInterface $baseUri, object $model): string
    {
        $path = 'rest/'
            . Utility::getResourceTypeForClassName(get_class($model))
            . '/';
        $modelListingUri = $baseUri->withPath($path);

        $methodGetUidExists = $model instanceof DomainObjectInterface
            || is_callable([$model, 'getUid']);

        if ($methodGetUidExists) {
            return $modelListingUri . intval($model->getUid()) . '/';
        }
        trigger_error(
            'The URI to a resource without an UID is requested. This URI can not be generated',
            E_USER_WARNING
        );

        $modelListingUriString = (string) $modelListingUri;
        assert('' !== $modelListingUriString);

        return $modelListingUriString;
    }

    /**
     * Return the recursion value of the object
     *
     * @return int Returns 0 if the object has not been processed before
     */
    private function getObjectRecursionValue(object $object): int
    {
        $objectHash = spl_object_hash($object);

        return static::$handledModels[$objectHash] ?? 0;
    }

    /**
     * Increase the recursion value for the given object
     */
    private function increaseObjectRecursionValue(object $object): int
    {
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
        $objectHash = spl_object_hash($object);

        $value = static::$handledModels[$objectHash] ?? 0;
        --$value;
        static::$handledModels[$objectHash] = $value;

        return $value;
    }

    /**
     * Return the current depth of object tree traversal
     */
    private function getDepthOfObjectTreeTraversal(): int
    {
        return $this->depthOfObjectTreeTraversal;
    }

    /**
     * Increase the current depth of object tree traversal
     */
    private function increaseDepthOfObjectTreeTraversal(): int
    {
        ++$this->depthOfObjectTreeTraversal;

        return $this->depthOfObjectTreeTraversal;
    }

    /**
     * Decrease the current depth of object tree traversal
     */
    private function decreaseDepthOfObjectTreeTraversal(): int
    {
        --$this->depthOfObjectTreeTraversal;

        return $this->depthOfObjectTreeTraversal;
    }

    /**
     * Test if the given input can be transformed
     */
    private function assertExtractableType(mixed $input): void
    {
        if (is_resource($input)) {
            throw new InvalidArgumentException('Can not extract data from resources');
        }
    }
}
