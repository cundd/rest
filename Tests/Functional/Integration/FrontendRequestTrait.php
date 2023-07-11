<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Psr\Http\Message\ResponseInterface;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use function var_dump;

trait FrontendRequestTrait
{
    protected function fetchFrontendResponse(
        string $path,
        ?int   $pageId = null,
        array  $queryParameters = []
    ): ResponseInterface
    {
        $internalRequest = (new InternalRequest('http://localhost' . $path))
            ->withQueryParameters($queryParameters);
        if (null !== $pageId) {
            return $this->executeFrontendSubRequest($internalRequest->withPageId($pageId));
        } else {
            return $this->executeFrontendSubRequest($internalRequest);
        }
//
//        $arguments = [
//            'documentRoot'         => $this->getInstancePath(),
//            'requestUrl'           => 'http://localhost' . $path . $additionalParameter,
//            'HTTP_ACCEPT_LANGUAGE' => 'de-DE',
//        ];
//
//        $template = new Text_Template('ntf://Frontend/Request.tpl');
//        $template->setVar(
//            [
//                'arguments'    => var_export($arguments, true),
//                'originalRoot' => ORIGINAL_ROOT,
//                'ntfRoot'      => __DIR__ . '/../../../vendor/nimut/testing-framework/',
//            ]
//        );
//
//        $php = DefaultPhpProcess::factory();
//        $response = $php->runJob($template->render());
//        $result = json_decode($response['stdout'], true);
//
//        if ($result === null) {
//            $this->fail('Frontend Response is empty.' . LF . 'Error: ' . LF . $response['stderr']);
//        }
//
//        if ($failOnFailure && $result['status'] === NimutResponse::STATUS_Failure) {
//            $this->fail('Frontend Response has failure:' . LF . $result['error']);
//        }
//
//        return TestResponseFactory::fromResponse(
//            new NimutResponse($result['status'], $result['content'], $result['error'])
//        );
    }

}
