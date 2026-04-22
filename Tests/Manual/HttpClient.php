<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual;

use CurlHandle;
use RuntimeException;
use Stringable;
use UnexpectedValueException;

/**
 * @phpstan-type Headers array<non-empty-string, array<string>|string>
 * @phpstan-type Statistics array{numberOfRequestsTotal:int,numberOfRequestsPerMethod:array<string,int>}
 *
 * @phpstan-import-type RequestData from HttpResponse
 */
final class HttpClient
{
    private readonly string $baseUrl;

    /**
     * @var Statistics
     */
    private array $statistics = [
        'numberOfRequestsTotal'     => 0,
        'numberOfRequestsPerMethod' => [
            'GET'     => 0,
            'POST'    => 0,
            'DELETE'  => 0,
            'PUT'     => 0,
            'PATCH'   => 0,
            'OPTIONS' => 0,
        ],
    ];

    /**
     * HTTP Client constructor
     *
     * @param string $baseUrl Provide a base URL for all requests (if used '/rest/' will not be appended to URLs)
     */
    public function __construct(
        private readonly bool $verbose = false,
        string|Stringable $baseUrl = '',
    ) {
        $this->baseUrl = (string) $baseUrl;
    }

    public static function client(
        bool $verbose = false,
        string|Stringable $baseUrl = '',
    ): self {
        return new self($verbose, $baseUrl);
    }

    /**
     * @param non-empty-string  $method
     * @param string|mixed|null $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param Headers           $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     */
    public function requestJson(
        string $path,
        string $method = 'GET',
        mixed $body = null,
        array $headers = [],
        $basicAuth = null,
    ): HttpResponse {
        $response = $this->request($path, $method, $body, $headers, $basicAuth);

        $response = $response->withParsedBody(json_decode(
            (string) $response->getBody(),
            true,
            JSON_THROW_ON_ERROR
        ));
        if (null === $response->getParsedBody()) {
            $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
                . substr(
                    (string) $response->getBody(),
                    0,
                    (int) getenv('ERROR_BODY_LENGTH') ?: 300
                ) . PHP_EOL
                . '------------------------------------' . PHP_EOL
                . $this->buildCurlCommand($path, $method, $body, $headers, $basicAuth);
            throw new UnexpectedValueException(json_last_error_msg() . ' for content: ' . $bodyPart);
        }

        return $response;
    }

    /**
     * @param non-empty-string  $method
     * @param string|mixed|null $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param Headers           $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     */
    public function request(
        string $path,
        string $method = 'GET',
        mixed $body = null,
        array $headers = [],
        ?string $basicAuth = null,
    ): HttpResponse {
        $method = strtoupper($method);
        $url = $this->getUrlForPath($path);
        /** @var CurlHandle|false $curlClient */
        $curlClient = curl_init($url);
        if (false === $curlClient) {
            throw new UnexpectedValueException('Could not init curl');
        }

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => true,
            CURLOPT_VERBOSE        => $this->verbose,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $this->flattenRequestHeaders($headers),
        ];

        if (null !== $basicAuth) {
            $options[CURLOPT_USERPWD] = $basicAuth;
        }

        if (null !== $body) {
            $body = $this->prepareBody($body, $headers);
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($curlClient, $options); // @phpstan-ignore argument.type

        $request = [
            'url'      => $url,
            'method'   => $method,
            'withBody' => null !== $body ? 'yes' : 'no',
        ];

        $this->debugCurl($path, $method, $body, $headers, $basicAuth);

        ++$this->statistics['numberOfRequestsTotal'];
        if (isset($this->statistics['numberOfRequestsPerMethod'][$method])) {
            ++$this->statistics['numberOfRequestsPerMethod'][$method];
        } else {
            $this->statistics['numberOfRequestsPerMethod'][$method] = 1;
        }

        return $this->send($curlClient, (object) $request);
    }

    /**
     * Return an array with some basic statistics of this client instance
     *
     * @return Statistics
     */
    public function getStatistics()
    {
        return $this->statistics;
    }

    /**
     * Set the environment variable REST_DEBUG_CURL to print the curl command
     *
     * @param string|mixed|null $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param Headers           $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     */
    private function debugCurl(
        string $path,
        string $method = 'GET',
        mixed $body = null,
        array $headers = [],
        ?string $basicAuth = null,
    ): void {
        if (getenv('REST_DEBUG_CURL')) {
            echo PHP_EOL;
            echo $this->buildCurlCommand($path, $method, $body, $headers, $basicAuth);
            echo PHP_EOL;
        }
    }

    /**
     * @param string|mixed|null $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param Headers           $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     *
     * @return string
     */
    private function buildCurlCommand(
        string $path,
        string $method = 'GET',
        mixed $body = null,
        array $headers = [],
        ?string $basicAuth = null,
    ) {
        $url = $this->getUrlForPath($path);
        $command = ['curl'];

        // Method
        $command[] = '-X';
        $command[] = escapeshellarg($method);

        // Basic auth
        if (null !== $basicAuth) {
            $command[] = '-u';
            $command[] = escapeshellarg($basicAuth);
        }

        // Body
        if (null !== $body) {
            $body = $this->prepareBody($body, $headers);
            $command[] = '-d';
            $command[] = '\'' . addslashes($body) . '\'';
        }

        // Headers
        foreach ($headers as $key => $value) {
            foreach ((array) ($value) as $item) {
                $command[] = '--header ' . escapeshellarg("$key: $item");
            }
        }

        // URL
        $command[] = escapeshellarg($url);

        return implode(' ', $command);
    }

    /**
     * @return Headers
     */
    private function parseResponseHeaders(
        string $headerString,
        int &$statusCode,
    ): array {
        if (!$headerString) {
            return [];
        }

        $headerLines = explode("\r\n", trim($headerString));
        $headers = [];

        foreach ($headerLines as $i => $line) {
            if (0 === $i) {
                [$httpCode, $rawStatusCode, $statusPhrase] = explode(' ', $line, 3);
                $statusCode = intval($rawStatusCode);
                $headers['status_code'] = [(string) $statusCode];
                $headers['http_code'] = [$httpCode];
                $headers['status_phrase'] = [$statusPhrase];
            } else {
                [$key, $value] = array_map('trim', explode(':', $line));

                assert('' !== $key);
                if (!isset($headers[$key])) {
                    $headers[$key] = [$value];
                } else {
                    $headers[$key][] = $value;
                }
            }
        }

        return $headers;
    }

    private function getBaseUrl(): string
    {
        if ($this->baseUrl) {
            return $this->baseUrl;
        }

        return (getenv('API_HOST') ?: 'http://localhost:8888') . '/rest/';
    }

    /**
     * @param Headers $headers
     *
     * @return string[]
     */
    private function flattenRequestHeaders(array $headers): array
    {
        $flatHeaders = [];
        foreach ($headers as $key => $value) {
            foreach ((array) ($value) as $item) {
                $flatHeaders[] = "$key: $item";
            }
        }

        return $flatHeaders;
    }

    /**
     * @param CurlHandle  $curlClient
     * @param RequestData $requestData
     *
     * @throws RuntimeException
     */
    private function send($curlClient, object $requestData): HttpResponse
    {
        $response = curl_exec($curlClient);

        if ($response) {
            $statusCode = 0;
            $headerSize = curl_getinfo($curlClient, CURLINFO_HEADER_SIZE);
            $responseHeaders = $this->parseResponseHeaders(
                substr((string) $response, 0, $headerSize),
                $statusCode
            );
            $responseBody = substr((string) $response, $headerSize);
        } else {
            $statusCode = null;
            $responseBody = null;
            $responseBody = null;
            $responseHeaders = [];
        }

        $error = curl_error($curlClient);

        curl_close($curlClient);

        if ($error) {
            throw new RuntimeException($error);
        }

        return new HttpResponse(
            $statusCode,
            $responseBody,
            $responseBody,
            $responseHeaders,
            (object) $requestData
        );
    }

    /**
     * @param string|mixed $body
     * @param Headers      $headers Reference to the headers array
     */
    private function prepareBody(mixed $body, array &$headers): string
    {
        if (!is_string($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        if (!isset($headers['Content-Length'])) {
            $headers['Content-Length'] = (string) strlen($body);
        }

        return (string) $body;
    }

    private function getUrlForPath(string $path): string
    {
        return str_starts_with($path, $this->getBaseUrl())
            ? $path
            : ($this->getBaseUrl() . ltrim($path, '/'));
    }
}
