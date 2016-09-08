<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 08/09/16
 * Time: 18:55
 */

namespace Cundd\Rest\Path;

/**
 * Instance providing information about a request path
 */
class PathInfo implements \ArrayAccess
{
    /**
     * @var string
     */
    private $vendor;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var string
     */
    private $model;

    /**
     * PathInfo constructor
     *
     * @param string $vendor
     * @param string $extension
     * @param string $model
     */
    public function __construct($vendor, $extension, $model)
    {
        $this->vendor = (string)$vendor;
        $this->extension = (string)$extension;
        $this->model = (string)$model;
    }

    /**
     * Returns the requested vendor
     *
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Returns the requested extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Returns the model path
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return intval($offset) < 3;
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        $index = intval($offset);
        switch ($index) {
            case 0:
                return $this->vendor;
            case 1:
                return $this->extension;
            case 2:
                return $this->model;
            default:
                throw new \OutOfRangeException(sprintf(
                    'Offset %d must be either 0 (=vendor), 1 (=extension), 2 (=model)',
                    $index
                ));
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Path Info instances must not be modified');
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Path Info instances must not be modified');
    }
}
