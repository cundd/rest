Sometimes the builtin routes do not cover all requirements of a project. But
REST also allows the creation of custom `Handler`s, which allow developers to
take full control.

# Building a custom web service

This guide will show how the extension can be used as a starting point for
custom web services. Web services are built around the concept of `Handler`s
who are responsible for handling requests for a specific [`Resource Type`](/FAQ/).

## Access configuration

The following configuration allows read and write access to all requests
matching the [`Resource Type`](/FAQ/) `customhandler` (e.g.
`https://example.com/rest/customhandler`, or
`https://example.com/rest/customhandler/subpath`)

```yaml
# {EXT:my_ext}/Configuration/Sets/{site}/settings.yaml
rest:
    settings:
        paths:
            customhandler:
                path: customhandler
                read: allow
                write: allow
                handlerClass: \MyVendor\MyExt\Rest\Handler
```

## Basic `Handler` structure

The configuration above tells the REST extension to use the service
`\MyVendor\MyExt\Rest\Handler` to handle requests matching the `Resource Type`
`customhandler`. A Handler must implement `\Cundd\Rest\Handler\HandlerInterface`
and it's method `configureRoutes(RouterInterface $router, RestRequestInterface $request)`.

A `Handler` which only accepts requests for a single URL and HTTP method could
look like this:

```php
<?php

declare(strict_types=1);

use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\RouterInterface;

/**
 * Example Custom Handler
 */
final class CustomHandler implements HandlerInterface
{
    public function configureRoutes(
        RouterInterface $router,
        RestRequestInterface $request,
    ): void {
        $router->routeGet($request->getResourceType(), $this->hello(...));
    }

    public function hello(RestRequestInterface $request): string
    {
        return 'hello world';
    }
}
```

## Building a `Route`

A route is an instance of `\Cundd\Rest\Router\Route`. It encapsulates the
necessary information for the `Router` to dispatch incoming requests to the
correct callback (typically a method provided by the `Handler`).

To route incoming requests to the correct destination, the `Route` stores a
URL-path-pattern and the accepted HTTP method. The pattern will be compared
against the current request's URI.

> Important thing about the route: The extension must not be part of the `Route`'s
> pattern (it will be removed from the requested URL. E.g.
> `http://localhost:8888/rest/customhandler.json` will be routed using the
> normalized URI `http://localhost:8888/rest/customhandler`).

The example above defines a new `Handler` with a single `Route`: A `GET`-request
to the `Handler`'s `Resource Type`. An alternative way to register the `Route`
would be:

```php
<?php

declare(strict_types=1);

use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\RouterInterface;

final class CustomHandler2 implements HandlerInterface
{
    public function configureRoutes(
        RouterInterface $router,
        RestRequestInterface $request,
    ): void {
        $router->add(
            new Route($request->getResourceType(), 'GET', $this->hello(...))
        );
    }

    public function hello(RestRequestInterface $request): string
    {
        return 'hello world';
    }
}
```

In both examples `$request->getResourceType()` is used as the `Route`'s pattern.
This can act as an alias to the `path` configured in the site settings.

The `Route`'s pattern of course is not limited to the `Resource Type`. Patterns
can consist of multiple path segments (e.g. `$request->getResourceType() . '/foo/bar'` to handle requests for `.../customhandler/foo/bar`).

```php
<?php

use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;

new Route($request->getResourceType() . '/foo/bar', 'GET', function(RestRequestInterface $request) {
    # curl -X GET http://localhost:8888/rest/customhandler/foo/bar
});
```

Callbacks for other HTTP request methods can be added in the same way. For the
most common HTTP methods factory methods are available.

```php
<?php

use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;

// Route::get(...) or
new Route($request->getResourceType() . '/foo', 'GET', function(RestRequestInterface $request) {
    # curl http://example.com/rest/customhandler/foo
});

// Route::post(...) or
new Route($request->getResourceType() . '/foo', 'POST', function(RestRequestInterface $request) {
    # curl -X POST http://example.com/rest/customhandler/foo
});

// Route::put(...) or
new Route($request->getResourceType() . '/foo', 'PUT', function(RestRequestInterface $request) {
    # curl -X PUT http://example.com/rest/customhandler/foo
});

// Route::delete(...) or
new Route($request->getResourceType() . '/foo', 'DELETE', function(RestRequestInterface $request) {
    # curl -X DELETE http://example.com/rest/customhandler/foo
});

new Route($request->getResourceType() . '/foo', 'PATCH', function(RestRequestInterface $request) {
    # curl -X PATCH http://example.com/rest/customhandler/foo
});
```

## Registering a `Route`

Finally a created `Route` must be added to the `Router` instance:

```php
<?php

use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;

$myRoute = Route::get($request->getResourceType(), function(RestRequestInterface $request) {});

$router->add($myRoute);
```

## Parameters

A routing system would be incomplete without the ability to pass variable parts
to the callback. The `Router` allows to use a set of parameter expressions
inside the route's pattern.

> **A parameter expression must be a complete URI segment**
>
> - ❌ `/foo{slug}/`
> - ✅ `/foo/{slug}/`

### Matching `string`s aka. `slug`

Extracts the value from segments matching the regular expression `[a-zA-Z0-9\._\-]+`:

```php
<?php

use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;

Route::get($request->getResourceType() . '/{slug}', function(RestRequestInterface $request, string $theParameter) {
    // Callback will be invoked for
    // curl -X GET http://localhost:8888/rest/customhandler/some-string
    // with $theParameter set to 'some-string'
});
```

### Matching `integer`s

Extract positive integer values:

```php
<?php

use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;

Route::get($request->getResourceType() . '/{integer}', function(RestRequestInterface $request, int $theParameter) {
    // Callback will be invoked for
    // curl -X GET http://localhost:8888/rest/customhandler/109
    // with $theParameter set to 109
});
```

### Matching `float`s

Extract positive float values:

```php
<?php

use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;

Route::get($request->getResourceType() . '/{float}', function(RestRequestInterface $request, float $theParameter) {
    // Callback will be invoked for
    // curl -X GET http://localhost:8888/rest/customhandler/109.0
    // with $theParameter set to 109.0
});
```

### Matching `boolean`s

Extract the value from segments matching the regular expression `(1|true|on|yes|0|false|off|no)` and converts it into a
boolean

```php
<?php

use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;

Route::get($request->getResourceType() . '/{boolean}', function(RestRequestInterface $request, bool $theParameter) {
    // Callback will be invoked for
    // curl -X GET http://localhost:8888/rest/customhandler/yes
    // with $theParameter set to true
});
```

### Matching anything aka. `raw`

Extracts the value from segments matching the regular expression `[^/]+`

```php
<?php

use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;

Route::get($request->getResourceType() . '/{raw}', function(RestRequestInterface $request, $theParameter) {
    // Callback will be invoked for
    // curl -X GET http://localhost:8888/rest/customhandler/Mr Müller
    // with $theParameter set to 'Mr%20Müller'
});
```

> The parameter value will not be decoded before being passed to the `Route`'s callback

## Response

The extension utilizes the `\Cundd\Rest\ResponseFactory` class to transform raw
`Handler` results into a presentation matching the format from the request's `getFormat()` method.

If a custom response should be sent to the client, without additional
formatting, the `Handler` callback should return an instance of
`\Psr\Http\Message\ResponseInterface` (e.g. created with the builtin
`\Cundd\Rest\ResponseFactory->createResponse($data, int $status)`).

> For a complete example see
> [`\Cundd\Rest\Tests\Functional\Fixtures\CustomHandler`](https://github.com/cundd/rest/blob/v7/Tests/Functional/Fixtures/CustomHandler.php)
