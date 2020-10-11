<?php
declare(strict_types=1);

namespace Cundd\Rest\VirtualObject;

/**
 * Class VirtualObject
 *
 * A simple wrapper object for data
 */
class VirtualObject
{
    /**
     * The data
     *
     * @var array
     */
    protected $data = [];

    /**
     * VirtualObject constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Sets the data
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns the data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the value for the given key
     *
     * @param string $key
     * @return mixed
     */
    public function valueForKey(string $key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Sets the value for the given key
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setValueForKey(string $key, $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Return the data if transformed to JSON
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->getData();
    }
}
