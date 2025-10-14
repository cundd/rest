<?php

declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use InvalidArgumentException;

class Parentheses
{
    public const OPEN = '(';
    public const CLOSE = ')';

    private $value = '';

    /**
     * Parentheses constructor
     */
    private function __construct(string $value)
    {
        if (self::OPEN !== $value && self::CLOSE !== $value) {
            throw new InvalidArgumentException(sprintf('Invalid parentheses "%s"', $value));
        }
        $this->value = $value;
    }

    public static function open(): self
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static(self::OPEN);
        }

        return $instance;
    }

    public static function close(): self
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static(self::CLOSE);
        }

        return $instance;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
