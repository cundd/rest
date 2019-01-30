Cross-domain requests
=====================

If you want to get data from TYPO3 for use on another website via the REST API, 
you'll need to explicitly tell the REST extension to allow access. You can do 
so by adding the necessary definition in `plugin.tx_rest.settings`. This step 
is necessary even if you're using a similar configuration elsewhere in the same 
installation: for example in *.htaccess* or a third-party extension like 
[CORS](https://typo3.org/extensions/repository/view/cors).

By controlling the access in your TYPO3 installation, client-side workarounds 
(like `JSONP` in jQuery `$.ajax`) aren't necessary.

Example
-------

The following example will allow the local development site on port `3000` and 
`https://production.com` to make `GET`, `POST` and preflight requests.

The `Access-Control-Allow-Origin` header will be set to the first 
`cors.allowedOrigins` value that matches the sent `origin` header.

	plugin.tx_rest.settings {
        cors.allowedOrigins {
            0 = http://localhost:3000
            1 = https://production.com
        }
		responseHeaders {
			Access-Control-Allow-Methods = POST, GET, OPTIONS
			
			# Inform the client that credentials may be used
			Access-Control-Allow-Credentials = true
			
			# Allow the client to send a `Content-Type` header for POST requests 
            Access-Control-Allow-Headers = Content-Type
		}
	}
