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
final class DebugUtility
{
    /**
     * Print debug information about the given values (arg0, arg1, ... argN)
     */
    public static function debug(mixed ...$variables): void
    {
        self::debugInternal($variables);
    }

    /**
     * @see debug()
     */
    public static function var_dump(mixed ...$variables): void
    {
        self::debugInternal($variables);
    }

    /**
     * Return the caller of the previous method
     *
     * @return array{function:string,line?:int,file?:string,class?:string}
     */
    public static function getCaller(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        return $backtrace[2];
    }

    /**
     * Return if the output of debugging information is allowed
     */
    public static function allowDebugInformation(): bool
    {
        if ('' !== (string) getenv('TEST_MODE')) {
            return false;
        }
        if ('cli' === php_sapi_name()) {
            return true;
        }
        $clientAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $devIpMask = static::getDevIpMask();
        if (in_array('*', $devIpMask)) {
            return true;
        }

        return in_array($clientAddress, $devIpMask);
    }

    /**
     * @param array<string|int,mixed> $variables
     */
    private static function debugInternal(array $variables): void
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
        $file = $caller['file'] ?? '';
        $line = $caller['line'] ?? '';
        if ($htmlOutput) {
            echo "<span class='rest-debug-path' style='font-size:9px'><a href='file:$file'>";
        }
        echo "see $file @ $line";
        if ($htmlOutput) {
            echo '</a></span>';
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
    private static function getDevIpMask(): array
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'])) {
            return explode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
        }

        return [];
    }
}
