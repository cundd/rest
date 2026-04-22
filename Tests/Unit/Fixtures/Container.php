<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Fixtures;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @param array<class-string|non-empty-string,object> $container
     */
    public function __construct(private array $container = [])
    {
    }

    /**
     * @param class-string|non-empty-string $id
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFoundException(sprintf('No object for ID "%s" found', $id));
        }

        $impl = $this->container[$id];
        if (is_callable($impl)) {
            $arguments = func_get_args();
            array_shift($arguments);

            return $impl(...$arguments);
        } else {
            return $impl;
        }
    }

    /**
     * @param class-string|non-empty-string $id
     */
    public function has(string $id): bool
    {
        return isset($this->container[$id]);
    }

    /**
     * @param class-string|non-empty-string $id
     */
    public function set(string $id, object $impl): void
    {
        $this->container[$id] = $impl;
    }
}
