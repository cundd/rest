<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

class Parentheses
{
    private const OPEN = '(';
    private const CLOSE = ')';

    private $value = '';

    /**
     * Parentheses constructor
     *
     * @param string $value
     */
    private function __construct(string $value)
    {
        if ($value !== self::OPEN && $value !== self::CLOSE) {
            throw new \InvalidArgumentException(sprintf('Invalid parentheses "%s"', $value));
        }
        $this->value = $value;
    }

    public static function open(): self
    {
        return new static(self::OPEN);
    }

    public static function close(): self
    {
        return new static(self::CLOSE);
    }


    public function __toString()
    {
        return $this->value;
    }
}
