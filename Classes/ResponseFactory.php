<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Http\Header;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Utility\DebugUtility;
use Laminas\Diactoros\Response as LaminasResponse;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\Response as TYPO3Response;

use function is_scalar;
use function var_export;

/**
 * Factory class to create Response objects
 */
final class ResponseFactory implements SingletonInterface, ResponseFactoryInterface
{
    public function createResponse(
        string $data,
        int $status,
    ): ResponseInterface {
        $responseClass = $this->getResponseImplementationClass();
        /** @var ResponseInterface $response */
        $response = new $responseClass();
        $response = $response->withStatus($status);
        $response->getBody()->write($data);

        return $response;
    }

    public function createErrorResponse(
        string|int|array|null $data,
        int $status,
        RestRequestInterface $request,
    ): ResponseInterface {
        return $this->createFormattedResponse($data, $status, true, $request);
    }

    public function createSuccessResponse(
        string|int|array|null $data,
        int $status,
        RestRequestInterface $request,
    ): ResponseInterface {
        return $this->createFormattedResponse($data, $status, false, $request);
    }

    /**
     * Return a response with the given message and status code
     *
     * @param string|int|array<mixed>|null $data       Data to send
     * @param int                          $status     Status code of the response
     * @param bool                         $forceError If TRUE the response will be treated as an error, otherwise any status below 400 will be a normal response
     */
    private function createFormattedResponse(
        string|int|array|null $data,
        int $status,
        bool $forceError,
        RestRequestInterface $request,
    ): ResponseInterface {
        $responseClass = $this->getResponseImplementationClass();
        /** @var ResponseInterface $response */
        $response = new $responseClass();
        $response = $response->withStatus($status);

        $messageKey = 'message';
        if ($forceError || $status >= 400) {
            $messageKey = 'error';
        }

        switch ($request->getFormat()) {
            case 'json':
                $body = match (gettype($data)) {
                    'string' => [
                        $messageKey => $data,
                    ],
                    'integer', 'double', 'boolean' => $data,
                    'array'                        => $data,
                    'NULL'                         => [
                        $messageKey => $response->getReasonPhrase(),
                    ],
                    default => null,
                };

                $response->getBody()->write(
                    (string) json_encode($body, JSON_THROW_ON_ERROR)
                );

                return $response->withHeader(Header::CONTENT_TYPE, 'application/json');

            case 'txt':
            case 'html':
                if (is_scalar($data)) {
                    $response->getBody()->write((string) $data);
                } elseif (DebugUtility::allowDebugInformation()) {
                    $response->getBody()->write(var_export($data, true));
                }

                return $response;
            case 'xml':
                // TODO: support more response formats

            default:
                $response->getBody()->write(
                    sprintf(
                        'Unsupported format: %s. Please set the Accept header to application/json',
                        $request->getFormat()
                    )
                );

                return $response;
        }
    }

    private function getResponseImplementationClass(): string
    {
        if (class_exists(TYPO3Response::class)) {
            return TYPO3Response::class;
        }
        if (class_exists(LaminasResponse::class)) {
            return LaminasResponse::class;
        }
        throw new LogicException('No response implementation found');
    }
}
