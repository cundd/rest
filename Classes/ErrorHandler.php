<?php

declare(strict_types=1);

namespace Cundd\Rest;

use Cundd\Rest\Utility\DebugUtility;

use function error_get_last;
use function header;
use function headers_sent;
use function http_response_code;
use function json_encode;
use function ob_end_clean;
use function register_shutdown_function;
use function sprintf;

/**
 * Error handler to capture fatal errors
 */
class ErrorHandler
{
    /**
     * Register a handler to capture fatal errors
     */
    public static function registerHandler()
    {
        register_shutdown_function([__CLASS__, 'checkForFatalError']);
    }

    /**
     * Returns if debugging information should be printed
     *
     * @return bool
     */
    public static function getShowDebugInformation(): bool
    {
        return DebugUtility::allowDebugInformation();
    }

    /**
     * Check if a fatal error occurred
     *
     * @internal
     */
    public static function checkForFatalError()
    {
        $error = error_get_last();
        if ($error !== null) {
            $type = $error['type'];
            if ($type & E_ERROR) {
                static::printError(new \Exception($error['message'], $error['type']));
            }
        }
    }

    /**
     * Print the error information
     *
     * @param \Exception $error
     */
    private static function printError(\Exception $error)
    {
        ob_end_clean();
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
            if (static::getShowDebugInformation()) {
                $response = [
                    'error' => sprintf(
                        'Error #%d: %s',
                        $error->getCode(),
                        $error->getMessage()
                    ),
                ];
            } else {
                $response = [
                    'error' => sprintf('Sorry! Something is wrong. Exception code #%d', $error->getCode()),
                ];
            }

            echo json_encode($response);
        }
    }
}
