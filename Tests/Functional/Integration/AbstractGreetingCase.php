<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class AbstractGreetingCase extends FunctionalTestCase
{
    use ImportPagesTrait;
    use FrontendRequestTrait;
    use FrontendSiteSetupTrait;

    protected array $testExtensionsToLoad = ['typo3conf/ext/rest'];

    protected function setUp(): void
    {
//        $this->markTestIncomplete('Frontend sub-request based tests are currently not working');
        parent::setUp();
//        $this->backendUser = $this->setUpBackendUser(1);
        // Note late static binding - Workspace related tests override the constant
//        $this->setWorkspaceId(static::VALUE_WorkspaceId);
//        Bootstrap::initializeLanguageObject();
    }

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
        $this->assertSame('{"message":"' . $expectedMessage . '"}', $response->getBody()->getContents());
    }
}
