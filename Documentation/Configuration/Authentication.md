# Authentication

The REST extension provides simple authentication via [Basic Access Authentication](https://en.wikipedia.org/wiki/Basic_access_authentication) (`\Cundd\Rest\Authentication\BasicAuthenticationProvider`)
and an alternative via an existing user session (`\Cundd\Rest\Authentication\RequestAuthenticationProvider`).

All of the methods are built around the `fe_users` table: The `BasicAuthenticationProvider` method validates the given credentials by comparing against the username (column: `username`) and API key (column: `tx_rest_apikey`). Whereas the `RequestAuthenticationProvider` checks for an already existing frontend user session.

## Limitations

- Currently it isn't possible to enforce the usage of a specific Authentication Provider.
- No ACL. Different users can not be limited to specific resources.

> ⚠︎ NEVER cache user-specific or personally identifiable information!
> The cache is shared between requests - even for authenticated requests.

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

A simple way of authentication with web services is through [Basic Access Authentication](http://en.wikipedia.org/wiki/Basic_access_authentication).

> Note: Depending on your setup the server has to be configured to forward the
> header to PHP.
> For Apache the following can be added to the `.htaccess` file:
>
> ```htaccess
> RewriteEngine on
> RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
> ```

The extension allows [Basic Access Authentication](http://en.wikipedia.org/wiki/Basic_access_authentication)
through an API key stored per user in TYPO3's frontend-user database table
(`fe_users`). To enable this feature and to specify where allowed users are
stored, the page ID must be configured.

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml

rest:
    settings:
        authentication:
            feUserProvider:
                storagePageId: 1000
```

## Authentication via User Session

The session-based `\Cundd\Rest\Authentication\RequestAuthenticationProvider` has
to be manually enabled:

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        authentication:
            provider:
                30: Cundd\Rest\Authentication\RequestAuthenticationProvider
```
