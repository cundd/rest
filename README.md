rest
====

Installation
------------

1. Install the extension through the Extension Manager
2. Install [Cundd Composer extension](https://github.com/cundd/CunddComposer) and let [Composer](http://getcomposer.org/) install all the dependencies


Configuration
-------------

Configure the access rules for different URI paths. `path` defines the URI path for the current rule. The `write` configuration belongs to the HTTP methods POST, PUT, DELETE and PATCH. Any other method will be treated as `read`.

The default access is defined with the path `all`:

    plugin.tx_rest.settings.paths {
        1 {
            path = all
            read = deny
            write = deny
        }
    }
    
Allow read and write access to the mode `MyModel` of the extension `MyExt`:
      
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


Example
-------

api.domain.com/rest/*path*


Path              | Class                                | Repository                                        | Data Provider                                   | Conf
----------------- | ------------------------------------ | ------------------------------------------------- | ----------------------------------------------- | ----
/yag-gallery      | Tx_Yag_Domain_Model_Gallery[]        | Tx_Yag_Domain_Repository_GalleryRepository        | \Cundd\Rest\DataProvider\DataProvider           | *
/yag-gallery/2    | Tx_Yag_Domain_Model_Gallery          | Tx_Yag_Domain_Repository_GalleryRepository        | \Cundd\Rest\DataProvider\DataProvider           | *
/cundd-foo-bar    | \Cundd\Foo\Domain\Model\Bar[]        | \Cundd\Foo\Domain\Repository\BarRepository        | \Cundd\Rest\DataProvider\DataProvider           | \**
/cundd-foo-bar/34 | \Cundd\Foo\Domain\Model\Bar          | \Cundd\Foo\Domain\Repository\BarRepository        | \Cundd\Rest\DataProvider\DataProvider           | \**
/Document/db      | \Cundd\Rest\Domain\Model\Document[]  | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | \*\**
/Document/db/9    | \Cundd\Rest\Domain\Model\Document    | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | \*\**
/Document/db/a3b  | \Cundd\Rest\Domain\Model\Document    | \Cundd\Rest\Domain\Repository\DocumentRepository  | \Cundd\Rest\DataProvider\DocumentDataProvider   | \*\**



999b92b6-35ec-4994-88b5-bf114f61ffb1


/Document/foo/9   | \Cundd\Rest\Domain\Model\Document   | \Cundd\Foo\Domain\Repository\BarRepository     | \Cundd\Rest\DataProvider\DocumentDataProvider   | \*\**
/Document/foo     | \Cundd\Rest\Domain\Model\Document   | \Cundd\Foo\Domain\Repository\BarRepository     | \Cundd\Rest\DataProvider\DocumentDataProvider   | \*\**

http://vvb2.ateliermerz.com/rest/Document/RealEstate/9


Document/RealEstate


/yag-gallery/2    | Tx_Yag_Domain_Model_Gallery      | Tx_Yag_Domain_Repository_GalleryRepository     | \Cundd\Rest\DataProvider\DocumentDataProvider



http://localhost:8888/rest/cundd-foo-bar

Cundd\Foo\Domain\Model\Bar




(c) 2014 Daniel Corn <info@cundd.net>, cundd-ImmoRegistry-Mail

http://vvb2.ateliermerz.com/rest/(c) 2014 Daniel Corn <info@cundd.net>, cundd-ImmoRegistry-Mail


/yag-gallery      















Path              | Method  | Class                            | Repository                                     | Data Provider                         
----------------- | ------- | -------------------------------- | ---------------------------------------------- | -------------------------------------  
/yag-gallery      | GET     | Tx_Yag_Domain_Model_Gallery[]    | Tx_Yag_Domain_Repository_GalleryRepository     | \Cundd\Rest\DataProvider\DataProvider 
/yag-gallery/2    | GET     | Tx_Yag_Domain_Model_Gallery      | Tx_Yag_Domain_Repository_GalleryRepository     | \Cundd\Rest\DataProvider\DataProvider 
/yag-gallery      | DELETE  




yag-gallery


| col 3 is      | right-aligned | $1600 |
| col 2 is      | centered      |   $12 |
| zebra stripes | are neat      |    $1 |