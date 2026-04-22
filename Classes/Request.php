<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Http\ServerRequestProxyTrait;
use Cundd\Rest\Request\Format;
use Cundd\Rest\Request\RequestType;
use Cundd\Rest\Request\ResourceType;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Specialized Request
 */
final class Request implements ServerRequestInterface, RestRequestInterface
{
    use ServerRequestProxyTrait;

    private mixed $sentData;

    /**
     * Constructor for a new request with the given Server Request, resource type and format
     *
     * @param ResourceType $resourceType Resource type - The first part of the request after mapping aliases
     */
    public function __construct(
        private ServerRequestInterface $originalRequest,
        private UriInterface $internalUri,
        private string $originalPath,
        private ResourceType $resourceType,
        private Format $format,
    ) {
    }

    /**
     * Returns the original request
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

    public function getSentData(): mixed
    {
        if (!isset($this->sentData)) {
            $this->sentData = $this->decodeSentData();
        }

        return $this->sentData;
    }

    public function getFormat(): Format
    {
        return $this->format;
    }

    public function getRequestType(): RequestType
    {
        return RequestType::fromRequest($this);
    }

    public function isPreflight(): bool
    {
        return RequestType::Preflight === $this->getRequestType();
    }

    public function isWrite(): bool
    {
        return RequestType::Write === $this->getRequestType();
    }

    public function isRead(): bool
    {
        return RequestType::Read === $this->getRequestType();
    }

    public function withFormat(Format $format): RestRequestInterface
    {
        return new static(
            $this->originalRequest,
            $this->internalUri,
            $this->originalPath,
            $this->resourceType,
            new Format((string) $format)
        );
    }

    /**
     * Returns the request path before mapping aliases
     */
    public function getOriginalResourceType(): string
    {
        return (string) strtok((string) strtok($this->originalPath, '?'), '/');
    }

    /**
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
    private function decodeSentData(): mixed
    {
        $contentTypes = $this->getHeader('content-type');
        $isFormEncoded = array_reduce(
            $contentTypes,
            function ($isFormEncoded, $contentType): bool {
                if ($isFormEncoded) {
                    return true;
                }

                return false !== strpos($contentType, 'application/x-www-form-urlencoded')
                    || false !== strpos($contentType, 'multipart/form-data');
            },
            false
        );

        // Data was sent form encoded, so we expect it to already be properly parsed
        if ($isFormEncoded) {
            return $this->getParsedBody();
        }

        // We expect the content to be a JSON payload
        $body = (string) $this->getBody();
        if ('' === $body || 'null' === $body) {
            return null;
        }

        $decodedData = json_decode($body, true);
        if (null === $decodedData) {
            // Decoding failed -> fall back to the parsed body
            return $this->getParsedBody();
        } else {
            return $decodedData;
        }
    }
}
