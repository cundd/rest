<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual\Api;

class AuthTest extends AbstractApiCase
{
    /**
     * @test
     */
    public function getStatusTest()
    {
        $response = $this->request('auth/login');

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertSame('{"status":"logged-out"}', $response->getBody(), $this->getErrorDescription($response));
    }

    /**
     * @test
     */
    public function checkLoginJsonTest()
    {
        $response = $this->request('auth/login', 'POST');
        $this->assertSame('{"status":"logged-out"}', $response->getBody(), $this->getErrorDescription($response));

        $response = $this->request(
            'auth/login',
            'POST',
            ['username' => $this->getApiUser(), 'apikey' => $this->getApiKey()],
            ['Content-Type' => 'application/json']
        );
        $errorDescription = $this->getErrorDescription($response);
        $this->assertSame(200, $response->getStatusCode(), $errorDescription);
        $this->assertSame('{"status":"logged-in"}', $response->getBody(), $errorDescription);
        $this->assertIsArray($response->getHeader('Set-Cookie'), $errorDescription);
        $this->assertNotEmpty($response->getHeader('Set-Cookie'), $errorDescription);
    }

    /**
     * @test
     */
    public function checkLoginUrlEncodedTest()
    {
        $response = $this->request('auth/login', 'POST');
        $this->assertSame('{"status":"logged-out"}', $response->getBody(), $this->getErrorDescription($response));

        $response = $this->request(
            'auth/login',
            'POST',
            http_build_query(['username' => $this->getApiUser(), 'apikey' => $this->getApiKey()])
        );
        $errorDescription = $this->getErrorDescription($response);
        $this->assertSame(200, $response->getStatusCode(), $errorDescription);
        $this->assertSame('{"status":"logged-in"}', $response->getBody(), $errorDescription);
        $this->assertIsArray($response->getHeader('Set-Cookie'), $errorDescription);
        $this->assertNotEmpty($response->getHeader('Set-Cookie'), $errorDescription);
    }

    /**
     * @test
     */
    public function logoutWithPostTest()
    {
        [$sessionVariable, $sessionId] = $this->loginAndGetSession();

        $status = $this->request('auth/login', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame('{"status":"logged-in"}', $status->getBody(), $this->getErrorDescription($status));

        $logout = $this->request('auth/logout', 'POST', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $logoutErrorDescription = $this->getErrorDescription($logout);
        $this->assertSame(200, $logout->getStatusCode(), $logoutErrorDescription);
        $this->assertSame('{"status":"logged-out"}', $logout->getBody(), $logoutErrorDescription);

        $status = $this->request('auth/login', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame(200, $status->getStatusCode(), $this->getErrorDescription($status));
        $this->assertSame('{"status":"logged-out"}', $status->getBody(), $this->getErrorDescription($status));
    }

    /**
     * @test
     */
    public function logoutWithGetTest()
    {
        [$sessionVariable, $sessionId] = $this->loginAndGetSession();

        $status = $this->request('auth/login', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame('{"status":"logged-in"}', $status->getBody(), $this->getErrorDescription($status));

        $logout = $this->request('auth/logout', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame(200, $logout->getStatusCode(), $this->getErrorDescription($logout));
        $this->assertSame('{"status":"logged-out"}', $logout->getBody(), $this->getErrorDescription($logout));

        $status = $this->request('auth/login', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame(200, $status->getStatusCode(), $this->getErrorDescription($status));
        $this->assertSame('{"status":"logged-out"}', $status->getBody(), $this->getErrorDescription($status));
    }

    /**
     * @return array
     */
    private function loginAndGetSession()
    {
        $response = $this->request(
            'auth/login',
            'POST',
            http_build_query(['username' => $this->getApiUser(), 'apikey' => $this->getApiKey()])
        );
        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertSame('{"status":"logged-in"}', $response->getBody(), $this->getErrorDescription($response));
        $this->assertIsArray($response->getHeader('Set-Cookie'), $this->getErrorDescription($response));
        $this->assertNotEmpty($response->getHeader('Set-Cookie'), $this->getErrorDescription($response));

        $cookie = $response->getHeaderLine('Set-Cookie');
        [$sessionCookie,] = explode(';', $cookie);

        return explode('=', $sessionCookie, 2);
    }
}

