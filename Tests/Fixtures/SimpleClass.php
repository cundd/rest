<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

class SimpleClass
{
    public mixed $firstName;

    public mixed $lastName;

    protected ?int $uid;

    protected ?int $pid;

    /**
     * @param array<string,mixed> $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }
}
