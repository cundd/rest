<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

class GreetingTest extends AbstractGreetingCase
{
    private const ROOT_PAGE_ID = 1;

    /**
     * @dataProvider dataProviderTestLanguage
     * @param string $prefix
     * @param string $expectedMessage
     */
    public function testLanguage(string $prefix, string $expectedMessage)
    {
//        $this->importDataSet('ntf://Database/sys_language.xml');
//        $this->importDataSet('ntf://Database/tt_content.xml');
        $this->importPages();

        // Set up the page with uid 10 and include the TypoScript as sys_template record
//        $this->setUpFrontendSite(self::ROOT_PAGE_ID, $this->siteLanguageConfiguration);
        $this->setUpFrontendRootPage(
            self::ROOT_PAGE_ID,
            [
                __DIR__ . '/../Fixtures/TypoScript/BasicPage.typoscript'
//                __DIR__ . '/../Fixtures/TypoScript/JsonRenderer.typoscript'
            ]
        );
        $this->setUpFrontendSite(self::ROOT_PAGE_ID);
        $this->fetchPathAndTestMessage($prefix, $expectedMessage, self::ROOT_PAGE_ID);
    }
}
