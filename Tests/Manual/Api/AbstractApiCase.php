<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual\Api;

use Cundd\Rest\Tests\Manual\HttpClient;
use Cundd\Rest\Tests\Manual\HttpErrorDescriptionTrait;
use Cundd\Rest\Tests\Manual\HttpResponse;

/**
 * @phpstan-import-type Headers from HttpClient
 */
abstract class AbstractApiCase extends \PHPUnit\Framework\TestCase
{
    use HttpErrorDescriptionTrait;

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
        return HttpClient::client()->request($path, $method, $body, $headers, $basicAuth);
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
        return HttpClient::client()->requestJson($path, $method, $body, $headers, $basicAuth);
    }

    /**
     * @return list<array<string>>
     */
    public static function suffixDataProvider(): array
    {
        return [
            [''],
            ['/'],
            ['.json'],
        ];
    }

    protected function getApiUser(): string
    {
        return getenv('API_USER') ?: 'daniel';
    }

    protected function getApiKey(): string
    {
        return getenv('API_KEY') ?: 'api-key';
    }
}
