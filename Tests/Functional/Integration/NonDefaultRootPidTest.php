<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

class NonDefaultRootPidTest extends AbstractGreetingCase
{
    /**
     * @dataProvider dataProviderTestLanguage
     * @param string $prefix
     * @param string $expectedMessage
     */
    public function testNonDefaultRootPid(string $prefix, string $expectedMessage)
    {
        $this->importDataSet('ntf://Database/sys_language.xml');
        $this->importPagesWithRootId10();

        // Setup the page with uid 10 and include the TypoScript as sys_template record
        $this->setUpFrontendRootPage(
            10,
            [
                'ntf://TypoScript/JsonRenderer.ts',
            ]
        );
        $this->fetchPathAndTestMessage($prefix, $expectedMessage);
    }
}
