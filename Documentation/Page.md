Page via REST
=============

Setup
-----

Include the static TypoScript file `Virtual Object - Page (rest)` through the Template editor and configure the access. In this example, the path key `2015` is intended to be a unique number.

	plugin.tx_rest.settings {
		paths {
			2015 {
				path = VirtualObject-Page
				read = allow
				write = allow
			}
		}
	}


Retrieving pages
----------------

Send a GET request to `http://your-domain.com/rest/VirtualObject-Page/`.


Creating a new page
-------------------

The following displays the request body to create a new page element with `title` and `doktype` on the parent page with UID `pageIdentifer`.

	{
		"VirtualObject-Page": {
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
