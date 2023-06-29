<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Http\ServerRequestProxyTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Specialized Request
 */
class Request implements ServerRequestInterface, RestRequestInterface
{
    use ServerRequestProxyTrait;

    /**
     * @var ServerRequestInterface
     */
    private $originalRequest;

    /**
     * Resource type - The first part of the request after mapping aliases
     *
     * @var ResourceType
     */
    private $resourceType;

    /**
     * @var UriInterface
     */
    private $internalUri;

    /**
     * @var Format
     */
    private $format;

    /**
     * @var
     */
    private $originalPath;

    /**
     * @var
     */
    private $sentData;

    /**
     * Constructor for a new request with the given Server Request, resource type and format
     *
     * @param ServerRequestInterface $originalRequest
     * @param UriInterface           $internalUri
     * @param string                 $originalPath
     * @param ResourceType           $resourceType
     * @param Format                 $format
     */
    public function __construct(
        ServerRequestInterface $originalRequest,
        UriInterface $internalUri,
        string $originalPath,
        ResourceType $resourceType,
        Format $format
    ) {
        $this->originalRequest = $originalRequest;
        $this->originalPath = $originalPath;
        $this->resourceType = $resourceType;
        $this->internalUri = $internalUri;
        $this->format = $format;
    }

    /**
     * Returns the original request
     *
     * @return ServerRequestInterface
     */
    public function getOriginalRequest(): ServerRequestInterface
    {
        return $this->originalRequest;
    }

    public function getPath(): string
    {
        return $this->internalUri->getPath();
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function withResourceType(ResourceType $resourceType): RestRequestInterface
    {
        $clone = clone $this;
        $clone->resourceType = $resourceType;

        return $clone;
    }

    public function getSentData()
    {
        if (!$this->sentData) {
            $this->sentData = $this->decodeSentData();
        }

        return $this->sentData;
    }

    public function getFormat(): Format
    {
        return $this->format;
    }

    public function isPreflight(): bool
    {
        return strtoupper($this->getMethod()) === 'OPTIONS';
    }

    public function isWrite(): bool
    {
        return !$this->isRead() && !$this->isPreflight();
    }

    public function isRead(): bool
    {
        return in_array(strtoupper($this->getMethod()), ['GET', 'HEAD']);
    }

    public function getRootObjectKey(): string
    {
        return $this->getOriginalResourceType();
    }

    public function withFormat(Format $format): RestRequestInterface
    {
        return new static(
            $this->originalRequest,
            $this->internalUri,
            $this->originalPath,
            $this->resourceType,
            new Format((string)$format)
        );
    }

    /**
     * Returns the request path before mapping aliases
     *
     * @return string
     */
    public function getOriginalResourceType(): string
    {
        return (string)strtok(strtok($this->originalPath, '?'), '/');
    }

    /**
     * @param ServerRequestInterface $request
     * @return $this
     */
    protected function setOriginalRequest(ServerRequestInterface $request): self
    {
        $this->originalRequest = $request;

        return $this;
    }

    /**
     * @return array|mixed|object|null
     */
    private function decodeSentData()
    {
        $contentTypes = $this->getHeader('content-type');
        $isFormEncoded = array_reduce(
            $contentTypes,
            function ($isFormEncoded, $contentType): bool {
                if ($isFormEncoded) {
                    return true;
                }

                return strpos($contentType, 'application/x-www-form-urlencoded') !== false
                    || strpos($contentType, 'multipart/form-data') !== false;
            },
            false
        );

        // Data was sent form encoded, so we expect it to already be properly parsed
        if ($isFormEncoded) {
            return $this->getParsedBody();
        }

        // We expect the content to be a JSON payload
        $body = (string)$this->getBody();
        if ('' === $body || 'null' === $body) {
            return null;
        }

        $decodedData = json_decode($body, true);
        if ($decodedData === null) {
            // Decoding failed -> fall back to the parsed body
            return $this->getParsedBody();
        } else {
            return $decodedData;
        }
    }
}
