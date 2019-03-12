<?php
declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Http\Header;
use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\Response as TYPO3Response;
use Zend\Diactoros\Response as ZendResponse;

/**
 * Factory class to create Response objects
 */
class ResponseFactory implements SingletonInterface, ResponseFactoryInterface
{
    public function createResponse($data, int $status): ResponseInterface
    {
        $responseClass = $this->getResponseImplementationClass();
        /** @var ResponseInterface $response */
        $response = new $responseClass();
        $response = $response->withStatus($status);
        $response->getBody()->write($data);

        return $response;
    }

    public function createErrorResponse($data, int $status, RestRequestInterface $request): ResponseInterface
    {
        return $this->createFormattedResponse($data, $status, true, $request);
    }

    public function createSuccessResponse($data, int $status, RestRequestInterface $request): ResponseInterface
    {
        return $this->createFormattedResponse($data, $status, false, $request);
    }

    /**
     * Returns a response with the given message and status code
     *
     * @param string|array         $data       Data to send
     * @param int                  $status     Status code of the response
     * @param bool                 $forceError If TRUE the response will be treated as an error, otherwise any status below 400 will be a normal response
     * @param RestRequestInterface $request
     * @return ResponseInterface
     */
    private function createFormattedResponse(
        $data,
        int $status,
        bool $forceError,
        RestRequestInterface $request
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
                switch (gettype($data)) {
                    case 'string':
                        $body = [
                            $messageKey => $data,
                        ];
                        break;

                    case 'integer':
                    case 'double':
                    case 'boolean':
                        $body = $data;
                        break;

                    case 'array':
                        $body = $data;
                        break;

                    case 'NULL':
                        $body = [
                            $messageKey => $response->getReasonPhrase(),
                        ];
                        break;

                    default:
                        $body = null;
                }

                $response->getBody()->write(json_encode($body));
                $response = $response->withHeader(Header::CONTENT_TYPE, 'application/json');
                break;

            case 'xml':
                // TODO: support more response formats

            default:
                $response->getBody()->write(
                    sprintf(
                        'Unsupported format: %s. Please set the Accept header to application/json',
                        $request->getFormat()
                    )
                );
        }

        return $response;
    }

    /**
     * @return string
     */
    private function getResponseImplementationClass()
    {
        if (class_exists(TYPO3Response::class)) {
            return TYPO3Response::class;
        }
        if (class_exists(ZendResponse::class)) {
            return ZendResponse::class;
        }
        throw new \LogicException('No response implementation found');
    }
}
