<?php

namespace Cundd\Rest\Log;

use Psr\Log\LoggerInterface as BaseLoggerInterface;

interface LoggerInterface extends BaseLoggerInterface
{
    /**
     * Logs the given request message and data
     *
     * @param string $message
     * @param array  $data
     */
    public function logRequest($message,array  $data = []);

    /**
     * Logs the given response message and data
     *
     * @param string $message
     * @param array  $data
     */
    public function logResponse($message, array $data = []);

    /**
     * Logs the given exception
     *
     * @param \Exception|\Throwable $exception
     */
    public function logException($exception);
}
