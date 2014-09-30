rest
====
[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/cundd/rest?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

The REST API for TYPO3 CMS

Installation
------------

1. Install the extension through the Extension Manager
2. Install [Cundd Composer extension](https://github.com/cundd/CunddComposer) and let [Composer](http://getcomposer.org/) install all the dependencies
3. Configure the API access
4. Connect to `your-domain.com/rest/` (`rest/` is the request namespace)


Configuration
-------------

Configure the access rules for different URI paths. `path` defines the URI path for the current rule. The `read` configuration belongs to the HTTP methods GET and HEAD. Any other method will be treated as `write`.

> Important: The term `URI path` or simply `path` refers to the first part of the resource URI after the namespace and API version. The path of the URL `http://your-domain.com/rest/my_ext-my_model/2` would be `my_ext-my_model`.

The default access is defined with the path `all`:

    plugin.tx_rest.settings.paths {
        1 {
            path = all
            read = deny
            write = deny
        }
    }
    
Allow read and write access to the model `MyModel` of the extension `MyExt`:
      
        ...
        2 {
            path = my_ext-my_model
            read = allow
            write = allow
        }
        ...
    
Allow read and write access for all models of the extension `MySecondext`:

        ...
        3 {
            path = my_secondext-*
            read = allow
            write = allow
        }
        ...
        
Additionally you can require a valid user login for write operations:

        ...
        4 {
            path = my_protectedext-*
            read = allow
            write = require
        }
        ...
        

Advanced configuration
----------------------

### Aliases

Often the full path for a model doesn't fit the clients expectations. To make the paths looking better, path aliases can be defined:

    plugin.tx_rest.settings.aliases {
        my_model = my_ext-my_model
    }


### Root object for collection

Some clients expect a returned data collection to have a root object, others don't. If an array output like the one below meets your demands no further configuration is required.

    [
        {
            "uid": 9,
            "name": "Daniel"
        },
        {
            "uid": 10,
            "name": "Paul"
        }
        ...
    ]
        
If you require an output like the following you can enable `plugin.tx_rest.settings.addRootObjectForCollection = 1` 

    {
        "users": [
            {
                "uid": 9,
                "name": "Daniel"
            },
            {
                "uid": 10,
                "name": "Paul"
            }
            ...
        ]
    }

This wraps the whole response array into an object with the key read from the request path (i.e. `users`).

Tip: The request path will be used as root key, so you may want to configure aliases.


Virtual Objects
---------------

The current extension version introduces a new feature called `Virtual Objects`. The original versions only supported models of Extbase extensions, but now `Virtual Objects` can be configured. This configuration allows the mapping of any database record to an object that can be used by `rest`.


### Example configuration

	plugin.tx_rest.settings.virtualObjects {
	
        # Name of this Virtual Object resource 
        # which will be used in the URL rest/VirtualObject-MyResourceName/
        MyResourceName {
        
            mapping {
                # Name of the table to map to
                tableName = my_resource_table
                
                # A property to use as identifer
                identifier = property1
                
                # If FALSE and a property is encountered that is NOT defined in 
                # the mapping an exception will be thrown
                skipUnknownProperties = true
                
                properties {
                    # Define the property name
                    property1 {
                        # Define one of the types: 
                        # string, float, int(eger), bool(ean), email, slug, url, trim
                        type = string
                        
                        # Define the column name to map to
                        column = property_one
                    }
                    property2 {
                        type = float
                        column = property_two
                    }
                    property3 {
                        type = int
                        column = property_three
                    }
                    property4 {
                        type = integer
                        column = property_four
                    }
                    property5 {
                        type = bool
                        column = property_five
                    }
                    property6 {
                        type = boolean
                        column = property_six
                    }
                }
            }
        }
	}


### Types

When converting from or to a `Virtual Object` each mapped property value will be converted.

In addition to the default PHP variable types (`string`, `float`, `int(eger)`, `bool(ean)`) the following types are supported:

- `email`: The value is converted to an email (with PHP's `FILTER_SANITIZE_EMAIL`)
- `url`: The value is converted to an URL (with PHP's `FILTER_SANITIZE_URL`)
- `trim`: Whitespaces are trimmed from the beginning and end of the string value (with PHP's `trim` function)
- `slug`: The value must only contain alphanumerics, dashes and underscores (must match `/^[a-zA-Z0-9-_]+$/`)


### Examples

[Mapping TYPO3 CMS content](/Documentation/Content.md)

[Mapping TYPO3 CMS pages](/Documentation/Page.md)


Server
------

The extension includes an (experimental) web server based on [React](http://reactphp.org/). To start the server, open a terminal, navigate to the rest extension's directory and type the following command:

    php server.php port (IP)
    
Replace `port` and the optional `IP` with your configuration.


Document Storage
----------------

The Document Storage is an (experimental) store for objects of class `\Cundd\Rest\Domain\Model\Document`. The Document class is a flexible, schema-less object. It's (required) core properties are an ID and the name of the connected database. All other properties can be dynamically set and retrieved through key-value-coding methods:

Get the value for a key: 
	
	valueForKey($key)


Get the value for a key path (i.e. "foo.bar"):

	valueForKeyPath($keyPath)
	

Set the value for a key:

	setValueForKey($key, $value)


Details
-------

### Paths and the associated classes

Below you find the URL paths (the part after `rest/`) and the matching class names that will be used:

| Path              | Class                                | Repository                                        | Data Provider                                   | Conf |
| ----------------- | ------------------------------------ | ------------------------------------------------- | ----------------------------------------------- | ---- |
| /yag-gallery      | Tx_Yag_Domain_Model_Gallery[]        | Tx_Yag_Domain_Repository_GalleryRepository        | \Cundd\Rest\DataProvider\DataProvider           | *a*  |
| /yag-gallery/2    | Tx_Yag_Domain_Model_Gallery          | Tx_Yag_Domain_Repository_GalleryRepository        | \Cundd\Rest\DataProvider\DataProvider           | *a*  |
| /cundd-foo-bar    | \Cundd\Foo\Domain\Model\Bar[]        | \Cundd\Foo\Domain\Repository\BarRepository        | \Cundd\Rest\DataProvider\DataProvider           | *b*  |
| /cundd-foo-bar/34 | \Cundd\Foo\Domain\Model\Bar          | \Cundd\Foo\Domain\Repository\BarRepository        | \Cundd\Rest\DataProvider\DataProvider           | *b*  |
| /Document/db      | \Cundd\Rest\Domain\Model\Document[]  | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | *c*  |
| /Document/db/9    | \Cundd\Rest\Domain\Model\Document    | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | *c*  |
| /Document/db/a3b  | \Cundd\Rest\Domain\Model\Document    | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | *c*  |
| /cundd-daa-bar    | *                                    | *                                                 | \Cundd\Daa\Rest\DataProvider                    | *d*  |
| /cundd-daa-bar/34 | *                                    | *                                                 | \Cundd\Daa\Rest\DataProvider                    | *d*  |

*) These classes are not fixed and depend on the custom `\Cundd\Daa\Rest\DataProvider`.

### Paths, methods and the associated actions

| Path              | Method | Class                                | Action                           | Conf |
| ----------------- | ------ | ------------------------------------ | -------------------------------- | ---- |
| /yag-gallery      | GET    | Tx_Yag_Domain_Model_Gallery[]        | List all galleries               | *a*  |
| /yag-gallery      | POST   | Tx_Yag_Domain_Model_Gallery          | Create a new gallery             | *a*  |
| /yag-gallery      | DELETE |                                      | 405 Method Not Allowed           | *a*  |
| /yag-gallery/2    | GET    | Tx_Yag_Domain_Model_Gallery          | Return  the gallery with UID 2   | *a*  |
| /yag-gallery/2    | POST   | Tx_Yag_Domain_Model_Gallery          | Replace  the gallery with UID 2  | *a*  |
| /yag-gallery/2    | DELETE | Tx_Yag_Domain_Model_Gallery          | Delete  the gallery with UID 2   | *a*  |
| /yag-gallery/2    | PATCH  | Tx_Yag_Domain_Model_Gallery          | Update  the gallery with UID 2   | *a*  |


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
		10 {
			path = yag-gallery
			read = allow
			write = allow
		}
	}


*b)*

	plugin.tx_rest.settings.paths {
		10 {
			path = cundd-foo-bar
			read = allow
			write = allow
		}
	}


*c)*

	plugin.tx_rest.settings.paths {
		10 {
			path = Document-db
			read = allow
			write = allow
		}
	}


*d)*

Assume the class `\Cundd\Daa\Rest\DataProvider`.

	plugin.tx_rest.settings.paths {
		10 {
			path = Cundd-Daa-Bar
			read = allow
			write = allow
		}
	}
