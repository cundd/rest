<?php

namespace Cundd\Rest\Tests\Manual;

/**
 * PSR-7 inspired HTTP Response
 *
 * @link https://www.php-fig.org/psr/psr-7/
 */
class HttpResponse
{
    private $body = null;
    private $parsedBody = null;
    private $headers = [];
    private $statusCode = null;
    private $requestData = null;

    /**
     * HTTP Response constructor
     *
     * @param int                      $status
     * @param string                   $body
     * @param string|null|array|object $parsedBody
     * @param string[][]               $headers
     * @param object                   $requestData
     */
    public function __construct($status, $body, $parsedBody, array $headers, $requestData)
    {
        $this->body = $body;
        $this->parsedBody = $parsedBody;
        $this->headers = array_combine(array_map('strtoupper', array_keys($headers)), $headers);
        $this->statusCode = $status;
        $this->requestData = $requestData;
    }

    /**
     * @return null|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string|null|array|object
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @param string|null|array|object $content
     * @return HttpResponse
     */
    public function withContent($content)
    {
        $clone = clone $this;
        $clone->parsedBody = $content;

        return $clone;
    }

    /**
     * @return \string[][]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @return string[]
     */
    public function getHeader($name)
    {
        $name = strtoupper($name);

        return isset($this->headers[$name]) ? $this->headers[$name] : [];
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return null|object
     */
    public function getRequestData()
    {
        return $this->requestData;
    }
}
