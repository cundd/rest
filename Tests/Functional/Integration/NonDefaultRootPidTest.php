<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

class NonDefaultRootPidTest extends AbstractGreetingCase
{
    private const ROOT_PAGE_ID = 10;

    /**
     * @dataProvider dataProviderTestLanguage
     * @param string $prefix
     * @param string $expectedMessage
     */
    public function testNonDefaultRootPid(string $prefix, string $expectedMessage)
    {
//        $this->importDataSet('ntf://Database/sys_language.xml');
        $this->importPagesWithRootId10();

        // Set up the page with uid 10 and include the TypoScript as sys_template record
        $this->setUpFrontendRootPage(
            self::ROOT_PAGE_ID,
            [
                __DIR__ . '/../Fixtures/TypoScript/JsonRenderer.typoscript'
            ]
        );
        $this->setUpFrontendSite(self::ROOT_PAGE_ID, $this->siteLanguageConfiguration);
        $this->fetchPathAndTestMessage($prefix, $expectedMessage, self::ROOT_PAGE_ID);
    }
}
