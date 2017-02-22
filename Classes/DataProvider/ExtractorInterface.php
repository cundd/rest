<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 11/02/2017
 * Time: 15:14
 */
namespace Cundd\Rest\DataProvider;

/**
 * Class to prepare/extract the data to be sent from objects
 */
interface ExtractorInterface
{
    /**
     * Returns the data from the given input
     *
     * @param mixed $input
     * @return mixed
     * @throws \Exception
     */
    public function extract($input);
}
