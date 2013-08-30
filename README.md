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

