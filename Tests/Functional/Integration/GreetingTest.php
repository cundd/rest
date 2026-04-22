<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use PHPUnit\Framework\Attributes\DataProvider;

class GreetingTest extends AbstractGreetingCase
{
    private const ROOT_PAGE_ID = 1000;

    public function setUp(): void
    {
        parent::setUp();

        $this->importPages();
        $this->setUpFrontendRootPage(
            self::ROOT_PAGE_ID,
            [
                'setup' => [
                    $this->prepareFrontendTypoScriptPath(
                        __DIR__ . '/../Fixtures/TypoScript/BasicPage.typoscript'
                    ),
                ],
            ]
        );
        $this->setUpFrontendSite(self::ROOT_PAGE_ID, $this->siteLanguageConfiguration);
    }

    #[DataProvider('dataProviderTestLanguage')]
    public function testLanguage(string $prefix, string $expectedMessage): void
    {
        $this->fetchPathAndTestMessage($prefix, $expectedMessage, self::ROOT_PAGE_ID);
    }
}
