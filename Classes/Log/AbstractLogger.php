<?php
declare(strict_types=1);


namespace Cundd\Rest\Log;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractLogger extends \Psr\Log\AbstractLogger implements LoggerInterface
{
    public function logRequest($message, array $data = [])
    {
        if ($this->getExtensionConfiguration('logRequests')) {
            $this->debug($message, $data);
        }
    }

    public function logResponse($message, array $data = [])
    {
        if ($this->getExtensionConfiguration('logResponse')) {
            $this->debug($message, $data);
        }
    }

    protected function getExtensionConfiguration($key)
    {
        try {
            return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('rest', $key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Logs the given exception
     *
     * @param \Exception|\Throwable $exception
     */
    public function logException($exception)
    {
        $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
        $this->error($message, ['exception' => $exception]);
    }
}
