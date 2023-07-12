<?php

declare(strict_types=1);

namespace Cundd\Rest\Log;

use Exception;
use Throwable;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractLogger extends \Psr\Log\AbstractLogger implements LoggerInterface
{
    public function logRequest(string $message, array $data = []): void
    {
        if ($this->getExtensionConfiguration('logRequests')) {
            $this->debug($message, $data);
        }
    }

    public function logResponse(string $message, array $data = []): void
    {
        if ($this->getExtensionConfiguration('logResponse')) {
            $this->debug($message, $data);
        }
    }

    protected function getExtensionConfiguration($key)
    {
        if (class_exists(GeneralUtility::class) && class_exists(ExtensionConfiguration::class)) {
            try {
                return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('rest', $key);
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Logs the given exception
     *
     * @param Throwable $exception
     */
    public function logException(Throwable $exception): void
    {
        $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
        $this->error($message, ['exception' => $exception]);
    }
}
