# Authentication

The REST extension provides simple authentication via [Basic Access Authentication](https://en.wikipedia.org/wiki/Basic_access_authentication) (`\Cundd\Rest\Authentication\BasicAuthenticationProvider`)
and an alternative via an existing user session (`\Cundd\Rest\Authentication\RequestAuthenticationProvider`).

All of the methods are built around the `fe_users` table: The `BasicAuthenticationProvider` method validates the given credentials by comparing against the username (column: `username`) and API key (column: `tx_rest_apikey`). Whereas the `RequestAuthenticationProvider` checks for an already existing frontend user session.

## Limitations

- Currently it isn't possible to force the usage of a specific Authentication Provider.
- No ACL. Different users can not be limited to specific resources.

## Configuring a frontend user

Frontend users are Webservice users. To allow a user to authenticate through the `BasicAuthenticationProvider` method, specify the users API key inside the User record in the TYPO3 Backend.

## Enable Authentication

To require a user to be authenticated to access a path set the `read` and/or `write` configuration in your Site Settings to `require`:

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml

rest:
    settings:
        paths:
            my_protectedext: path = my_protectedext-*
                read = require
                write = require
```

## Basic Auth

A popular way of authentication with Webservices is through [Basic Access Authentication](http://en.wikipedia.org/wiki/Basic_access_authentication). Use the user's API key as password when making a request.

If this method should be used without the Apache PHP module (`mod_php`; e.g. with `CGI` or `FastCGI`) the server must be configured to pass the sent header to the PHP script. This may be done in the `.htaccess` file.

```htaccess
RewriteEngine on
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
```

## Authentication via User Session

The session-based `\Cundd\Rest\Authentication\RequestAuthenticationProvider` has
to be manually enabled:

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        authenticationProvider:
            30: Cundd\Rest\Authentication\RequestAuthenticationProvider
```
