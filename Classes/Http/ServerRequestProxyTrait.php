<?php

declare(strict_types=1);

namespace Cundd\Rest\Http;

use Cundd\Rest\Request;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

trait ServerRequestProxyTrait
{
    abstract public function getOriginalRequest(): ServerRequestInterface;

    abstract protected function setOriginalRequest(ServerRequestInterface $request): Request;

    protected function copy(): self
    {
        return clone $this;
    }

    public function getProtocolVersion(): string
    {
        return $this->getOriginalRequest()->getProtocolVersion();
    }

    public function getHeaders(): array
    {
        return $this->getOriginalRequest()->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->getOriginalRequest()->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->getOriginalRequest()->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->getOriginalRequest()->getHeaderLine($name);
    }

    public function getBody(): StreamInterface
    {
        return $this->getOriginalRequest()->getBody();
    }

    public function getRequestTarget(): string
    {
        return $this->getOriginalRequest()->getRequestTarget();
    }

    public function getMethod(): string
    {
        return $this->getOriginalRequest()->getMethod();
    }

    public function getUri(): UriInterface
    {
        return $this->getOriginalRequest()->getUri();
    }

    public function getServerParams(): array
    {
        return $this->getOriginalRequest()->getServerParams();
    }

    public function getCookieParams(): array
    {
        return $this->getOriginalRequest()->getCookieParams();
    }

    public function getQueryParams(): array
    {
        return $this->getOriginalRequest()->getQueryParams();
    }

    public function getUploadedFiles(): array
    {
        return $this->getOriginalRequest()->getUploadedFiles();
    }

    public function getParsedBody()
    {
        return $this->getOriginalRequest()->getParsedBody();
    }

    public function getAttributes(): array
    {
        return $this->getOriginalRequest()->getAttributes();
    }

    public function getAttribute(string $name, $default = null): mixed
    {
        return $this->getOriginalRequest()->getAttribute($name, $default);
    }

    public function withProtocolVersion(string $version): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withProtocolVersion($version));
    }

    public function withHeader(string $name, $value): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withHeader($name, $value));
    }

    public function withAddedHeader(string $name, $value): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withAddedHeader($name, $value));
    }

    public function withoutHeader(string $name): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withoutHeader($name));
    }

    public function withBody(StreamInterface $body): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withBody($body));
    }

    public function withRequestTarget($requestTarget): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withRequestTarget($requestTarget));
    }

    public function withMethod(string $method): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withMethod($method));
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withUri($uri, $preserveHost));
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withCookieParams($cookies));
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withQueryParams($query));
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withUploadedFiles($uploadedFiles));
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withParsedBody($data));
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withAttribute($name, $value));
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        return $this->copy()->setOriginalRequest($this->getOriginalRequest()->withoutAttribute($name));
    }
}
