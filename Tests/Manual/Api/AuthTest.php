<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07.01.17
 * Time: 11:50
 */

namespace Cundd\Rest\Tests\Manual\Api;

class AuthTest extends AbstractApiCase
{
    /**
     * @test
     */
    public function getStatusTest()
    {
        $response = $this->request('auth/login');

        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertSame('{"status":"logged-out"}', $response->body, $this->getErrorDescription($response));
    }

    /**
     * @test
     */
    public function checkLoginJsonTest()
    {
        $response = $this->request('auth/login', 'POST');
        $this->assertSame('{"status":"logged-out"}', $response->body, $this->getErrorDescription($response));

        $response = $this->request(
            'auth/login',
            'POST',
            ['username' => $this->getApiUser(), 'apikey' => $this->getApiKey()],
            ['Content-Type' => 'application/json']
        );
        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertSame('{"status":"logged-in"}', $response->body, $this->getErrorDescription($response));
        $this->assertArrayHasKey('Set-Cookie', $response->headers, $this->getErrorDescription($response));
    }

    /**
     * @test
     */
    public function checkLoginUrlEncodedTest()
    {
        $response = $this->request('auth/login', 'POST');
        $this->assertSame('{"status":"logged-out"}', $response->body, $this->getErrorDescription($response));

        $response = $this->request(
            'auth/login',
            'POST',
            http_build_query(['username' => $this->getApiUser(), 'apikey' => $this->getApiKey()])
        );
        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertSame('{"status":"logged-in"}', $response->body, $this->getErrorDescription($response));
        $this->assertArrayHasKey('Set-Cookie', $response->headers, $this->getErrorDescription($response));
    }

    /**
     * @test
     */
    public function logoutWithPostTest()
    {
        list($sessionVariable, $sessionId) = $this->loginAndGetSession();

        $status = $this->request('auth/login', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame('{"status":"logged-in"}', $status->body, $this->getErrorDescription($status));

        $logout = $this->request('auth/logout', 'POST', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame(200, $logout->status, $this->getErrorDescription($logout));
        $this->assertSame('{"status":"logged-out"}', $logout->body, $this->getErrorDescription($logout));

        $status = $this->request('auth/login', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame(200, $status->status, $this->getErrorDescription($status));
        $this->assertSame('{"status":"logged-out"}', $status->body, $this->getErrorDescription($status));
    }

    /**
     * @test
     */
    public function logoutWithGetTest()
    {
        list($sessionVariable, $sessionId) = $this->loginAndGetSession();

        $status = $this->request('auth/login', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame('{"status":"logged-in"}', $status->body, $this->getErrorDescription($status));

        $logout = $this->request('auth/logout', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame(200, $logout->status, $this->getErrorDescription($logout));
        $this->assertSame('{"status":"logged-out"}', $logout->body, $this->getErrorDescription($logout));

        $status = $this->request('auth/login', 'GET', null, ['Cookie' => "$sessionVariable=$sessionId"]);
        $this->assertSame(200, $status->status, $this->getErrorDescription($status));
        $this->assertSame('{"status":"logged-out"}', $status->body, $this->getErrorDescription($status));
    }

    /**
     * @return string
     */
    private function getApiUser()
    {
        return getenv('API_USER') ?: 'daniel';
    }

    /**
     * @return string
     */
    private function getApiKey()
    {
        return getenv('API_KEY') ?: 'api-key';
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
        $this->assertSame(200, $response->status, $this->getErrorDescription($response));
        $this->assertSame('{"status":"logged-in"}', $response->body, $this->getErrorDescription($response));
        $this->assertArrayHasKey('Set-Cookie', $response->headers, $this->getErrorDescription($response));

        $cookie = $response->headers['Set-Cookie'];
        list($sessionCookie,) = explode(';', $cookie);

        return explode('=', $sessionCookie, 2);
    }
}

