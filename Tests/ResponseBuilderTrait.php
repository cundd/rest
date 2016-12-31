<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 29.12.16
 * Time: 12:38
 */

namespace Cundd\Rest\Tests;


use Cundd\Rest\Domain\Model\Format;
use Cundd\Rest\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

trait ResponseBuilderTrait
{
    /**
     * @param int   $status
     * @param array $headers
     * @param mixed $rawBody
     * @return Response
     */
    public static function buildTestResponse($status, array $headers = array(), $rawBody = null)
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
