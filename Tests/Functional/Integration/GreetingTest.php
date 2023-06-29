<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

class GreetingTest extends AbstractGreetingCase
{
    /**
     * @dataProvider dataProviderTestLanguage
     * @param string $prefix
     * @param string $expectedMessage
     */
    public function testLanguage(string $prefix, string $expectedMessage)
    {
        $this->importDataSet('ntf://Database/sys_language.xml');
        $this->importDataSet('ntf://Database/tt_content.xml');
        $this->importPages();

        // Setup the page with uid 1 and include the TypoScript as sys_template record
        $this->setUpFrontendRootPage(
            1,
            [
                'ntf://TypoScript/JsonRenderer.ts',
            ]
        );

        $this->fetchPathAndTestMessage($prefix, $expectedMessage);
    }
}
