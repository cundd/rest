Page via REST
=============

Setup
-----

Include the static TypoScript file `Virtual Object - Page (rest)` through the Template editor and configure the access.

```typo3_typoscript
plugin.tx_rest.settings {
    paths {
        virtual_object-page {
            path = virtual_object-page
            read = allow
            write = allow
        }
    }
}
```

Retrieving pages
----------------

Send a GET request to `http://your-domain.com/rest/virtual_object-page/`.


Creating a new page
-------------------

The following displays the request body to create a new page element with `title` and `doktype` on the parent page with UID `pageIdentifer`.

```json
{
    "virtual_object-page": {
        "pageIdentifer": 1,
        "sorting": 32,
        "deleted": false,
        "editLock": 0,
        "hidden": false,
        "title": "A completely new page",
        "doktype": 1,
        "isSiteRoot": 0
    }
}
```
