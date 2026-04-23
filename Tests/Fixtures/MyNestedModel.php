<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Fixtures;

use DateTime;

class MyNestedModel extends BaseModel
{
    protected string $base = 'Base';

    protected DateTime $date;

    /**
     * @var BaseModel
     */
    protected mixed $child;

    public function __construct()
    {
        parent::__construct();
        $this->child = new MyModel();
        $this->date = new DateTime();
    }

    public function setBase(string $base): void
    {
        $this->base = $base;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function setChild(mixed $child): void
    {
        $this->child = $child;
    }

    public function getChild(): BaseModel
    {
        return $this->child;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
}
