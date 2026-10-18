# OpenCode AGENTS.md - cundd/rest (TYPO3 v7)

## Architecture Overview

This is a TYPO3 CMS extension exposing REST API endpoints. Core concept:

- **Handlers** (`\Cundd\Rest\Handler\HandlerInterface`) are the primary entry points
- Handlers implement `configureRoutes()` which registers routes via Router
- Routes define patterns like `/resource/{slug}/?` that match request paths
- DataProvider converts incoming data to Domain Models (Extbase entities)

### File Structure

| Location                                  | Purpose                                       |
| ----------------------------------------- | --------------------------------------------- |
| `Classes/Handler/*.php`                   | Implement custom endpoints                    |
| `Configuration/Services.yaml`             | Register handlers, service aliases, providers |
| `ext_localconf.php`                       | Cache config (auto-generated patterns)        |
| `Configuration/Sets/{site}/settings.yaml` | Define path → handler mappings                |

## Setup Commands

### Install extension

```bash
composer require cundd/rest
```

### Initialize project

#### Using Docker/Podman

```bash
Build/Scripts/runTests.sh -s cgl              # Code style & normalization
Build/Scripts/runTests.sh -s composer         # Install dependencies
Build/Scripts/runTests.sh -s unit             # Unit tests
Build/Scripts/runTests.sh -s functional       # Functional tests
```

### Run single test file

```bash
./Build/Scripts/runTests.sh -s unit Tests/Unit/Handler/GreetingHandlerTest.php
./Build/Scripts/runTests.sh -s functional Tests/Functional/Integration/GreetingTest.php -d sqlite
```

### Code style lint & fix

```bash
.Build/bin/phpstan                          # Static analysis
./Build/Scripts/runTests.sh -s lint         # Check only
./Build/Scripts/runTests.sh -s cgl          # Fix automatically
# Debug mode (dry-run)
./Build/Scripts/runTests.sh -s cgl -n
```

## Configuration Flow

### 1. Define path pattern in site settings YAML

```yaml
# config/sites/my-site/settings.yaml OR {EXT:your-ext}/Configuration/Sets/{site}/settings.yaml
rest:
    settings:
        paths:
            my_resource:
                path: "my-resource-*" # Pattern can have wildcards
                read: allow # GET/HEAD allowed
                write: require # POST/PUT/PATCH/DELETE needs auth
                cacheLifetime: 3600 # Cache for response (seconds, `-1` for
                default)
                handlerClass: '\YourVendor\MyExt\Rest\Handler\MyResourceHandler'
```

### 2. Register Handler in Services.yaml

```yaml
# Configuration/Services.yaml
services:
    YourVendor\MyExt\Rest\Handler\MyResourceHandler:
        public: true
# Or reference CrudHandler if implementing CRUD operations
Cundd\Rest\Handler\CrudHandler:
    arguments:
        $objectManager: \TYPO3\CMS\Core\DependencyInjection\ObjectManager
```

### 3. Handler must implement `configureRoutes()`

```php
<?php
namespace YourVendor\MyExt\Rest\Handler;

use Cundd\Rest\Handler\HandlerInterface;
use Cundd\Rest\Router\Route;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\Router\RouterInterface;

class MyResourceHandler implements HandlerInterface, \Cundd\Rest\Hander\HandlerDescriptionInterface
{
    protected $objectManager; // injected

    public function getDescription(): string { /* documentation text */ return ''; }

    public function configureRoutes(
        RouterInterface $router,
        RestRequestInterface $request,
    ): void {
        // Pattern matches requests starting with /my-resource/
        $router->add(
            Route::get('my-resource/{slug}/?', function($r, $slug) {
                return ['id' => $slug];
            })
        );
    }
}
```

## Routes by HTTP Method

| Pattern                             | Maps to Handler Method(s)                                        |
| ----------------------------------- | ---------------------------------------------------------------- |
| `/{resource_type}/`                 | GET → `listAll()`, POST → `create()`                             |
| `/{resource_type}/{slug}/`          | GET → `show()`, PUT/PATCH/POST → `update()`, DELETE → `delete()` |
| `/{resource_type}/{property}` (GET) | `getProperty($value, $property)`                                 |

## Route Configuration Methods

```php
// Static factory methods
Route::get('pattern', $callback);
Route::post('pattern', $callback);
Route::put('pattern', $callback);
Route::delete('pattern', $callback);
Route::options('pattern', $callback);
Route::patch('pattern', $callback);

// Or add directly to router
$router->add($route);
```

## Parameter Types in Route Patterns

Use `{} ` syntax inside patterns for typed parameters:

| Syntax      | Matches                               | Type Conversion                 |
| ----------- | ------------------------------------- | ------------------------------- |
| `{slug}`    | `[a-zA-Z0-9\._\-]+`                   | string                          |
| `{integer}` | `[0-9]+`                              | int                             |
| `{float}`   | `[0-9]+\.[0-9]+`                      | float                           |
| `{boolean}` | `1\|true\|on\|yes\|0\|false\|off\|no` | bool                            |
| `{raw}`     | `[^/]+`                               | string (URL-encoded, no decode) |

## Testing Patterns

### Manual CLI tests with cURL

```bash
curl -X GET http://localhost:8000/rest/my-resource/item-123
curl -X POST -H "Content-Type: application/json" \
  -d '{"name":"test"}' http://localhost:8000/rest/my-resource/
```

### Unit tests (isolated, no TYPO3)

Tests in `Tests/Unit/` run with PHP directly, no TYPO3 bootstrap needed.

### Functional tests (requires full TYPO3 instance)

Tests in `Tests/Functional/` need Docker/Podman container or full TYPO3 setup. Common databases:

- sqlite (simplest, CI default)
- mariadb/mysql/postgres (full stack)

Run with:

```bash
./Build/Scripts/runTests.sh -s functional -d sqlite Tests/...
./Build/Scripts/runTests.sh -s functional -d mariadb -i 10.11 Tests/...
```

## Critical Notes

**PHP Version**: Required `^8.2` (composer.json)

**Autoloading**:

- PSR-4 autoload maps `Cundd\Rest\` namespace to `Classes/`
- Dev tests use `Cundd\Rest\Tests\` → `Tests/`

**Testing infrastructure**: All tests run in Docker containers via `.Build/Scripts/runTests.sh`

**Cache configuration**: Extension registers `cundd_rest_cache` - don't delete from TYPO3 core cache settings.

**Backend module**: The report endpoint (version 4+) shows available handlers in TYPO3 BE at `/module/system/reports/rest`.

## Common Patterns

### Custom HTTP method response

```php
public function listAll(RestRequestInterface $request): array
{
    return ['custom' => true]; // Response gets serialized based on request format (JSON/XML)
}
```

### Return pre-formatted PSR-7 response

```php
use Cundd\Rest\ResponseFactoryInterface;

public function show($slug): Psr\Http\Message\ResponseInterface
{
    return $this->responseFactory->createSuccessResponse(
        ['data' => $model->getName()],
        200,
        $request
    );
}
```

### Extend default CrudHandler

```php
class MyCrudHandler extends \Cuddd\Rest\Handler\CrudHandler implements HandlerInterface
{
    public function configureRoutes($router, $request): void
    {
        // Override base CRUD routes with custom logic
        parent::configureRoutes($router, $request);

        // Add additional method-specific route
        $router->add(
            Route::post('my-resource/{slug}/?update', [$this, 'handleSpecificUpdate'])
        );
    }

    public function update($request, string $identifier): array
    {
        // Custom update logic instead parent's generic implementation
        return ['status' => 'updated'];
    }
}
```

## Directory Ownership Summary

| Directory                     | Owner          | Purpose                                       |
| ----------------------------- | -------------- | --------------------------------------------- |
| `Classes/`                    | This extension | Core REST framework code                      |
| `Tests/Unit/`                 | This extension | Unit tests (no TYPO3)                         |
| `Tests/Functional/`           | This extension | Integration tests (requires TYPO3 runtime)    |
| `Configuration/Services.yaml` | This extension | Service definition for DI container           |
| `ext_localconf.php`           | Base extension | Cache registrations (auto-generated patterns) |

## Authentication Quick Reference

Three auth methods built-in:

| Provider                        | How it works                        | Config key value     |
| ------------------------------- | ----------------------------------- | -------------------- |
| `BasicAuthenticationProvider`   | Username + API key from HTTP header | 10                   |
| `RequestAuthenticationProvider` | Existing fe_users session           | 30 (manual register) |
| No provider                     | None required for GET               | -                    |

Enable in settings.yaml:

```yaml
rest:
    settings:
        authenticationProvider:
            30: Cundd\Rest\Authentication\RequestAuthenticationProvider
```

## CI/CD Notes

- Tests run on PHP 8.3 and 8.4 (CI yml)
- Functional tests use sqlite by default (fast), mariadb/postgres also supported
- Code style uses `./Build/bin/php-cs-fixer` with TYPO3 config
