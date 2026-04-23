<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

use JsonSerializable;

class SimpleClassJsonSerializable extends SimpleClass implements JsonSerializable
{
    /**
     * @return array{firstName:string,lastName:string,uid:?int,pid:?int}
     */
    public function jsonSerialize(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'uid'       => $this->uid,
            'pid'       => $this->pid,
        ];
    }
}
