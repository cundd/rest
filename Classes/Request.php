<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

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
        $data = $this->getParsedBody();

        /*
         * If no form url-encoded body is sent check if a JSON
         * payload is sent with the singularized root object key as
         * the payload's root object key
         */
        if (!$data) {
            return json_decode((string)$this->getBody(), true);
        }

        return $data;
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
     * Returns if the request wants to write data
     *
     * @return bool
     */
    public function isWrite()
    {
        return !$this->isRead();
    }

    /**
     * Returns if the request wants to read data
     *
     * @return bool
     */
    public function isRead()
    {
        return in_array(strtoupper($this->getMethod()), array('GET', 'HEAD'));
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
