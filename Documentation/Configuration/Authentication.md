Authentication
==============

The REST extension provides two main ways to authenticate:

- via Basic Access Authentication (`\Cundd\Rest\Authentication\BasicAuthenticationProvider`)
- via Login Requests (`\Cundd\Rest\Authentication\CredentialsAuthenticationProvider`)

And a fallback via an existing user session (`\Cundd\Rest\Authentication\RequestAuthenticationProvider`).

All of the methods are built around the `fe_users` table: The `BasicAuthenticationProvider` and `CredentialsAuthenticationProvider` methods validate given credentials by comparing against the username (column: `username`) and API key (column: `tx_rest_apikey`). Whereas the `RequestAuthenticationProvider` checks for an already existing frontend user session.


Limitations
-----------

- Currently it isn't possible to force the usage of a specific Authentication Provider.
- No ACL. Different users can not be limited to specific resources.


Configuring a frontend user
---------------------------

Frontend users are Webservice users. To allow a user to authenticate through the `BasicAuthenticationProvider` or `CredentialsAuthenticationProvider` methods specify the users API key inside the User record in the TYPO3 Backend.


Enable Authentication
---------------------

To require a user to be authenticated to access a path set the `read` and/or `write` configuration in TypoScript to `require`:

```
plugin.tx_rest.settings.paths {
    my_protectedext {
        path = my_protectedext-*
        read = require
        write = require
    }
}
```


Basic Auth
----------

A popular way of authentication with Webservices is through [Basic Access Authentication](http://en.wikipedia.org/wiki/Basic_access_authentication). Use the user's API key as password when making a request.

If this method should be used without the Apache PHP module (`mod_php`; e.g. with `CGI` or `FastCGI`) the server must be configured to pass the sent header to the PHP script. This may be done in the `.htaccess` file.

```htaccess
RewriteEngine on
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
```


Login Request
-------------

An alternative is to authenticate through a separate Login Request. To log in send a POST request to `/rest/auth/login` with a body containing `username` and `apikey`.

Example:

```http
POST /rest/auth/login HTTP/1.1
Host: your-domain.com
Cache-Control: no-cache
Content-Type: application/x-www-form-urlencoded

username=myUserName&apikey=myApiKey
```

In addition to logging in this method allows to get the current login status:

```bash
curl -X GET http://your-domain.com/rest/auth/login
```

and to log out from the Webservice:

```bash
curl -X POST http://your-domain.com/rest/auth/logout
```

(Actually this method is not really restful, because it neglects the principle of being [Stateless](http://en.wikipedia.org/wiki/Representational_state_transfer#Stateless).)
