<?php

declare(strict_types=1);

namespace Cundd\Rest\Handler;

interface HandlerDescriptionInterface
{
    /**
     * Return the description of the handler
     */
    public function getDescription(): string;
}
