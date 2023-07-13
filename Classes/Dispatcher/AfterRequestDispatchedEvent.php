<?php

declare(strict_types=1);

namespace Cundd\Rest\Dispatcher;

use Cundd\Rest\Http\RestRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Event which is fired after the dispatcher has successfully dispatched a request
 */
final class AfterRequestDispatchedEvent
{
    private RestRequestInterface $request;

    private ResponseInterface $response;

    public function __construct(RestRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): RestRequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
