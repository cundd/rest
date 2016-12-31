<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 17/06/16
 * Time: 19:08
 */

namespace Cundd\Rest\Router;


use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Request;
use Cundd\Rest\Response;

class Router
{
    private $registeredRoutes = array(
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
    );

    /**
     * @param RestRequestInterface $request
     * @return Response
     */
    public function dispatch(RestRequestInterface $request)
    {
        return new Response();
    }

    /**
     * @param callable $handler
     * @param string $path
     * @param string $method
     * @return $this
     */
    public function register(callable $handler, $path, $method)
    {
        $method = strtoupper($method);
        $pathParts = array_filter(explode('/', trim($path, '/')));
//        $last = end($pathParts);

        if (!isset($this->registeredRoutes[$method])) {
            $this->registeredRoutes[$method] = array();
        }

        $routeTree = $this->appendHandlerToTree($pathParts, $handler, $this->registeredRoutes[$method]);
        if (!isset($this->registeredRoutes[$method])) {
            $this->registeredRoutes[$method] = array();
        }

        $this->registeredRoutes[$method] = array_merge_recursive($this->registeredRoutes[$method], $routeTree);


//        $tail['_hndr'] = &$handler;
//        var_dump($tail);
////
//        $routeTree = array_reduce($pathParts, function (array &$carry, $item) {
//            return $carry[$item] = array();
//            return $carry;
//        }, array());

        var_dump($this->registeredRoutes);

        foreach ($pathParts as $part) {
            if (!isset($routeTree[$part])) {
                $routeTree[$part] = $handler;
            }
        }

//        $last

        return $this;
    }


    private function appendHandlerToTree(array $pathParts, $handler, array &$carry)
    {
        $currentPart = array_shift($pathParts);
        if (count($pathParts) > 0) {
            return array(
                $currentPart => $this->appendHandlerToTree($pathParts, $handler, $carry),
            );
//            return array(
//                $currentPart => array(
//                    0 => $this->pushHandlerToTree($pathParts, $handler, $carry),
//                ),
//            );
        }

        return array(
            $currentPart => $handler,
        );
//        return array(
//            $currentPart => array(
//                1 => $handler,
//            ),
//        );
    }


    private function resolveHandler($path)
    {

    }

    /**
     * @return array
     */
    public function dump()
    {
        return $this->registeredRoutes;
    }
}