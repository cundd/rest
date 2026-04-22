<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use Psr\Http\Message\ResponseInterface;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

trait FrontendRequestTrait
{
    /**
     * @param array<string,mixed> $queryParameters
     */
    protected function fetchFrontendResponse(
        string $path,
        ?int $pageId = null,
        array $queryParameters = [],
    ): ResponseInterface {
        $internalRequest = (new InternalRequest(AbstractIntegrationCase::BASE_URI . $path))
            ->withQueryParameters($queryParameters);
        if (null !== $pageId) {
            return $this->executeFrontendSubRequest($internalRequest->withPageId($pageId));
        } else {
            return $this->executeFrontendSubRequest($internalRequest);
        }
    }
}
