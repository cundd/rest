<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.08.15
 * Time: 13:22
 */
namespace Cundd\Rest\Dispatcher;

use Bullet\Response;
use Cundd\Rest\Request;

/**
 * Interface for the main dispatcher of REST requests
 *
 * @package Cundd\Rest
 */
interface DispatcherInterface
{
    /**
     * Dispatch the request
     *
     * @param Request $request Overwrite the request
     * @param Response $responsePointer Reference to be filled with the response
     * @return boolean Returns if the request has been successfully dispatched
     */
    public function dispatch(Request $request = null, Response &$responsePointer = null);
}
