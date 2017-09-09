<?php


namespace Cundd\Rest\Tests\Functional\Integration;


use Psr\Log\AbstractLogger;

class StreamLogger extends AbstractLogger
{
    private $stream;

    /**
     * StreamLogger constructor.
     *
     * @param $stream
     */
    public function __construct($stream = null)
    {
        if (!is_resource($stream)) {
            $stream = STDERR;
        }
        $this->stream = $stream;
    }


    public function log($level, $message, array $context = [])
    {
        fwrite($this->stream, sprintf('[%s] %s', strtoupper($level), $message));
    }
}
