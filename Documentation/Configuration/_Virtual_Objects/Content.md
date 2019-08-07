Content via REST
================

Setup
-----

Include the static TypoScript file `Virtual Object - Content (rest)` through the Template editor and configure the access.

```typo3_typoscript
plugin.tx_rest.settings {
    paths {
        virtual_object-content {
            path = virtual_object-content
            read = allow
            write = allow
        }
    }
}
```

Retrieving content
------------------

Send a GET request to `http://your-domain.com/rest/VirtualObject-Content/`.


Creating a new content
----------------------

The following displays the request body to create a new content element with `header` and `bodytext` on the page with UID `pageIdentifer`.

```json
{
    "virtual_object-content": {
        "pageIdentifier": 251,
        "creationUserId": 2,
        "hidden": false,
        "type": "text",
        "header": "A title for the new content element",
        "bodytext": "<p>Can you see this <strong>new</strong> content?</p>",
        "layout": 0,
        "deleted": false,
        "columns": 0,
        "colPos": 0
    }
}
```
