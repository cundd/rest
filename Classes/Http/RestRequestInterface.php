<?php

declare(strict_types=1);

namespace Cundd\Rest\Http;

use Cundd\Rest\Request\Format;
use Cundd\Rest\Request\ResourceType;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Specialized Request
 */
interface RestRequestInterface extends ServerRequestInterface
{
    /**
     * Return the original request
     */
    public function getOriginalRequest(): ServerRequestInterface;

    /**
     * Return the request path (eventually aliases have been mapped)
     */
    public function getPath(): string;

    /**
     * Return the requested resource type
     *
     * The resource type is the first part of the request path, after mapping aliases
     */
    public function getResourceType(): ResourceType;

    /**
     * Return the sent data
     */
    public function getSentData();

    /**
     * Return the requested format
     */
    public function getFormat(): Format;

    /**
     * Return if the request is a preflight request
     */
    public function isPreflight(): bool;

    /**
     * Return if the request wants to write data
     */
    public function isWrite(): bool;

    /**
     * Return if the request wants to read data
     */
    public function isRead(): bool;

    /**
     * Return an instance with the given format
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message.
     *
     * @return static
     */
    public function withFormat(Format $format): RestRequestInterface;

    /**
     * Return an instance with the given Resource Type
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message.
     *
     * @return static
     */
    public function withResourceType(ResourceType $resourceType): RestRequestInterface;
}
