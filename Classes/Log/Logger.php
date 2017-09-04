<?php


namespace Cundd\Rest\Log;


use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Logger implements LoggerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $concreteLogger;

    /**
     * Logger constructor
     *
     * @param \Psr\Log\LoggerInterface|null $concreteLogger
     */
    public function __construct(\Psr\Log\LoggerInterface $concreteLogger = null)
    {
        $this->concreteLogger = $concreteLogger
            ? $concreteLogger
            : GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    public function logRequest($message, array $data = [])
    {
        if ($this->getExtensionConfiguration('logRequests')) {
            $this->debug($message, $data);
        }
    }

    private function getExtensionConfiguration($key)
    {
        // Read the configuration from the globals
        static $configuration;
        if (!$configuration) {
            if (isset($GLOBALS['TYPO3_CONF_VARS'])
                && isset($GLOBALS['TYPO3_CONF_VARS']['EXT'])
                && isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'])
                && isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rest'])
            ) {
                $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rest']);
            }
        }

        return isset($configuration[$key]) ? $configuration[$key] : null;
    }

    public function debug($message, array $context = [])
    {
        $this->concreteLogger->debug($message, $context);
    }

    public function logResponse($message, array $data = [])
    {
        if ($this->getExtensionConfiguration('logResponse')) {
            $this->debug($message, $data);
        }
    }

    public function logException($exception)
    {
        $message = 'Uncaught exception #' . $exception->getCode() . ': ' . $exception->getMessage();
        $this->error($message, ['exception' => $exception]);
    }

    public function error($message, array $context = [])
    {
        $this->concreteLogger->error($message, $context);
    }

    public function emergency($message, array $context = [])
    {
        $this->concreteLogger->emergency($message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->concreteLogger->alert($message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->concreteLogger->critical($message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->concreteLogger->warning($message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->concreteLogger->notice($message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->concreteLogger->info($message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $this->concreteLogger->log($level, $message, $context);
    }
}
