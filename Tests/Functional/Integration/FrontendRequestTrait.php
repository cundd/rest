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
    }
}
