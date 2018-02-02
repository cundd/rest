<?php


namespace Cundd\Rest\Log;


use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Logger extends AbstractLogger
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

    public function log($level, $message, array $context = [])
    {
        $this->concreteLogger->log($level, $message, $context);
    }
}
