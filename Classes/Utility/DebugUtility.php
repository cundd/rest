<?php
declare(strict_types=1);

namespace Cundd\Rest\Utility;

use function explode;
use function getenv;
use function in_array;
use function php_sapi_name;

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
     * @param array $variables
     * @see debug()
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
     * Return if the output of debugging information is allowed
     *
     * @return bool
     */
    public static function allowDebugInformation(): bool
    {
        if ('' !== (string)getenv('TEST_MODE')) {
            return false;
        }
        if (php_sapi_name() === 'cli') {
            return true;
        }
        $clientAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $devIpMask = static::getDevIpMask();
        if (in_array('*', $devIpMask)) {
            return true;
        }

        return in_array($clientAddress, $devIpMask);
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

    /**
     * @return string[]
     */
    private static function getDevIpMask()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS'])
            && isset($GLOBALS['TYPO3_CONF_VARS']['SYS'])
            && isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'])
        ) {
            return explode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
        }

        return [];
    }
}
