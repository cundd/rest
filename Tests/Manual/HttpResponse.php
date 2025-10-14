<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual;

/**
 * PSR-7 inspired HTTP Response
 *
 * @see https://www.php-fig.org/psr/psr-7/
 */
class HttpResponse
{
    private $body;
    private $parsedBody;
    private $headers = [];
    private $statusCode;
    private $requestData;

    /**
     * HTTP Response constructor
     *
     * @param int                      $status
     * @param string                   $body
     * @param string|array|object|null $parsedBody
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
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string|array|object|null
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @param string|array|object|null $parsedBody
     *
     * @return HttpResponse
     */
    public function withParsedBody($parsedBody)
    {
        $clone = clone $this;
        $clone->parsedBody = $parsedBody;

        return $clone;
    }

    /**
     * @return string[][]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name)
    {
        $name = strtoupper($name);

        return isset($this->headers[$name]) ? $this->headers[$name] : [];
    }

    /**
     * @param string $name
     *
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
     * @return object|null
     */
    public function getRequestData()
    {
        return $this->requestData;
    }
}
