Content via REST
================

Setup
-----

Include the static TypoScript file `Virtual Object - Content (rest)` through the Template editor and configure the access. In this example, the path key `201611161200` is a date/time-based unique number.

	plugin.tx_rest.settings {
		paths {
			201611161200 {
				path = VirtualObject-Content
				read = allow
				write = allow
			}
		}
	}


Retrieving content
------------------

Send a GET request to `http://your-domain.com/rest/VirtualObject-Content/`.


Creating a new content
----------------------

The following displays the request body to create a new content element with `header` and `bodytext` on the page with UID `pageIdentifer`.

	{
		"VirtualObject-Content": {
			"pageIdentifer": 251,
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
