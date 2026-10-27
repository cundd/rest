## Resource Type

The resource type is an identifier for a model class. It is the first segment of the request path, after alias mapping has been applied.

## Sending data

Different "layouts" for sent data are supported by the extension:

### Send raw body

```bash
curl -X POST \
-H "Content-Type: application/json" \
-d '{
  "property1":"Value 1",
  "property2":"Value 2"
}' \
"http://your-domain.com/rest/..."
```

### Send form-data

```bash
curl -X POST \
-H "Content-Type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW" \
-d '------WebKitFormBoundary7MA4YWxkTrZu0gW
Content-Disposition: form-data; name="field1"

Value 1
------WebKitFormBoundary7MA4YWxkTrZu0gW
Content-Disposition: form-data; name="field2"

Value 2
------WebKitFormBoundary7MA4YWxkTrZu0gW--' \
"http://your-domain.com/rest/..."
```

### Send x-www-form-urlencoded

```bash
curl -X POST \
-H "Content-Type: application/x-www-form-urlencoded" \
-d 'field1=Value 1&field2=Value 2' \
"http://your-domain.com/rest/..."
```

In a custom `Handler` implementation the sent data can be retrieved through the
request's `getSentData()` method.

## Customize the output of a model

The easiest way to customize the properties exported by your models is through implementing the [JsonSerializable interface](http://php.net/manual/en/class.jsonserializable.php).

```php
<?php

declare(strict_types=1);

class Person extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    implements JsonSerializable {

    public function jsonSerialize(): mixed
    {
        return [
            'companyName' => 'the company name'
        ];
    }
}
```

### Add additional headers

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        responseHeaders:
            Access-Control-Allow-Origin: http://localhost:5173
            Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE
```

## Preprocess input data

The best way to preprocess sent data before saving is to create a custom `DataProvider`.

Lets assume your extension is in the namespace `\Foo\Bar`. When you request `/foo-bar-model` the Object Manager will look for a class called `\Foo\Bar\Rest\DataProvider` and will use it to fetch and create models (the built in DataProvider `\Cundd\Rest\DataProvider\DataProvider` is used as fallback).

You can extend the default DataProvider and overwrite `prepareModelData()`.

```php
<?php

declare(strict_types=1);

namespace Foo\Bar\Rest;

class DataProvider extends \Cundd\Rest\DataProvider\DataProvider {
    #[\Override]
    protected function prepareModelData(array $data): array
    {
		// Work with the data
		return $data;
	}
}
```

## How do paths and classes work together?

### Paths, resource types and the associated classes

Below you find examples of the URL paths (the part after `rest/`) and the matching class names that will be used:

| Path              | Resource Type | Class                               | Repository                                       | Data Provider                                 | Conf                    |
| ----------------- | ------------- | ----------------------------------- | ------------------------------------------------ | --------------------------------------------- | ----------------------- |
| /cundd-foo-bar    | cundd-foo-bar | \Cundd\Foo\Domain\Model\Bar[]       | \Cundd\Foo\Domain\Repository\BarRepository       | \Cundd\Rest\DataProvider\DataProvider         | [_a_](#configuration-a) |
| /cundd-foo-bar/34 | cundd-foo-bar | \Cundd\Foo\Domain\Model\Bar         | \Cundd\Foo\Domain\Repository\BarRepository       | \Cundd\Rest\DataProvider\DataProvider         | [_a_](#configuration-a) |
| /Document/db      | Document-db   | \Cundd\Rest\Domain\Model\Document[] | \Cundd\Rest\Domain\Repository\DocumentRepository | \Cundd\Rest\DataProvider\DocumentDataProvider | [_b_](#configuration-b) |
| /Document/db/9    | Document-db   | \Cundd\Rest\Domain\Model\Document   | \Cundd\Rest\Domain\Repository\DocumentRepository | \Cundd\Rest\DataProvider\DocumentDataProvider | [_b_](#configuration-b) |
| /Document/db/a3b  | Document-db   | \Cundd\Rest\Domain\Model\Document   | \Cundd\Rest\Domain\Repository\DocumentRepository | \Cundd\Rest\DataProvider\DocumentDataProvider | [_b_](#configuration-b) |
| /cundd-daa-bar    | cundd-daa-bar | \*                                  | \*                                               | \Cundd\Daa\Rest\DataProvider                  | [_c_](#configuration-c) |
| /cundd-daa-bar/34 | cundd-daa-bar | \*                                  | \*                                               | \Cundd\Daa\Rest\DataProvider                  | [_c_](#configuration-c) |

\*) These classes are not fixed and depend on the custom `\Cundd\Daa\Rest\DataProvider`.

### Paths, methods and the associated actions

Example: A Document Storage

| Path           | Method | Class                               | Action                                                 | Conf                    |
| -------------- | ------ | ----------------------------------- | ------------------------------------------------------ | ----------------------- |
| /Document/db   | GET    | \Cundd\Rest\Domain\Model\Document[] | List all Documents in database 'db'                    | [_b_](#configuration-b) |
| /Document/db   | POST   | \Cundd\Rest\Domain\Model\Document   | Create a new Document in database 'db'                 | [_b_](#configuration-b) |
| /Document/db   | DELETE |                                     | 405 Method Not Allowed                                 | [_b_](#configuration-b) |
| /Document/db/9 | GET    | \Cundd\Rest\Domain\Model\Document   | Return the Document with UID or ID 2 in database 'db'  | [_b_](#configuration-b) |
| /Document/db/9 | POST   | \Cundd\Rest\Domain\Model\Document   | Replace the Document with UID or ID 2 in database 'db' | [_b_](#configuration-b) |
| /Document/db/9 | DELETE | \Cundd\Rest\Domain\Model\Document   | Delete the Document with UID or ID 2 in database 'db'  | [_b_](#configuration-b) |
| /Document/db/9 | PATCH  | \Cundd\Rest\Domain\Model\Document   | Update the Document with UID or ID 2 in database 'db'  | [_b_](#configuration-b) |

### Configuration

_b)_
<a id="configuration-a"></a>

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        paths:
            cundd-foo-bar:
                path: cundd-foo-bar
                read: allow
                write: allow
```

_c)_
<a id="configuration-b"></a>

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        paths:
            document-db:
                path: Document-db
                read: allow
                write: allow
```

_d)_
<a id="configuration-c"></a>

Assume the class `\Cundd\Daa\Rest\DataProvider` exists.

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        paths:
            cundd-daa-bar:
                path: Cundd-Daa-Bar
                read: allow
                write: allow
```

## Caching

To enable caching for all resource types set the cache lifetime inside `settings`:

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        cacheLifetime: 3600
```

Additionally, a separate lifetime for the expires header can be defined:

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        expiresHeaderLifetime: 300
```

Furthermore, it is possible to define a lifetime for a resource type separately:

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        paths:
            cundd-daa-bar:
                path: Cundd-Daa-Bar
                read: allow
                write: allow
                cacheLifetime: 3600
```

> If `cacheLifetime` is set to `0` TYPO3 will cache the response forever

## Install TYPO3 and REST in a subdirectory

If the TYPO3 installation is inside a subdirectory the environment variable `TYPO3_REST_REQUEST_BASE_PATH` has to be set to the subdirectories name.

E.g.: TYPO3 is installed in `$DOC_ROOT/dev_install/` and accessed through `http://your-domain.com/dev_install/rest/` set `TYPO3_REST_REQUEST_BASE_PATH` to `dev_install`.

## Internationalization and localization

> The start page of your site should be translated. The extension will use it as the context for handling the request.

- Since version `3.6.0` TYPO3's site handling is supported
