<?php

declare(strict_types=1);

namespace Cundd\Rest\Log;

use Psr\Log\LoggerInterface as BaseLoggerInterface;
use Throwable;

interface LoggerInterface extends BaseLoggerInterface
{
    /**
     * Logs the given request message and data
     */
    public function logRequest(string $message, array $data = []): void;

    /**
     * Logs the given response message and data
     */
    public function logResponse(string $message, array $data = []): void;

    /**
     * Logs the given exception
     */
    public function logException(Throwable $exception): void;
}
