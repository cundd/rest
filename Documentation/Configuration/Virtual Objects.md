Virtual Objects
===============

In addition to Extbase models `rest` can export data from any database table. `Virtual Objects` are defined in TypoScript and configure the mapping between database columns and properties.

IMPORTANT: Please be aware that changes (including but not limited to update and delete) to `Virtual Objects` are performed directly on the database. The TCA is NOT respected. When deleting a record, it will be removed permanently!

Example configuration
---------------------

```typo3_typoscript
plugin.tx_rest.settings.virtualObjects {

    # Name of this Virtual Object resource 
    # which will be used in the URL rest/VirtualObject-MyResourceName/
    # or in the URL rest/virtual_object-my_resource_name/
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
```

Types
-----

When converting from or to a `Virtual Object` each mapped property value will be converted.

In addition to the default PHP variable types (`string`, `float`, `int(eger)`, `bool(ean)`) the following types are supported:

- `email`: The value is converted to an email (with PHP's `FILTER_SANITIZE_EMAIL`)
- `url`: The value is converted to an URL (with PHP's `FILTER_SANITIZE_URL`)
- `trim`: Whitespaces are trimmed from the beginning and end of the string value (with PHP's `trim` function)
- `slug`: The value must only contain alphanumerics, dashes and underscores (must match `/^[a-zA-Z0-9-_]+$/`)


Mappings for contents and pages
-------------------------------

[Mapping TYPO3 CMS content](../../Configuration/_Virtual_Objects/Content/)

[Mapping TYPO3 CMS pages](../../Configuration/_Virtual_Objects/Page/)
