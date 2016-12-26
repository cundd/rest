<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 23.08.14
 * Time: 12:06
 */

namespace Cundd\Rest\Utility;

/**
 * Debug utility
 *
 * @package Cundd\Rest\Utility
 */
class DebugUtility
{
    /**
     * Print debug information about the given values (arg0, arg1, ... argN)
     *
     * @param $variable
     */
    public static function debug($variable)
    {
        $caller = static::getCaller();
        $htmlOutput = false;

        if ($htmlOutput) {
            echo '<pre class="rest-debug"><code>';
        }

        $variables = func_get_args();
        foreach ($variables as $variable) {
            var_dump($variable);
            echo PHP_EOL;
        }
        if ($htmlOutput) {
            echo '</code>';
        }

        // Debug info
        $file = $caller['file'];
        $line = $caller['line'];
        if ($htmlOutput) {
            echo "<span class='rest-debug-path' style='font-size:9px'><a href='file:$file'>";
        }
        echo "see $file @ $line";
        if ($htmlOutput) {
            echo "</a></span>";
        }

        if ($htmlOutput) {
            echo '</pre>';
        }
        echo PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * @see debug()
     */
    public static function var_dump($variable)
    {
        $variables = func_get_args();
        call_user_func_array(array(__CLASS__, 'debug'), $variables);
    }

    /**
     * Returns the caller of the previous method
     *
     * @return array
     */
    public static function getCaller()
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        } else {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        return $backtrace[1];
    }
}
