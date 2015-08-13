Content via REST
================

Setup
-----

Include the static TypoScript file from `Virtual Object - Content (rest)` and configure the access

	plugin.tx_rest.settings {
		paths {
			2010 {
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
			"header": "How to creating a new content",
			"bodytext": "<p>Can you see <strong>this new</strong> content?</p>",
			"layout": 0,
			"deleted": false,
			"columns": 0,
			"colPos": 0
		}
	}
