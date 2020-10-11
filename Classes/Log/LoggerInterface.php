<?php
declare(strict_types=1);

namespace Cundd\Rest\Log;

use Psr\Log\LoggerInterface as BaseLoggerInterface;
use Throwable;

interface LoggerInterface extends BaseLoggerInterface
{
    /**
     * Logs the given request message and data
     *
     * @param string $message
     * @param array  $data
     */
    public function logRequest(string $message, array $data = []): void;

    /**
     * Logs the given response message and data
     *
     * @param string $message
     * @param array  $data
     */
    public function logResponse(string $message, array $data = []): void;

    /**
     * Logs the given exception
     *
     * @param Throwable $exception
     */
    public function logException(Throwable $exception): void;
}
