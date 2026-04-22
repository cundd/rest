<?php

declare(strict_types=1);

namespace Cundd\Rest\Log;

use Psr\Log\LoggerInterface as BaseLoggerInterface;
use Throwable;

interface LoggerInterface extends BaseLoggerInterface
{
    /**
     * Log the given request message and data
     *
     * @param array<string|int,mixed> $data
     */
    public function logRequest(string $message, array $data = []): void;

    /**
     * Log the given response message and data
     *
     * @param array<string|int,mixed> $data
     */
    public function logResponse(string $message, array $data = []): void;

    /**
     * Log the given exception
     */
    public function logException(Throwable $exception): void;
}
