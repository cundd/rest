<?php

declare(strict_types=1);

namespace Cundd\Rest\Log;

use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Logger extends AbstractLogger
{
    private \Psr\Log\LoggerInterface $concreteLogger;

    public function __construct(\Psr\Log\LoggerInterface $concreteLogger = null)
    {
        $this->concreteLogger = $concreteLogger
            ?? GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->concreteLogger->log($level, $message, $context);
    }
}
