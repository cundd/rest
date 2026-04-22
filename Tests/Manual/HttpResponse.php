<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual;

/**
 * PSR-7 inspired HTTP Response
 *
 * @see https://www.php-fig.org/psr/psr-7/
 *
 * @phpstan-type Headers array<non-empty-string, array<string>|string>
 * @phpstan-type RequestData object{method:non-empty-string,url:string}
 */
final class HttpResponse
{
    /**
     * @var Headers
     */
    private array $headers = [];

    /**
     * HTTP Response constructor
     *
     * @param Headers                                           $headers
     * @param string|object|array<non-empty-string, mixed>|null $parsedBody
     * @param RequestData                                       $requestData
     */
    public function __construct(
        private readonly ?int $statusCode,
        private readonly ?string $body,
        private readonly string|array|object|null $parsedBody,
        array $headers,
        private readonly object $requestData,
    ) {
        $this->headers = array_combine(
            array_map('strtoupper', array_keys($headers)),
            $headers
        );
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @return string|object|array<non-empty-string, mixed>|null
     */
    public function getParsedBody(): string|array|object|null
    {
        return $this->parsedBody;
    }

    /**
     * @param string|object|array<non-empty-string, mixed>|null $parsedBody
     */
    public function withParsedBody(string|array|object|null $parsedBody): self
    {
        return new self(
            statusCode: $this->statusCode,
            body: $this->body,
            parsedBody: $parsedBody,
            headers: $this->headers,
            requestData: $this->requestData
        );
    }

    /**
     * @return Headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param non-empty-string $name
     *
     * @return string[]
     */
    public function getHeader(string $name): array
    {
        $name = strtoupper($name);
        if (empty($this->headers[$name])) {
            return [];
        }

        $headerValue = $this->headers[$name];

        return is_array($headerValue) ? $headerValue : [$headerValue];
    }

    /**
     * @param non-empty-string $name
     */
    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @return RequestData
     */
    public function getRequestData(): object
    {
        return $this->requestData;
    }
}
