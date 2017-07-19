<?php

namespace Cundd\Rest\Utility;

/**
 * Debug utility
 */
class DebugUtility
{
    /**
     * Print debug information about the given values (arg0, arg1, ... argN)
     *
     * @param array $variables
     */
    public static function debug(...$variables)
    {
        self::debugInternal($variables);
    }

    /**
     * @see debug()
     * @param array $variables
     */
    public static function var_dump(...$variables)
    {
        self::debugInternal($variables);
    }

    /**
     * Returns the caller of the previous method
     *
     * @return array
     */
    public static function getCaller()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        return $backtrace[2];
    }

    /**
     * @param array $variables
     */
    private static function debugInternal(array $variables)
    {
        $caller = static::getCaller();
        $htmlOutput = PHP_SAPI !== 'cli';

        if ($htmlOutput) {
            echo '<pre class="rest-debug"><code>';
        }

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
}
