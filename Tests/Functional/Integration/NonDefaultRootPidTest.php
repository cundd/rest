<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

class NonDefaultRootPidTest extends AbstractGreetingCase
{
    private const ROOT_PAGE_ID = 10;

    public function setUp(): void
    {
        parent::setUp();

        $this->importPagesWithRootId10();
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
    public function testNonDefaultRootPid(string $prefix, string $expectedMessage)
    {
        $this->fetchPathAndTestMessage($prefix, $expectedMessage, self::ROOT_PAGE_ID);
    }
}
