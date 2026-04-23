<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

use Traversable;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class MyNestedModelWithObjectStorage extends MyNestedModel
{
    /**
     * @var ObjectStorage<BaseModel>|array<BaseModel>|Traversable<BaseModel>
     */
    protected iterable $children;

    /**
     * @return ObjectStorage<BaseModel>|array<BaseModel>|Traversable<BaseModel>
     */
    public function getChildren(): iterable
    {
        return $this->children;
    }

    /**
     * @param iterable<BaseModel>|ObjectStorage<BaseModel>|array<BaseModel>|Traversable<BaseModel> $children
     */
    public function setChildren(iterable $children): void
    {
        $this->children = $children;
    }
}
