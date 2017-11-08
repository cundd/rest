<?php

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
        $originalPath,
        ResourceType $resourceType,
        Format $format
    ) {
        $this->assertStringArgument($originalPath, 'originalPath');

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
    public function getOriginalRequest()
    {
        return $this->originalRequest;
    }

    /**
     * Returns the request path (eventually aliases have been mapped)
     *
     * @return string
     */
    public function getPath()
    {
        return $this->internalUri->getPath();
    }

    /**
     * Returns the requested resource type
     *
     * The resource type is the first part of the request path, after mapping aliases
     *
     * @return ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Returns the request path before mapping aliases
     *
     * @return string
     */
    public function getOriginalResourceType()
    {
        return (string)strtok(strtok($this->originalPath, '?'), '/');
    }

    /**
     * Returns the sent data
     *
     * @return mixed
     */
    public function getSentData()
    {
        if ($this->sentData) {
            return $this->sentData;
        }
        $contentTypes = $this->getHeader('content-type');
        $isFormEncoded = array_reduce(
            $contentTypes,
            function ($isFormEncoded, $contentType) {
                if ($isFormEncoded) {
                    return true;
                }

                return strpos($contentType, 'application/x-www-form-urlencoded') !== false
                    || strpos($contentType, 'multipart/form-data') !== false;
            },
            false
        );
        if ($isFormEncoded) {
            $this->sentData = $this->getParsedBody();
        } else {
            $this->sentData = json_decode((string)$this->getBody(), true);
        }

        return $this->sentData;
    }

    /**
     * Returns the requested format
     *
     * @return Format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns if the request is a preflight request
     *
     * @return bool
     */
    public function isPreflight()
    {
        return strtoupper($this->getMethod()) === 'OPTIONS';
    }

    /**
     * Returns if the request wants to write data
     *
     * @return bool
     */
    public function isWrite()
    {
        return !$this->isRead() && !$this->isPreflight();
    }

    /**
     * Returns if the request wants to read data
     *
     * @return bool
     */
    public function isRead()
    {
        return in_array(strtoupper($this->getMethod()), ['GET', 'HEAD']);
    }

    /**
     * Returns the key to use for the root object if addRootObjectForCollection
     * is enabled
     *
     * @return string
     */
    public function getRootObjectKey()
    {
        return $this->getOriginalResourceType();
    }

    /**
     * @param $format
     * @return static
     */
    public function withFormat($format)
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
     * @param ServerRequestInterface $request
     * @return $this
     */
    protected function setOriginalRequest(ServerRequestInterface $request)
    {
        $this->originalRequest = $request;

        return $this;
    }

    /**
     * @param string|mixed $input
     * @param string       $argumentName
     */
    private function assertStringArgument($input, $argumentName)
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument "%s" passed must be a string, %s given',
                    $argumentName,
                    gettype($input)
                )
            );
        }
    }
}
