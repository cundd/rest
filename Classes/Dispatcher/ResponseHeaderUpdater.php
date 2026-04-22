<?php

declare(strict_types=1);

namespace Cundd\Rest\Dispatcher;

use Cundd\Rest\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @phpstan-import-type HeaderValue from ResponseHeaderUpdaterInterface
 */
final class ResponseHeaderUpdater implements ResponseHeaderUpdaterInterface
{
    /**
     * @param array<string,HeaderValue> $headers
     */
    public function addHeaders(
        ResponseInterface $response,
        array $headers,
        bool $overwrite,
    ): ResponseInterface {
        foreach ((array) $headers as $responseHeaderType => $value) {
            // If the header is already set skip it unless `$overwrite` is TRUE
            if (!$overwrite && $response->getHeaderLine($responseHeaderType)) {
                continue;
            }

            $preparedValue = $this->getPreparedHeaderValue($response, $value);
            $response = $response->withHeader(
                $responseHeaderType,
                $preparedValue
            );
        }

        return $response;
    }

    /**
     * @param HeaderValue $value,
     *
     * @return string|string[]
     */
    private function getPreparedHeaderValue(
        ResponseInterface $response,
        mixed $value,
    ): string|array {
        if (is_string($value)) {
            return $value;
        }

        if (true === $value) {
            return 'true';
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected header value to be either a string, array or `true`. %s given',
                    get_debug_type($value)
                ),
                1776848880
            );
        }
        if (!empty($value['userFunc'])) {
            $value['response'] = $response;
            $userFuncResult = GeneralUtility::callUserFunction(
                $value['userFunc'],
                $value,
                $this
            );
            if (is_scalar($userFuncResult)) {
                return (string) $userFuncResult;
            }
            if (InvalidArgumentException::isStringArray($userFuncResult)) {
                return $userFuncResult;
            }

            throw new InvalidArgumentException(
                'Expected result of user function for header values to be either a scalar,'
                    . 'or an array of strings',
                1776848875
            );
        }

        InvalidArgumentException::assertStringArray($value, 'header value');

        return $value;
    }
}
