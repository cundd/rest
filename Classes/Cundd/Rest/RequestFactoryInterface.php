<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 10.08.15
 * Time: 10:29
 */
namespace Cundd\Rest;


/**
 * Factory class to get the current Request
 *
 * @package Cundd\Rest
 */
interface RequestFactoryInterface {
    /**
     * Returns the request
     *
     * @return \Cundd\Rest\Request
     */
    public function getRequest();

    /**
     * Resets the current request
     *
     * @return $this
     */
    public function resetRequest();

    /**
     * Register/overwrite the current request
     *
     * @param Request $request
     * @return $this
     */
    public function registerCurrentRequest($request);

    /**
     * Returns the URI
     *
     * @param string $format Reference to be filled with the request format
     * @return string
     */
    public function getUri(&$format = '');
}