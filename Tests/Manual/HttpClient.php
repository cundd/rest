<?php
declare(strict_types=1);
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Cundd\Rest\Tests\Manual;

use InvalidArgumentException;
use RuntimeException;
use stdClass;
use UnexpectedValueException;

class HttpClient
{
    private $verbose;
    private $baseUrl;
    private $statistics = [
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
     * @param bool   $verbose
     * @param string $baseUrl Provide a base URL for all requests (if used '/rest/' will not be appended to URLs)
     */
    public function __construct($verbose = false, $baseUrl = '')
    {
        if (!is_bool($verbose)) {
            throw new InvalidArgumentException('Expected argument "verbose" to be of type boolean');
        }
        if (!is_string($baseUrl) && !(is_object($baseUrl) && method_exists($baseUrl, '__toString'))) {
            throw new InvalidArgumentException('Expected argument "baseUrl" to be of type string');
        }
        $this->verbose = (bool)$verbose;
        $this->baseUrl = (string)$baseUrl;
    }

    /**
     * @param bool   $verbose
     * @param string $baseUrl
     * @return HttpClient
     */
    public static function client($verbose = false, $baseUrl = '')
    {
        return new self($verbose, $baseUrl);
    }

    /**
     * @param string            $path
     * @param string            $method
     * @param null|string|mixed $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param string[]          $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     * @return HttpResponse
     */
    public function requestJson($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
        $response = $this->request($path, $method, $body, $headers, $basicAuth);

        $response = $response->withParsedBody(json_decode($response->getBody(), true));
        if ($response->getParsedBody() === null) {
            $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
                . substr($response->getBody(), 0, (int)getenv('ERROR_BODY_LENGTH') ?: 300) . PHP_EOL
                . '------------------------------------' . PHP_EOL
                . $this->buildCurlCommand($path, $method, $body, $headers, $basicAuth);
            throw new UnexpectedValueException(json_last_error_msg() . ' for content: ' . $bodyPart);
        }

        return $response;
    }

    /**
     * @param string            $path
     * @param string            $method
     * @param null|string|mixed $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param string[]          $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     * @return HttpResponse
     */
    public function request($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
        $method = strtoupper($method);
        $url = $this->getUrlForPath($path);
        $curlClient = curl_init($url);

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => true,
            CURLOPT_VERBOSE        => $this->verbose,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $this->flattenRequestHeaders($headers),
        ];

        if ($basicAuth !== null) {
            if (is_string($basicAuth)) {
                $options[CURLOPT_USERPWD] = $basicAuth;
            } else {
                throw new InvalidArgumentException('Expected argument "basicAuth" to be of type string');
            }
        }

        if ($body !== null) {
            $body = $this->prepareBody($body, $headers);
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($curlClient, $options);

        $request = [
            'url'      => $url,
            'method'   => $method,
            'withBody' => null !== $body ? 'yes' : 'no',
        ];

        $this->debugCurl($path, $method, $body, $headers, $basicAuth);

        $this->statistics['numberOfRequestsTotal'] += 1;
        if (isset($this->statistics['numberOfRequestsPerMethod'][$method])) {
            $this->statistics['numberOfRequestsPerMethod'][$method] += 1;
        } else {
            $this->statistics['numberOfRequestsPerMethod'][$method] = 1;
        }

        return $this->send($curlClient, $request);
    }

    /**
     * Return an array with some basic statistics of this client instance
     *
     * @return string[]
     */
    public function getStatistics()
    {
        return $this->statistics;
    }

    /**
     * Set the environment variable REST_DEBUG_CURL to print the curl command
     *
     * @param string            $path
     * @param string            $method
     * @param null|string|mixed $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param string[]          $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     */
    private function debugCurl($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
        if (getenv('REST_DEBUG_CURL')) {
            echo PHP_EOL;
            echo $this->buildCurlCommand($path, $method, $body, $headers, $basicAuth);
            echo PHP_EOL;
        }
    }

    /**
     * @param string            $path
     * @param string            $method
     * @param null|string|mixed $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param string[]          $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     * @return string
     */
    private function buildCurlCommand($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
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
            $command[] = '--header ' . escapeshellarg("$key: $value");
        }

        // URL
        $command[] = escapeshellarg($url);

        return implode(' ', $command);
    }

    /**
     * @param string $headerString
     * @param int    $statusCode
     * @return array
     */
    private function parseResponseHeaders($headerString, &$statusCode)
    {
        if (!$headerString) {
            return [];
        }

        $headerLines = explode("\r\n", trim($headerString));
        $headers = [];

        foreach ($headerLines as $i => $line) {
            if ($i === 0) {
                [$httpCode, $rawStatusCode, $statusPhrase] = explode(' ', $line, 3);
                $statusCode = intval($rawStatusCode);
                $headers['status_code'] = [$statusCode];
                $headers['http_code'] = [$httpCode];
                $headers['status_phrase'] = [$statusPhrase];
            } else {
                [$key, $value] = explode(': ', $line);

                if (!isset($headers[$key])) {
                    $headers[$key] = [$value];
                } else {
                    $headers[$key][] = $value;
                }
            }
        }

        return $headers;
    }

    private function getBaseUrl()
    {
        if ($this->baseUrl) {
            return $this->baseUrl;
        }

        return (getenv('API_HOST') ?: 'http://localhost:8888') . '/rest/';
    }

    private function hasPrefix($prefix, $input)
    {
        return (substr($input, 0, strlen($prefix)) === $prefix);
    }

    /**
     * @param array $headers
     * @return array
     */
    private function flattenRequestHeaders(array $headers)
    {
        $flatHeaders = [];
        foreach ($headers as $key => $value) {
            $flatHeaders[] = "$key: $value";
        }

        return $flatHeaders;
    }

    /**
     * @param resource       $curlClient
     * @param array|stdClass $requestData
     * @return HttpResponse
     * @throws RuntimeException
     */
    private function send($curlClient, $requestData)
    {
        $response = curl_exec($curlClient);

        if ($response) {
            $headerSize = curl_getinfo($curlClient, CURLINFO_HEADER_SIZE);
            $responseHeaders = $this->parseResponseHeaders(substr($response, 0, $headerSize), $statusCode);
            $responseBody = substr($response, $headerSize);
        } else {
            $statusCode = null;
            $responseBody = null;
            $responseBody = null;
            $responseHeaders = null;
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
            (object)$requestData
        );
    }

    /**
     * @param string|mixed $body
     * @param string[]     $headers Reference to the headers array
     * @return string
     */
    protected function prepareBody($body, array &$headers)
    {
        if (!is_string($body)) {
            $body = json_encode($body);
        }

        if (!isset($headers['Content-Length'])) {
            $headers['Content-Length'] = strlen($body);
        }

        return $body;
    }

    /**
     * @param $path
     * @return string
     */
    private function getUrlForPath($path)
    {
        return $this->hasPrefix($this->getBaseUrl(), $path) ? $path : ($this->getBaseUrl() . ltrim($path, '/'));
    }
}
