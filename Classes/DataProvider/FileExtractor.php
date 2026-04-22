<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Exception\InvalidArgumentException;
use Prophecy\Exception\Call\UnexpectedCallException as ProphecyUnexpectedCallException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Core\Resource\Folder as CoreFolder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\File as ExtbaseFile;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Domain\Model\Folder as ExtbaseFolder;

/**
 * Class to prepare/extract the data from File Abstraction Layer objects
 *
 * @phpstan-import-type Data from ExtractorInterface
 */
class FileExtractor
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @return array<string,mixed>|list<array<string,mixed>>
     */
    public function extract(mixed $input): array
    {
        if ($input instanceof FileInterface) {
            return $this->transformCoreFileObject($input);
        } elseif ($input instanceof CoreFolder) {
            return $this->transformCoreFolderObject($input);
        } elseif ($input instanceof ExtbaseFile || $input instanceof ExtbaseFileReference) {
            return $this->transformExtbaseFileObject($input);
        } elseif ($input instanceof ExtbaseFolder) {
            return $this->transformExtbaseFolderObject($input);
        }

        throw new InvalidArgumentException(
            'Could not extract data from object of class ' . get_class($input)
        );
    }

    /**
     * Retrieve data from a file
     *
     * @return array<string,mixed>
     */
    private function transformCoreFileObject(FileInterface $input): array
    {
        if (!$input instanceof CoreFileReference) {
            return [
                'name'     => $input->getName(),
                'mimeType' => $input->getMimeType(),
                'url'      => $input->getPublicUrl(),
                'size'     => $input->getSize(),
            ];
        }

        // This would expose all data
        // return $input->getProperties();

        try {
            [$title, $description] = $this->getTitleAndDescription($input);
        } catch (ProphecyUnexpectedCallException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            $title = '';
            $description = '';
        }

        return [
            'uid'          => intval($input->getReferenceProperty('uid_local')),
            'referenceUid' => $input->getUid(),
            'name'         => $input->getName(),
            'mimeType'     => $input->getMimeType(),
            'url'          => $input->getPublicUrl(),
            'size'         => $input->getSize(),
            'title'        => $title,
            'description'  => $description,
        ];
    }

    /**
     * Retrieve data from a file reference
     *
     * @return array<string,mixed>
     */
    private function transformExtbaseFileObject(ExtbaseFile|ExtbaseFileReference $input): array
    {
        return $this->transformCoreFileObject($input->getOriginalResource());
    }

    /**
     * Retrieve data for each file in a folder
     *
     * @return list<array<string,mixed>>
     */
    private function transformCoreFolderObject(CoreFolder $input): array
    {
        $filesInFolder = [];
        foreach ($input->getFiles() as $currentFile) {
            $filesInFolder[] = $this->transformCoreFileObject($currentFile);
        }

        return $filesInFolder;
    }

    /**
     * Retrieve data for each file in a folder
     *
     * @return list<array<string,mixed>>
     */
    private function transformExtbaseFolderObject(ExtbaseFolder $input): array
    {
        $original = $input->getOriginalResource();
        if (null === $original) {
            return [];
        }

        return $this->transformCoreFolderObject($original);
    }

    /**
     * Get the title and description of a File
     *
     * @return array{0:string,1:string}
     */
    private function getTitleAndDescription(CoreFileReference $fileReference): array
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

    protected function getLogger(): LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
