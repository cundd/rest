<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

class MyNestedJsonSerializeModel extends MyNestedModel
{
    /**
     * @return array{base:string,child:BaseModel}
     */
    public function jsonSerialize(): array
    {
        return [
            'base'  => $this->base,
            'child' => $this->child,
        ];
    }
}
