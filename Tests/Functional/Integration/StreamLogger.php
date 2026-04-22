<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Log\AbstractLogger;
use Stringable;

class StreamLogger extends AbstractLogger
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * @param resource|null $stream
     */
    public function __construct($stream = null)
    {
        if (!is_resource($stream)) {
            $stream = STDERR;
        }
        $this->stream = $stream;
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        fwrite($this->stream, sprintf('[%s] %s', strtoupper($level), $message));
    }
}
