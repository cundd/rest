<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit\Core;

use Cundd\Rest\Tests\Unit\AbstractRequestBasedCase;

class RequestTest extends AbstractRequestBasedCase
{
    /**
     * @test
     */
    public function getSentDataFormUrlEncodedTest()
    {
        $testData = [
            'myData' => [
                'name' => 'Blur',
                'time' => time(),
            ],
        ];
        $_POST['myData'] = $testData['myData'];
        $request = $this->buildTestRequest(
            'MyAliasedModel' . time(),
            null,
            [],
            ['Content-Type' => 'application/x-www-form-urlencoded']
        );
        $this->assertSame($testData, $request->getSentData());
    }

    /**
     * @test
     */
    public function getSentDataMultipartFormDataTest()
    {
        $testData = [
            'myData' => [
                'name' => 'Blur',
                'time' => time(),
            ],
        ];
        $_POST['myData'] = $testData['myData'];
        $request = $this->buildTestRequest(
            'MyAliasedModel' . time(),
            null,
            [],
            ['Content-Type' => 'multipart/form-data']
        );
        $this->assertSame($testData, $request->getSentData());
    }

    /**
     * @test
     */
    public function getSentDataFromRawBodyTest()
    {
        $testData = [
            'myData' => [
                'name' => 'Test Name',
                'time' => time(),
            ],
        ];
        $request = $this->buildTestRequest('MyAliasedModel' . time(), null, [], [], json_encode($testData));
        $this->assertSame($testData, $request->getSentData());
    }

    /**
     * @test
     */
    public function isPreflightTest()
    {
        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'HEAD')->isPreflight(),
            'Failed isPreflight() for HEAD'
        );
        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'GET')->isPreflight(),
            'Failed isPreflight() for GET'
        );

        $this->assertTrue(
            $this->buildTestRequest('MyAliasedModel', 'OPTIONS')->isPreflight(),
            'Failed isPreflight() for OPTIONS'
        );

        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'POST')->isPreflight(),
            'Failed isPreflight() for POST'
        );
        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'PUT')->isPreflight(),
            'Failed isPreflight() for PUT'
        );
        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'PATCH')->isPreflight(),
            'Failed isPreflight() for PATCH'
        );
        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'DELETE')->isPreflight(),
            'Failed isPreflight() for DELETE'
        );
        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'TRACE')->isPreflight(),
            'Failed isPreflight() for TRACE'
        );
    }

    /**
     * @test
     */
    public function isWriteTest()
    {
        $this->assertFalse($this->buildTestRequest('MyAliasedModel', 'HEAD')->isWrite(), 'Failed isWrite() for HEAD');
        $this->assertFalse($this->buildTestRequest('MyAliasedModel', 'GET')->isWrite(), 'Failed isWrite() for GET');

        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'OPTIONS')->isWrite(),
            'Failed isWrite() for OPTIONS'
        );

        $this->assertTrue($this->buildTestRequest('MyAliasedModel', 'POST')->isWrite(), 'Failed isWrite() for POST');
        $this->assertTrue($this->buildTestRequest('MyAliasedModel', 'PUT')->isWrite(), 'Failed isWrite() for PUT');
        $this->assertTrue($this->buildTestRequest('MyAliasedModel', 'PATCH')->isWrite(), 'Failed isWrite() for PATCH');
        $this->assertTrue(
            $this->buildTestRequest('MyAliasedModel', 'DELETE')->isWrite(),
            'Failed isWrite() for DELETE'
        );
        $this->assertTrue($this->buildTestRequest('MyAliasedModel', 'TRACE')->isWrite(), 'Failed isWrite() for TRACE');
    }

    /**
     * @test
     */
    public function isReadTest()
    {
        $this->assertTrue($this->buildTestRequest('MyAliasedModel', 'HEAD')->isRead(), 'Failed isRead() for HEAD');
        $this->assertTrue($this->buildTestRequest('MyAliasedModel', 'GET')->isRead(), 'Failed isRead() for GET');

        $this->assertFalse(
            $this->buildTestRequest('MyAliasedModel', 'OPTIONS')->isRead(),
            'Failed isRead() for OPTIONS'
        );

        $this->assertFalse($this->buildTestRequest('MyAliasedModel', 'POST')->isRead(), 'Failed isRead() for POST');
        $this->assertFalse($this->buildTestRequest('MyAliasedModel', 'PUT')->isRead(), 'Failed isRead() for PUT');
        $this->assertFalse($this->buildTestRequest('MyAliasedModel', 'PATCH')->isRead(), 'Failed isRead() for PATCH');
        $this->assertFalse($this->buildTestRequest('MyAliasedModel', 'DELETE')->isRead(), 'Failed isRead() for DELETE');
        $this->assertFalse($this->buildTestRequest('MyAliasedModel', 'TRACE')->isRead(), 'Failed isRead() for TRACE');
    }
}
