Cross-domain requests
=====================

If you want to get data from TYPO3 for use on another website via the REST API, you'll need to explicitly tell the REST extension to allow access. You can do so by adding the necessary definition in `plugin.tx_rest.settings`. This step is necessary even if you're using a similar configuration elsewhere in the same installation: for example in *.htaccess* or a third-party extension like [CORS](https://typo3.org/extensions/repository/view/cors).

By controlling the access in your TYPO3 installation, client-side workarounds (like `JSONP` in jQuery `$.ajax`) aren't necessary.

Example
-------

An example which will allow the site *example.com* to make `GET` requests.

	plugin.tx_rest.settings {
		responseHeaders {
			Access-Control-Allow-Origin = example.com
			Access-Control-Allow-Methods = GET
		}
	}
