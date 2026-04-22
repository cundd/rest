<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual\Api;

use PHPUnit\Framework\Attributes\Test;

class GreetingTest extends AbstractApiCase
{
    #[Test]
    public function getGreetingTest(): void
    {
        $response = $this->request('/');

        $this->assertSame(200, $response->getStatusCode(), $this->getErrorDescription($response));
        $this->assertTrue(
            in_array(
                $response->getParsedBody(),
                [
                    '{"message":"What\'s up?"}',
                    '{"message":"Good Morning!"}',
                    '{"message":"Hy! Still awake?"}',
                ]
            ),
            $this->getErrorDescription($response)
        );
    }
}
