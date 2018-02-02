<?php

namespace Cundd\Rest\Http;

use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Domain\Model\ResourceType;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Specialized Request
 */
interface RestRequestInterface extends ServerRequestInterface
{
    /**
     * Returns the original request
     *
     * @return ServerRequestInterface
     */
    public function getOriginalRequest();

    /**
     * Returns the request path (eventually aliases have been mapped)
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns the requested resource type
     *
     * The resource type is the first part of the request path, after mapping aliases
     *
     * @return ResourceType
     */
    public function getResourceType();

    /**
     * Returns the sent data
     *
     * @return mixed
     */
    public function getSentData();

    /**
     * Returns the requested format
     *
     * @return Format
     */
    public function getFormat();

    /**
     * Returns if the request is a preflight request
     *
     * @return bool
     */
    public function isPreflight();

    /**
     * Returns if the request wants to write data
     *
     * @return bool
     */
    public function isWrite();

    /**
     * Returns if the request wants to read data
     *
     * @return bool
     */
    public function isRead();

    /**
     * Returns the key to use for the root object if addRootObjectForCollection
     * is enabled
     *
     * @return string
     */
    public function getRootObjectKey();

    /**
     * @param $format
     * @return static
     */
    public function withFormat($format);
}
