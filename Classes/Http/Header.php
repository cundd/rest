<?php

namespace Cundd\Rest\Http;

/**
 * A list of often used headers
 */
abstract class Header
{
    const CONTENT_ENCODING = 'Content-Encoding';
    const CACHE_CONTROL = 'Cache-Control';
    const LAST_MODIFIED = 'Last-Modified';
    const EXPIRES = 'Expires';
    const ETAG = 'ETag';
    const CONTENT_TYPE = 'Content-Type';
    const CONTENT_LENGTH = 'Content-Length';

    // This header will be sent if the response has been cached by the REST extension
    const CUNDD_REST_CACHED = 'Cundd-Rest-Cached';

    // This header can be set to prevent the REST extension from caching a response
    const CUNDD_REST_NO_CACHE = 'Cundd-Rest-No-Cache';
}
