<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Fixtures;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private $container = [];

    public function __construct(array $container = [])
    {
        $this->container = $container;
    }

    public function get($id)
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

    public function has($id)
    {
        return isset($this->container[$id]);
    }

    /**
     * @param string $id
     * @param object $impl
     */
    public function set(string $id, $impl)
    {
        $this->container[$id] = $impl;
    }
}
