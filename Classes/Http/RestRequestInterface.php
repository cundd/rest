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
     * Return the original request
     *
     * @return ServerRequestInterface
     */
    public function getOriginalRequest();

    /**
     * Return the request path (eventually aliases have been mapped)
     *
     * @return string
     */
    public function getPath();

    /**
     * Return the requested resource type
     *
     * The resource type is the first part of the request path, after mapping aliases
     *
     * @return ResourceType
     */
    public function getResourceType();

    /**
     * Return the sent data
     *
     * @return mixed
     */
    public function getSentData();

    /**
     * Return the requested format
     *
     * @return Format
     */
    public function getFormat();

    /**
     * Return if the request is a preflight request
     *
     * @return bool
     */
    public function isPreflight();

    /**
     * Return if the request wants to write data
     *
     * @return bool
     */
    public function isWrite();

    /**
     * Return if the request wants to read data
     *
     * @return bool
     */
    public function isRead();

    /**
     * Return the key to use for the root object if addRootObjectForCollection is enabled
     *
     * @return string
     */
    public function getRootObjectKey();

    /**
     * Return an instance with the given format
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message.
     *
     * @param Format $format
     * @return static
     */
    public function withFormat(Format $format);

    /**
     * Return an instance with the given Resource Type
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message.
     *
     * @param ResourceType $resourceType
     * @return static
     */
    public function withResourceType(ResourceType $resourceType);
}
