<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 02/06/16
 * Time: 19:31
 */

namespace Cundd\Rest\Formatter;


use Cundd\Rest\Formatter\Exception\FormatterException;

/**
 * Json Formatter
 */
class JsonFormatter implements FormatterInterface {
    /**
     * Formats the given input
     *
     * @param mixed $input
     * @return string
     */
    public function format($input) {
//        $serializedData = json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $serializedData = json_encode($input);
        if ($serializedData === false) {
            throw $this->createExceptionFromLastError();
        }
        return $serializedData;
    }

    /**
     * Returns an exception describing the last JSON error
     *
     * @return FormatterException
     */
    protected function createExceptionFromLastError() {
        if (!function_exists('json_last_error_msg')) {
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    $errorMessage = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $errorMessage = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $errorMessage = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $errorMessage = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $errorMessage = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $errorMessage = 'Unknown JSON error';
            }
        } else {
            $errorMessage = json_last_error_msg();
        }
        return new FormatterException($errorMessage, json_last_error());
    }
}
