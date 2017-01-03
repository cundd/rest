<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02.01.17
 * Time: 17:43
 */
namespace Cundd\Rest\Router;

/**
 * Interface for Routes
 */
interface RouteInterface
{
    /**
     * Returns the normalized path pattern
     *
     * @return string
     */
    public function getPattern();

    /**
     * Returns the request method for this route
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns the requested parameters
     *
     * @return string[]
     */
    public function getParameters();

    /**
     * Returns the priority of this route
     *
     * Deeper nested paths have a higher priority. Fixed paths have precedence over paths with parameter expressions.
     *
     * @return int
     */
    public function getPriority();
}