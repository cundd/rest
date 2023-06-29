<?php

declare(strict_types=1);

namespace Cundd\Rest\Http;

/**
 * A list of often used headers
 */
abstract class Header
{
    public const CONTENT_ENCODING = 'Content-Encoding';
    public const CACHE_CONTROL = 'Cache-Control';
    public const LAST_MODIFIED = 'Last-Modified';
    public const EXPIRES = 'Expires';
    public const ETAG = 'ETag';
    public const CONTENT_TYPE = 'Content-Type';
    public const CONTENT_LENGTH = 'Content-Length';

    public const CORS_ORIGIN = 'Access-Control-Allow-Origin';
    public const CORS_METHODS = 'Access-Control-Allow-Methods';
    public const CORS_CREDENTIALS = 'Access-Control-Allow-Credentials';

    /**
     * This header will be sent if the response has been cached by the REST extension
     */
    public const CUNDD_REST_CACHED = 'X-Cundd-Rest-Cached';

    /**
     * This header can be set to prevent the REST extension from caching a response
     */
    public const CUNDD_REST_NO_CACHE = 'X-Cundd-Rest-No-Cache';

    /**
     * Header to send debug information about the Resource Type
     */
    public const CUNDD_REST_RESOURCE_TYPE = 'X-Cundd-Rest-Resource-Type';

    /**
     * Header to send debug information about the path
     */
    public const CUNDD_REST_PATH = 'X-Cundd-Rest-Path';

    /**
     * Header to send debug information about suggested routes
     */
    public const CUNDD_REST_ROUTER_ALTERNATIVE_ROUTES = 'X-Cundd-Rest-Router-Alternative-Routes';

    /**
     * Header to send debug information about the used Handler class
     */
    public const CUNDD_REST_HANDLER = 'X-Cundd-Rest-Router-Handler';

    /**
     * Header to send debug information about the used DataProvider class
     */
    public const CUNDD_REST_DATA_PROVIDER = 'X-Cundd-Rest-Router-DataProvider';

    /**
     * Header to send debug information about allowed aliases
     */
    public const CUNDD_REST_ALIASES = 'X-Cundd-Rest-Router-Aliases';
}
