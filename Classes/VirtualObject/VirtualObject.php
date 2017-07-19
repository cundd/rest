<?php

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
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the value for the given key
     *
     * @param string $key
     * @return mixed
     */
    public function valueForKey($key)
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
    public function setValueForKey($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Return the data if transformed to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getData();
    }
}
