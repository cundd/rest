<?php

declare(strict_types=1);

namespace Cundd\Rest\Documentation\Handler;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request\Format;
use Cundd\Rest\Request\ResourceType;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Http\Uri;

class DummyRequest implements RestRequestInterface
{
    public function __construct(private readonly ResourceType $resourceType)
    {
    }

    public function getOriginalRequest(): ServerRequestInterface
    {
        return $this;
    }

    public function getPath(): string
    {
        return (string) $this->resourceType;
    }

    public function getResourceType(): ResourceType
    {
        return $this->resourceType;
    }

    public function withResourceType(ResourceType $resourceType): RestRequestInterface
    {
        return clone $this;
    }

    public function getSentData(): mixed
    {
        return null;
    }

    public function getFormat(): Format
    {
        return Format::defaultFormat();
    }

    public function isPreflight(): bool
    {
        return false;
    }

    public function isWrite(): bool
    {
        return false;
    }

    public function isRead(): bool
    {
        return false;
    }

    public function getRootObjectKey(): string
    {
        return '';
    }

    public function withFormat(Format $format): RestRequestInterface
    {
        return clone $this;
    }

    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    public function withProtocolVersion(string $version): ServerRequestInterface
    {
        return clone $this;
    }

    public function getHeaders(): array
    {
        return [[]];
    }

    public function hasHeader(string $name): bool
    {
        return false;
    }

    public function getHeader(string $name): array
    {
        return [];
    }

    public function getHeaderLine(string $name): string
    {
        return '';
    }

    public function withHeader(string $name, $value): ServerRequestInterface
    {
        return clone $this;
    }

    public function withAddedHeader(string $name, $value): ServerRequestInterface
    {
        return clone $this;
    }

    public function withoutHeader(string $name): ServerRequestInterface
    {
        return clone $this;
    }

    public function getBody(): StreamInterface
    {
        return new Stream('php://temp');
    }

    public function withBody(StreamInterface $body): ServerRequestInterface
    {
        return clone $this;
    }

    public function getRequestTarget(): string
    {
        return '';
    }

    public function withRequestTarget(string $requestTarget): ServerRequestInterface
    {
        return clone $this;
    }

    public function getMethod(): string
    {
        return 'GET';
    }

    public function withMethod(string $method): ServerRequestInterface
    {
        return clone $this;
    }

    public function getUri(): UriInterface
    {
        return new Uri((string) $this->resourceType);
    }

    public function withUri(
        UriInterface $uri,
        bool $preserveHost = false,
    ): ServerRequestInterface {
        return clone $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getServerParams(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getCookieParams(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $cookies
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return clone $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQueryParams(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $query
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        return clone $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getUploadedFiles(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $uploadedFiles
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return clone $this;
    }

    /**
     * @return array<string, mixed>|object|null
     */
    public function getParsedBody()
    {
        return null;
    }

    /**
     * @param array<string, mixed>|object|null $data
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        return clone $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return [];
    }

    public function getAttribute(string $name, $default = null)
    {
        return null;
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        return clone $this;
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        return clone $this;
    }
}
