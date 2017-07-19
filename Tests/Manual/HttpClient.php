<?php

namespace Cundd\Rest\Tests\Manual;

class HttpClient
{
    private $verbose;

    /**
     * HttpClient constructor.
     *
     * @param bool $verbose
     */
    public function __construct($verbose = false)
    {
        $this->verbose = (bool)$verbose;
    }

    /**
     * @param bool $verbose
     * @return HttpClient
     */
    public static function client($verbose = false)
    {
        return new self($verbose);
    }

    /**
     * @param string            $path
     * @param string            $method
     * @param null|string|mixed $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param string[]          $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     * @return object
     */
    public function requestJson($path, $method = 'GET', $body = null, array $headers = [], $basicAuth = null)
    {
        $response = $this->request($path, $method, $body, $headers, $basicAuth);
        $response->content = json_decode($response->body, true);
        if ($response->content === null) {
            $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
                . substr($response->body, 0, 200) . PHP_EOL
                . '------------------------------------' . PHP_EOL
                . $this->buildCurlCommand($path, $method, $body, $headers, $basicAuth);
            throw new \UnexpectedValueException(json_last_error_msg() . ' for content: ' . $bodyPart);
        }

        return $response;
    }

    /**
     * @param string            $path
     * @param string            $method
     * @param null|string|mixed $body      Will be ignored if NULL, otherwise will be JSON encoded if it is not a string
     * @param string[]          $headers   A dictionary of headers
     * @param string            $basicAuth String in the format "user:password"
     * @return object
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

        if ($basicAuth) {
            $options[CURLOPT_USERPWD] = $basicAuth;
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

        return $this->send($curlClient, $request);
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
            $command[] = escapeshellarg($body);
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
     * @return array
     */
    private function parseResponseHeaders($headerString)
    {
        if (!$headerString) {
            return [];
        }

        $headerLines = explode("\r\n", trim($headerString));
        $headers = [];

        foreach ($headerLines as $i => $line) {
            if ($i === 0) {
                list($httpCode, $statusCode, $statusPhrase) = explode(' ', $line, 3);
                $headers['http_code'] = $httpCode;
                $headers['status_code'] = intval($statusCode);
                $headers['status_phrase'] = $statusPhrase;
            } else {
                list($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    private function getBaseUrl()
    {
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
     * @param                 $curlClient
     * @param array|\stdClass $requestData
     * @return object
     * @throws \Exception
     */
    private function send($curlClient, $requestData)
    {
        $response = curl_exec($curlClient);

        $headerSize = curl_getinfo($curlClient, CURLINFO_HEADER_SIZE);
        $responseHeaders = $this->parseResponseHeaders(substr($response, 0, $headerSize));
        $responseBody = substr($response, $headerSize);

        $error = curl_error($curlClient);

        curl_close($curlClient);

        if ($error) {
            throw  new \Exception($error);
        }

        return (object)[
            'body'        => $responseBody,
            'content'     => $responseBody,
            'headers'     => $responseHeaders,
            'status'      => $responseHeaders['status_code'],
            'requestData' => (object)$requestData,
        ];
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
