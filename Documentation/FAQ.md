Resource Type
-------------

The resource type is a identifier for a model class. It is the first segment of the request path, after alias mapping has been applied.


Sending data
------------

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

In a custom handler the sent data can be retrieved through the request's `getSentData()` method.


Customize the output of a model
-------------------------------

The easiest way to customize the properties exported by your models is through implementing the [JsonSerializable interface](http://php.net/manual/en/class.jsonserializable.php).

```php
class Person extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity 
implements JsonSerializable {
    public function jsonSerialize()
    {
        return [
            'companyName' => 'the company name'
        ];
    }
}
```


### Show information about the models using __class property 

The extension can add a property to the JSON output that provides the object's class name. This can easily be enabled with TypoScript:

```typoscript
plugin.tx_rest.settings {
    addClass = 1
}
```


### Add additional headers

```typoscript
plugin.tx_rest.settings {
	responseHeaders {
		Access-Control-Allow-Origin = example.com
		Access-Control-Allow-Methods = GET, POST, OPTIONS, DELETE
	}
}
```


Preprocess input data
---------------------

The best way to preprocess sent data before saving is to create a custom `DataProvider`.

Lets assume your extension is in the namespace `\Foo\Bar`. When you request `/foo-bar-model` the Object Manager will look for a class called `\Foo\Bar\Rest\DataProvider` and will use it to fetch and create models (the built in DataProvider `\Cundd\Rest\DataProvider\DataProvider` is used as fallback).

You can extend the default DataProvider and overwrite `prepareModelData()`.


```php
namespace Foo\Bar\Rest;
class DataProvider extends \Cundd\Rest\DataProvider\DataProvider {
	/**
	 * @param $data
	 * @return array
	 */
	protected function prepareModelData($data) {
		// Work with the data
		return $data;
	}
}
```

How do paths and classes work together?
---------------------------------------

### Paths, resource types and the associated classes

Below you find examples of the URL paths (the part after `rest/`) and the matching class names that will be used:

| Path              | Resource Type | Class                                | Repository                                        | Data Provider                                   | Conf |
| ----------------- | ------------- | ------------------------------------ | ------------------------------------------------- | ----------------------------------------------- | ---- |
| /pix-gallery      | pix-gallery   | Tx_Pix_Domain_Model_Gallery[]        | Tx_Pix_Domain_Repository_GalleryRepository        | \Cundd\Rest\DataProvider\DataProvider           | *a*  |
| /pix-gallery/2    | pix-gallery   | Tx_Pix_Domain_Model_Gallery          | Tx_Pix_Domain_Repository_GalleryRepository        | \Cundd\Rest\DataProvider\DataProvider           | *a*  |
| /cundd-foo-bar    | cundd-foo-bar | \Cundd\Foo\Domain\Model\Bar[]        | \Cundd\Foo\Domain\Repository\BarRepository        | \Cundd\Rest\DataProvider\DataProvider           | *b*  |
| /cundd-foo-bar/34 | cundd-foo-bar | \Cundd\Foo\Domain\Model\Bar          | \Cundd\Foo\Domain\Repository\BarRepository        | \Cundd\Rest\DataProvider\DataProvider           | *b*  |
| /Document/db      | Document-db   | \Cundd\Rest\Domain\Model\Document[]  | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | *c*  |
| /Document/db/9    | Document-db   | \Cundd\Rest\Domain\Model\Document    | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | *c*  |
| /Document/db/a3b  | Document-db   | \Cundd\Rest\Domain\Model\Document    | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | *c*  |
| /cundd-daa-bar    | cundd-daa-bar | *                                    | *                                                 | \Cundd\Daa\Rest\DataProvider                    | *d*  |
| /cundd-daa-bar/34 | cundd-daa-bar | *                                    | *                                                 | \Cundd\Daa\Rest\DataProvider                    | *d*  |

*) These classes are not fixed and depend on the custom `\Cundd\Daa\Rest\DataProvider`.


### Paths, methods and the associated actions

Example: Gallery

| Path              | Method | Class                                | Action                           | Conf |
| ----------------- | ------ | ------------------------------------ | -------------------------------- | ---- |
| /pix-gallery      | GET    | Tx_Pix_Domain_Model_Gallery[]        | List all galleries               | *a*  |
| /pix-gallery      | POST   | Tx_Pix_Domain_Model_Gallery          | Create a new gallery             | *a*  |
| /pix-gallery      | DELETE |                                      | 405 Method Not Allowed           | *a*  |
| /pix-gallery/2    | GET    | Tx_Pix_Domain_Model_Gallery          | Return  the gallery with UID 2   | *a*  |
| /pix-gallery/2    | POST   | Tx_Pix_Domain_Model_Gallery          | Replace  the gallery with UID 2  | *a*  |
| /pix-gallery/2    | DELETE | Tx_Pix_Domain_Model_Gallery          | Delete  the gallery with UID 2   | *a*  |
| /pix-gallery/2    | PATCH  | Tx_Pix_Domain_Model_Gallery          | Update  the gallery with UID 2   | *a*  |


Example: Document Storage

| Path              | Method | Class                                | Action                                                  | Conf |
| ----------------- | ------ | ------------------------------------ | ------------------------------------------------------- | ---- |
| /Document/db      | GET    | \Cundd\Rest\Domain\Model\Document[]  | List all Documents in database 'db'                     | *c*  |
| /Document/db      | POST   | \Cundd\Rest\Domain\Model\Document    | Create a new Document in database 'db'                  | *c*  |
| /Document/db      | DELETE |                                      | 405 Method Not Allowed                                  | *c*  |
| /Document/db/9    | GET    | \Cundd\Rest\Domain\Model\Document    | Return  the Document with UID or ID 2 in database 'db'  | *c*  |
| /Document/db/9    | POST   | \Cundd\Rest\Domain\Model\Document    | Replace the Document with UID or ID 2 in database 'db'  | *c*  |
| /Document/db/9    | DELETE | \Cundd\Rest\Domain\Model\Document    | Delete  the Document with UID or ID 2 in database 'db'  | *c*  |
| /Document/db/9    | PATCH  | \Cundd\Rest\Domain\Model\Document    | Update  the Document with UID or ID 2 in database 'db'  | *c*  |


### Configuration

*a)*

	plugin.tx_rest.settings.paths {
		path {
			path = pix-gallery
			read = allow
			write = allow
		}
	}


*b)*

	plugin.tx_rest.settings.paths {
		path {
			path = cundd-foo-bar
			read = allow
			write = allow
		}
	}


*c)*

	plugin.tx_rest.settings.paths {
		path {
			path = Document-db
			read = allow
			write = allow
		}
	}


*d)*

Assume the class `\Cundd\Daa\Rest\DataProvider` exists.

	plugin.tx_rest.settings.paths {
		path {
			path = Cundd-Daa-Bar
			read = allow
			write = allow
		}
	}


Caching
-------

To enable caching set the cache life time:

```typoscript
plugin.tx_rest.settings.cacheLifetime = 3600
```

Additionally a separate life time for the expires header can be defined:

```typoscript
plugin.tx_rest.settings.expiresHeaderLifetime = 300
```


Install TYPO3 and REST in a subdirectory
----------------------------------------

If the TYPO3 installation is inside a subdirectory the environment variable `TYPO3_REST_REQUEST_BASE_PATH` has to be set to the subdirectories name.

E.g.: TYPO3 is installed in `$DOC_ROOT/dev_install/` and accessed through `http://your-domain.com/dev_install/rest/` set `TYPO3_REST_REQUEST_BASE_PATH` to `dev_install`.

