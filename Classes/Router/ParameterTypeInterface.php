<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02.01.17
 * Time: 17:43
 */
namespace Cundd\Rest\Router;

interface ParameterTypeInterface
{
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const SLUG = 'slug';
    const BOOLEAN = 'boolean';
}