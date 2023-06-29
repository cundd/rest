<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Manual;

trait HttpErrorDescriptionTrait
{
    /**
     * Return a descriptive error message for the response
     *
     * @param HttpResponse $response
     * @return string
     */
    public static function getErrorDescription(HttpResponse $response)
    {
        $bodyPart = PHP_EOL . '------------------------------------' . PHP_EOL
            . substr($response->getBody(), 0, (int)getenv('ERROR_BODY_LENGTH') ?: 300) . PHP_EOL
            . '------------------------------------';

        return sprintf(
            'Error for request %s %s with response content: %s',
            $response->getRequestData()->method,
            $response->getRequestData()->url,
            $bodyPart
        );
    }
}
