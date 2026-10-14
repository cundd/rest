# Cross-domain requests

The extension allows to define a list of allowed origins for
[CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CORS) requests.

The `Access-Control-Allow-Origin` header will be set to the first
`allowedOrigins` value that matches the sent `origin` header.

The following example will allow the local development site on port `3000` and
`https://example.com` to make `GET`, `POST` and preflight requests.

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        cors:
            allowedOrigins:
                - "http://localhost:3000"
                - "https//example.com"

        responseHeaders:
            Access-Control-Allow-Methods: "GET, POST, OPTIONS"

            # Allow the client to include credentials
            Access-Control-Allow-Credentials: true

            # Allow the client to send a `Content-Type` header for POST requests
            Access-Control-Allow-Headers: Content-Type
```
