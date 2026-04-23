<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

class MyModel extends BaseModel
{
    protected string $name = 'Initial value';

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
