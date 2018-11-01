<?php


namespace Cundd\Rest\Log;

abstract class AbstractLogger extends \Psr\Log\AbstractLogger implements LoggerInterface
{
    public function logRequest($message, array $data = [])
    {
        if ($this->getExtensionConfiguration('logRequests')) {
            $this->debug($message, $data);
        }
    }

    protected function getExtensionConfiguration($key)
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

    public function logResponse($message, array $data = [])
    {
        if ($this->getExtensionConfiguration('logResponse')) {
            $this->debug($message, $data);
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
