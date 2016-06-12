<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02/06/16
 * Time: 19:28
 */

namespace Cundd\Rest\Formatter;

/**
 * Interface for Formatter implementations
 */
interface FormatterInterface {
    /**
     * Formats the given input
     *
     * @param mixed $input
     * @return string
     */
    public function format($input);
}
