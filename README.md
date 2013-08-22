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
            read = allow
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
        
