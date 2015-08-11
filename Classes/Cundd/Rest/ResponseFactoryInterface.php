<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 10.08.15
 * Time: 12:01
 */
namespace Cundd\Rest;

use Bullet\Response;


/**
 * Factory class to create Response objects
 *
 * @package Cundd\Rest
 */
interface ResponseFactoryInterface {
    /**
     * Returns a response with the given message and status code
     *
     * @param string|array $data
     * @param int $status
     * @return Response
     */
    public function createErrorResponse($data, $status);

    /**
     * Returns a response with the given message and status code
     *
     * @param string|array $data
     * @param int $status
     * @return Response
     */
    public function createSuccessResponse($data, $status);
}