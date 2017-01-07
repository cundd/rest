<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07.01.17
 * Time: 11:46
 */

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

    public function requestJson($path, $method = 'GET', $body = null, array $headers = [])
    {
        $response = $this->request($path, $method, $body, $headers);
        $response->content = json_decode($response->body, true);
        if ($response->content === null) {
            $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
                . substr($response->body, 0, 200) . PHP_EOL
                . '------------------------------------';
            throw new \UnexpectedValueException(json_last_error_msg() . ' for content: ' . $bodyPart);
        }

        return $response;
    }

    public function request($path, $method = 'GET', $body = null, array $headers = [])
    {
        $method = strtoupper($method);
        $url = $this->hasPrefix($this->getBaseUrl(), $path) ? $path : ($this->getBaseUrl() . ltrim($path, '/'));
        $curlClient = curl_init($url);

//        echo 'Request ' . $url . PHP_EOL;
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

        if ($body !== null) {
            if (!is_string($body)) {
                $body = json_encode($body);
            }

            $options[CURLOPT_POSTFIELDS] = $body;
            if (!isset($headers['Content-Length'])) {
                $headers['Content-Length'] = strlen($body);
            }
        }

        curl_setopt_array($curlClient, $options);

        $request = [
            'url'      => $url,
            'method'   => $method,
            'withBody' => null !== $body ? 'yes' : 'no',
        ];

        return $this->send($curlClient, $request);
    }

    private function parseResponseHeaders($headerString)
    {
        $headerLines = explode("\r\n", trim($headerString));
        $headers = array();

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
}
