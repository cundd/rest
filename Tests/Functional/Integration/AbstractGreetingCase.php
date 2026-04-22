<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

abstract class AbstractGreetingCase extends AbstractIntegrationCase
{
    use ImportPagesTrait;
    use FrontendRequestTrait;
    use FrontendSiteSetupTrait;

    protected array $testExtensionsToLoad = ['typo3conf/ext/rest'];

    /**
     * @return list<array{0:string,1:string}>
     */
    public static function dataProviderTestLanguage(): array
    {
        return [
            ['', "What's up?"],
            ['de/', "Wie geht's?"],
            ['fr/', "Qu'est-ce qu'il y a ?"],
        ];
    }

    protected function fetchPathAndTestMessage(
        string $languagePrefix,
        string $expectedMessage,
        ?int $pageId = null,
    ): void {
        // Fetch the frontend response
        $response = $this->fetchFrontendResponse(
            $languagePrefix . 'rest/',
            $pageId,
            ['no_cache' => 1]
        );

        // Assert no error has occurred
        $this->assertSame(200, $response->getStatusCode());
        $response->getBody()->rewind();
        $this->assertSame(
            '{"message":"' . $expectedMessage . '"}',
            $response->getBody()->getContents()
        );
    }
}
