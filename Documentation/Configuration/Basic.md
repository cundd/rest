Configuration
=============

Basic configuration
-------------------

Configure the access rules for different URI paths. `path` defines the URI path for the current rule. The `read` configuration belongs to the HTTP methods GET and HEAD. Any other method will be treated as `write`.

The default access is defined with the path `all`:

    plugin.tx_rest.settings.paths {
        all {
            path = all
            read = deny
            write = deny
        }
    }

Allow read and write access to the model `MyModel` of the extension `MyExt`:

        ...
        my_ext-my_model {
            path = my_ext-my_model
            read = allow
            write = allow
        }
        ...

Allow read and write access for all models of the extension `MySecondext`:

        ...
        my_secondext {
            path = my_secondext-*
            read = allow
            write = allow
        }
        ...

Additionally you can require a valid user login for write operations:

        ...
        my_protectedext {
            path = my_protectedext-*
            read = allow
            write = require
        }
        ...

Learn more on [Authentication](/Configuration/Authentication/).

### Namespaces and vendor prefix

Extensions that are created with a vendor prefix have to include their prefix into the path. `\Vendor\MyExtension\Domain\Model\MyModel` as an example would need the path `vendor-my_ext-my_model`.

		...
		vendor-my_ext-my_model {
			path = vendor-my_ext-my_model
			read = allow
			write = deny
		}
		...


Advanced configuration
----------------------

### Aliases

Often the full path for a model doesn't fit the clients expectations. To make the paths looking better, path aliases can be defined:

    plugin.tx_rest.settings.aliases {
        the_alias = my_ext-my_model
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
(This setting will **NOT** apply to custom handlers)

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
