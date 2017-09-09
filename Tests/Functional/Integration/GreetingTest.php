<?php

namespace Cundd\Rest\Tests\Functional\Integration;

class GreetingTest extends AbstractIntegrationCase
{
    /**
     * @test
     */
    public function getGreetingTest()
    {
        $response = $this->dispatch($this->buildTestRequest('/'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(
            in_array(
                (string)$response->getBody(),
                [
                    '{"message":"What\'s up?"}',
                    '{"message":"Good Morning!"}',
                    '{"message":"Hy! Still awake?"}',
                ]
            ),
            sprintf('Response "%s" was not expected', (string)$response->getBody())
        );
    }
}
