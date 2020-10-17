<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Nimut\TestingFramework\Http\Response;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Util\PHP\DefaultPhpProcess;
use Text_Template;
use TYPO3\CMS\Core\Information\Typo3Version;
use function class_exists;
use function json_decode;
use function var_export;

class GreetingTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/rest'];

    /**
     * @dataProvider dataProviderTestLanguage
     * @param string $path
     * @param string $expectedMessage
     */
    public function testLanguage(string $path, string $expectedMessage)
    {
        $this->importDataSet('ntf://Database/sys_language.xml');
        $this->importDataSet('ntf://Database/tt_content.xml');

        if (class_exists(Typo3Version::class) && (new Typo3Version())->getMajorVersion() >= 10) {
            $this->importDataSet(__DIR__ . '/../Fixtures/pages-modern-typo3.xml');
        } else {
            $this->importDataSet('ntf://Database/pages.xml');
            $this->importDataSet('ntf://Database/pages_language_overlay.xml');
        }

        // Setup the page with uid 1 and include the TypoScript as sys_template record
        $this->setUpFrontendRootPage(
            1,
            [
                'ntf://TypoScript/JsonRenderer.ts',
            ]
        );

        // Fetch the frontend response
        $response = $this->fetchFrontendResponse($path . 'rest/');

        // Assert no error has occurred
        $this->assertSame('success', $response->getStatus());
        $this->assertSame('{"message":"' . $expectedMessage . '"}', $response->getContent());
    }

    public function dataProviderTestLanguage(): array
    {
        return [
            ['/', "What's up?"],
            ['/da/', "Hvad s\u00e5?"],
            ['/de/', "Wie geht's?"],
        ];
    }

    /**
     * @param string $path
     * @param int    $backendUserId
     * @param int    $workspaceId
     * @param bool   $failOnFailure
     * @param int    $frontendUserId
     * @return Response
     */
    protected function fetchFrontendResponse(
        string $path,
        $backendUserId = 0,
        $workspaceId = 0,
        $failOnFailure = true,
        $frontendUserId = 0
    ) {
        $additionalParameter = '';

        if (!empty($frontendUserId)) {
            $additionalParameter .= '&frontendUserId=' . (int)$frontendUserId;
        }
        if (!empty($backendUserId)) {
            $additionalParameter .= '&backendUserId=' . (int)$backendUserId;
        }
        if (!empty($workspaceId)) {
            $additionalParameter .= '&workspaceId=' . (int)$workspaceId;
        }

        $arguments = [
            'documentRoot' => $this->getInstancePath(),
            'requestUrl'   => 'http://localhost' . $path . $additionalParameter,
            'HTTP_ACCEPT_LANGUAGE'=>'de-DE'
        ];

        $template = new Text_Template('ntf://Frontend/Request.tpl');
        $template->setVar(
            [
                'arguments'    => var_export($arguments, true),
                'originalRoot' => ORIGINAL_ROOT,
                'ntfRoot'      => __DIR__ . '/../../../vendor/nimut/testing-framework/',
            ]
        );

        $php = DefaultPhpProcess::factory();
        $response = $php->runJob($template->render());
        $result = json_decode($response['stdout'], true);

        if ($result === null) {
            $this->fail('Frontend Response is empty.' . LF . 'Error: ' . LF . $response['stderr']);
        }

        if ($failOnFailure && $result['status'] === Response::STATUS_Failure) {
            $this->fail('Frontend Response has failure:' . LF . $result['error']);
        }

        return new Response($result['status'], $result['content'], $result['error']);
    }
}
