<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

class GreetingTest extends AbstractGreetingCase
{
    private const ROOT_PAGE_ID = 1;

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
                    $this->prepareFrontendTypoScriptPath(
                        __DIR__ . '/../../../ext_typoscript_setup.txt'
                    ),
                ]
            ]
        );
        $this->setUpFrontendSite(self::ROOT_PAGE_ID, $this->siteLanguageConfiguration);
    }

    /**
     * @dataProvider dataProviderTestLanguage
     * @param string $prefix
     * @param string $expectedMessage
     */
    public function testLanguage(string $prefix, string $expectedMessage)
    {
        $this->fetchPathAndTestMessage($prefix, $expectedMessage, self::ROOT_PAGE_ID);
    }
}
