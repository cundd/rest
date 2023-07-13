<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Cundd\Rest\Tests\Functional\AbstractCase;

abstract class AbstractGreetingCase extends AbstractCase
{
    use ImportPagesTrait;
    use FrontendRequestTrait;
    use FrontendSiteSetupTrait;

    protected array $testExtensionsToLoad = ['typo3conf/ext/rest'];

    public function dataProviderTestLanguage(): array
    {
        return [
            ['/', "What's up?"],
            ['/dk/', "Hvad s\u00e5?"],
            ['/de/', "Wie geht's?"],
        ];
    }

    protected function fetchPathAndTestMessage(string $prefix, string $expectedMessage, ?int $pageId = null): void
    {
        // Fetch the frontend response
        $response = $this->fetchFrontendResponse($prefix . 'rest/', $pageId, ['no_cache' => 1]);

        // Assert no error has occurred
        $this->assertSame(200, $response->getStatusCode());
        $response->getBody()->rewind();
        $this->assertSame('{"message":"' . $expectedMessage . '"}', $response->getBody()->getContents());
    }
}
