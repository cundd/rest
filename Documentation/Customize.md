Sometimes the builtin resources do not cover all requirements of a project. But REST also allows the creation of custom 
`Handler`s, which enables the developer to take full control. 

Building custom Handlers
========================

This tutorial will show how the extension can be used as a starting point for custom web services. The core of the web 
service is the Handler. The Handler implements `\Cundd\Rest\Handler\HandlerInterface` which provides the method 
`configureRoutes(RouterInterface $router, RestRequestInterface $request)`.

You can find the tutorial extension under [https://github.com/cundd/custom_rest](https://github.com/cundd/custom_rest).


Configure the access
--------------------

The following lines configure read and write access to all paths matching 
`cundd-custom_rest-*`. This allows calls to `your-domain.com/rest/cundd-custom_rest-route`, 
`your-domain.com/rest/cundd-custom_rest-path`, `your-domain.com/rest/cundd-custom_rest-whatever`, etc.

```typo3_typoscript
plugin.tx_rest.settings.paths {
    cundd-custom_rest {
        path = cundd-custom_rest-*

        read = allow
        write = allow

        handlerClass = \Cundd\CustomRest\Rest\Handler
    }
}
```

File: [ext_typoscript_setup.txt](https://github.com/cundd/custom_rest/blob/master/ext_typoscript_setup.txt)


Configure an alias
------------------

The paths shown above are not esthetic, but enable the extensions flexibility. To still provide pretty URLs aliases can 
be registered.

```typo3_typoscript
plugin.tx_rest.settings.aliases {
    customhandler = cundd-custom_rest-custom_handler
}
```

File: [ext_typoscript_setup.txt](https://github.com/cundd/custom_rest/blob/master/ext_typoscript_setup.txt)

This allows us to call `your-domain.com/rest/customhandler` instead of 
`your-domain.com/rest/cundd-custom_rest-custom_handler`.


Creating the Handler
--------------------

Now lets have a look at the core of the custom extension: the Handler. To ship a Handler with your extension create a 
class in the format `\YourVendor\YourExtensionName\Rest\Handler` and make it implement 
`\Cundd\Rest\Handler\HandlerInterface`. REST's Object Manager will then automatically use this class for any request to 
the [resource types](/FAQ/) matching `cundd-custom_rest-*`.

The more interesting method is `configureRoutes(RouterInterface $router, RestRequestInterface $request)`. REST provides 
a custom routing implementation since version 3.0 and this is the place where the actual routing is configured.


Building a route
----------------

A route is an instance of `\Cundd\Rest\Router\Route`. It encapsulates the necessary information to be compared to the 
current request and a callback that will be invoked if the route matches. One required information is the pattern that will be compared against the request URI. The second important part is the request method a route belongs to.  

> Important thing about the route: The extension suffix will be removed from the sent URL. E.g. 
`http://localhost:8888/rest/customhandler.json` will be treated as `http://localhost:8888/rest/customhandler`.

Lets create a route:

We want to provide a service if a GET request to the URL for this Handler is invoked. So we create a route that will 
match if the requested Resource Type matches the Handlers Resource Type:

```php
new Route($request->getResourceType(), 'GET', function(RestRequestInterface $request) {
    // Callback will be invoked for
    # curl -X GET http://localhost:8888/rest/customhandler
});
```

In this case `$request->getResourceType()` is used as the route's pattern. This is a shortcut to dispatch requests to 
our Handler. If we want to distinguish between `cundd-custom_rest-custom_handler` and `cundd-custom_rest-require` we 
could have added separate routes for each path:

```php
new Route('cundd-custom_rest-custom_handler', 'GET', function(RestRequestInterface $request) {
    // Callback will be invoked for
    # curl -X GET http://localhost:8888/rest/cundd-custom_rest-custom_handler
    // and because of the defined alias
    # curl -X GET http://localhost:8888/rest/customhandler
});

new Route('cundd-custom_rest-require', 'GET', function(RestRequestInterface $request) {
    // Only will be invoked for
    # curl -X GET http://localhost:8888/rest/cundd-custom_rest-require
});
```

Similarly functions for other HTTP request methods can be added. Furthermore factory methods for the most popular HTTP 
methods exist.

### GET
```php
new Route($request->getResourceType(), 'GET', function(RestRequestInterface $request) {
    # curl -X GET http://localhost:8888/rest/cundd-custom_rest-custom_handler
});

Route::get($request->getResourceType(), function(RestRequestInterface $request) {
    # curl -X GET http://localhost:8888/rest/cundd-custom_rest-custom_handler
});
```

### POST
```php
new Route($request->getResourceType(), 'POST', function(RestRequestInterface $request) {
    # curl -X POST http://localhost:8888/rest/cundd-custom_rest-custom_handler
});

Route::post($request->getResourceType(), function(RestRequestInterface $request) {
    # curl -X POST http://localhost:8888/rest/cundd-custom_rest-custom_handler
});
```

### PUT
```php
new Route($request->getResourceType(), 'PUT', function(RestRequestInterface $request) {
    # curl -X PUT http://localhost:8888/rest/cundd-custom_rest-custom_handler
});

Route::put($request->getResourceType(), function(RestRequestInterface $request) {
    # curl -X PUT http://localhost:8888/rest/cundd-custom_rest-custom_handler
});
```

### DELETE
```php
new Route($request->getResourceType(), 'DELETE', function(RestRequestInterface $request) {
    # curl -X DELETE http://localhost:8888/rest/cundd-custom_rest-custom_handler
});

Route::delete($request->getResourceType(), function(RestRequestInterface $request) {
    # curl -X DELETE http://localhost:8888/rest/cundd-custom_rest-custom_handler
});
```

### Other HTTP methods
```php
$method = 'PATCH';
new Route($request->getResourceType(), $method, function(RestRequestInterface $request) {
    # curl -X PATCH http://localhost:8888/rest/cundd-custom_rest-custom_handler
});
```

Registering a route
-------------------

Finally the created routes must be given to the router instance:

```php
$myRoute = Route::get($request->getResourceType(), function(RestRequestInterface $request) {});

$router->add($myRoute);
```


Parameters
----------

A routing system would be incomplete without the ability to pass variable parts to the callback. The REST router allows 
you to use a set of parameter expressions inside the route's pattern.

**A parameter expression must be a complete URI segment**


### Matching `string`s aka. `slug`

Extracts the value from segments matching the regular expression `[a-zA-Z0-9\._\-]+`

```php
Route::get($request->getResourceType() . '/{slug}', function(RestRequestInterface $request, $theParameter) {
    // Callback will be invoked for
    # curl -X GET http://localhost:8888/rest/customhandler/some-string
    // with $theParameter set to 'some-string'
});
```


### Matching `integer`s

Extracts integer values

```php
Route::get($request->getResourceType() . '/{integer}', function(RestRequestInterface $request, $theParameter) {
    // Callback will be invoked for
    # curl -X GET http://localhost:8888/rest/customhandler/109
    // with $theParameter set to 109
});
```


### Matching `float`s

Extracts integer values

```php
Route::get($request->getResourceType() . '/{float}', function(RestRequestInterface $request, $theParameter) {
    // Callback will be invoked for
    # curl -X GET http://localhost:8888/rest/customhandler/109.0
    // with $theParameter set to 109.0
});
```


### Matching `boolean`s

Extracts the value from segments matching the regular expression `(1|true|on|yes|0|false|off|no)` and converts it into a 
boolean

```php
Route::get($request->getResourceType() . '/{boolean}', function(RestRequestInterface $request, $theParameter) {
    // Callback will be invoked for
    # curl -X GET http://localhost:8888/rest/customhandler/yes
    // with $theParameter set to true
});
```


### Matching anything aka. `raw`

Extracts the value from segments matching the regular expression `[^/]+`

```php
Route::get($request->getResourceType() . '/{raw}', function(RestRequestInterface $request, $theParameter) {
    // Callback will be invoked for
    # curl -X GET http://localhost:8888/rest/customhandler/Mr Müller
    // with $theParameter set to 'Mr%20Müller'
});
```

> The parameter value will not be decoded before being passed to the route callback


Response
--------

The extension utilizes the `\Cundd\Rest\ResponseFactory` class to transform raw handler results into a presentation 
fitting the format from the request's `getFormat()` method.

If a specific response should be sent to the client, without additional formatting, the handler callback can return an 
instance of `\Psr\Http\Message\ResponseInterface` (e.g. built using `\Cundd\Rest\ResponseFactory::createResponse($data, int $status)`).


Putting it together
-------------------

```php
namespace Cundd\CustomRest\Rest;

use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Router\RouterInterface;

class CustomHandler implements \Cundd\Rest\Handler\HandlerInterface
{
    /**
     * @var \Cundd\CustomRest\Rest\Helper
     * @inject
     */
    protected $helper;

    public function configureRoutes(RouterInterface $router, RestRequestInterface $request)
    {
        # curl -X GET http://localhost:8888/rest/customhandler
        $router->add(
            Route::get(
                $request->getResourceType(),
                function (RestRequestInterface $request) {
                    return [
                        'path'         => $request->getPath(),
                        'uri'          => (string)$request->getUri(),
                        'resourceType' => (string)$request->getResourceType(),
                    ];
                }
            )
        );
        # curl -X GET http://localhost:8888/rest/cundd-custom_rest-require
        $router->add(
            Route::get(
                'cundd-custom_rest-require',
                function () {
                    return 'Access Granted';
                }
            )
        );

        # curl -X GET http://localhost:8888/rest/customhandler/subpath
        $router->add(
            Route::get(
                $request->getResourceType() . '/subpath',
                function (RestRequestInterface $request) {
                    return [
                        'path'         => $request->getPath(),
                        'uri'          => (string)$request->getUri(),
                        'resourceType' => (string)$request->getResourceType(),
                    ];
                }
            )
        );

        # curl -X POST -d '{"username":"johndoe","password":"123456"}' http://localhost:8888/rest/customhandler/subpath
        $router->add(
            Route::post(
                $request->getResourceType() . '/subpath',
                function (RestRequestInterface $request) {
                    return [
                        'path'         => $request->getPath(),
                        'uri'          => (string)$request->getUri(),
                        'resourceType' => (string)$request->getResourceType(),
                        'data'         => $request->getSentData(),
                    ];
                }
            )
        );

        # curl -X POST -H "Content-Type: application/json" -d '{"firstName":"john","lastName":"john"}' http://localhost:8888/rest/customhandler/create
        $router->add(
            Route::post(
                $request->getResourceType() . '/create',
                function (RestRequestInterface $request) {
                    $arguments = [
                        'person' => $request->getSentData(),
                    ];

                    /**
                     * @see \Cundd\CustomRest\Rest\Helper for more information regarding Extbase calls
                     */
                    return $this->helper->callExtbasePlugin(
                        'customRest',
                        'Cundd',
                        'CustomRest',
                        'Person',
                        'create',
                        $arguments
                    );
                }
            )
        );
    }
}
```

The lines below show how the Dispatcher will invoke your `configureRoutes()` method: 

```php
$handler = new \Cundd\CustomRest\Rest\CustomHandler(); // Actually get the Handler instance from the Object Manager
$handler->configureRoutes($router, $request);
```

To access the POST data sent by the client use the request's `getSentData()` which will return an array.

Finally the web service can be tested with

```bash
curl -X GET http://your-domain.com/rest/customhandler
curl -X GET http://your-domain.com/rest/customhandler/subpath
curl -X POST -H "Content-Type: application/json" -d '{"username":"johndoe","password":"123456"}' http://your-domain.com/rest/customhandler/subpath
```
