<?php

namespace Cundd\Rest\Tests;


use Zend\Diactoros\Response;

trait ResponseBuilderTrait
{
    /**
     * @param int   $status
     * @param array $headers
     * @param mixed $rawBody
     * @return Response
     */
    public static function buildTestResponse($status, array $headers = [], $rawBody = null)
    {
        if ($rawBody) {
            $stream = fopen('php://temp', 'a+');
            fputs($stream, (string)$rawBody);
        } else {
            $stream = 'php://input';
        }

        return new Response($stream, $status, $headers);
    }
}
