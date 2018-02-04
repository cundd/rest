<?php


namespace Cundd\Rest\Documentation\Handler;


use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Http\Uri;

class DummyRequest implements RestRequestInterface
{
    /**
     * @var ResourceType
     */
    private $resourceType;

    public function __construct(ResourceType $resourceType)
    {
        $this->resourceType = $resourceType;
    }

    public function getOriginalRequest()
    {
        return $this;
    }

    public function getPath()
    {
        return (string)$this->resourceType;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function getSentData()
    {
        return null;
    }

    public function getFormat()
    {
        return Format::defaultFormat();
    }

    public function isPreflight()
    {
        return false;
    }

    public function isWrite()
    {
        return false;
    }

    public function isRead()
    {
        return false;
    }

    public function getRootObjectKey()
    {
        return '';
    }

    public function withFormat($format)
    {
        return clone $this;
    }

    public function getProtocolVersion()
    {
        return '1.1';
    }

    public function withProtocolVersion($version)
    {
        return clone $this;
    }

    public function getHeaders()
    {
        return [[]];
    }

    public function hasHeader($name)
    {
        return false;
    }

    public function getHeader($name)
    {
        return [];
    }

    public function getHeaderLine($name)
    {
        return '';
    }

    public function withHeader($name, $value)
    {
        return clone $this;
    }

    public function withAddedHeader($name, $value)
    {
        return clone $this;
    }

    public function withoutHeader($name)
    {
        return clone $this;
    }

    public function getBody()
    {
        return new Stream('php://temp');
    }

    public function withBody(StreamInterface $body)
    {
        return clone $this;
    }

    public function getRequestTarget()
    {
        return '';
    }

    public function withRequestTarget($requestTarget)
    {
        return clone $this;
    }

    public function getMethod()
    {
        return 'GET';
    }

    public function withMethod($method)
    {
        return clone $this;
    }

    public function getUri()
    {
        return new Uri((string)$this->resourceType);
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return clone $this;
    }

    public function getServerParams()
    {
        return [];
    }

    public function getCookieParams()
    {
        return [];
    }

    public function withCookieParams(array $cookies)
    {
        return clone $this;
    }

    public function getQueryParams()
    {
        return [];
    }

    public function withQueryParams(array $query)
    {
        return clone $this;
    }

    public function getUploadedFiles()
    {
        return [];
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        return clone $this;
    }

    public function getParsedBody()
    {
        return null;
    }

    public function withParsedBody($data)
    {
        return clone $this;
    }

    public function getAttributes()
    {
        return [];
    }

    public function getAttribute($name, $default = null)
    {
        return null;
    }

    public function withAttribute($name, $value)
    {
        return clone $this;
    }

    public function withoutAttribute($name)
    {
        return clone $this;
    }
}
