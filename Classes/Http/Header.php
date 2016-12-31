<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 30.12.16
 * Time: 12:39
 */

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

    const CUNDD_REST_CACHED = 'cundd-rest-cached';
}
