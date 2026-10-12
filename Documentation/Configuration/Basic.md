# Configuration

## Basic configuration

Configure the access rules for different URI paths. `path` defines the URI path for the current rule. The `read` configuration belongs to the HTTP methods GET and HEAD. Any other method will be treated as `write`.

The default access is defined with the path `all`:

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        paths:
            all:
                path: all
                read: deny
                write: deny
```

Allow read and write access to the model `MyModel` of the extension `MyExt`:

```yaml
# EXT:my_ext/Configuration/Sets/{SiteSet}/settings.yaml
rest:
    settings:
        paths:
            vendor-my_ext-my_model:
                path: vendor-my_ext-my_model
                read: allow
                write: allow
```

Allow read and write access for all models of the extension `MySecondext`:

```yaml
# EXT:my_secondext/Configuration/Sets/{SiteSet}/settings.yaml
rest:
    settings:
        paths:
            vendor-my_secondext:
                path: vendor-my_secondext-*
                read: allow
                write: allow
```

Additionally you can require a valid user login for write operations:

```yaml
# EXT:my_protectedext/Configuration/Sets/{SiteSet}/settings.yaml
rest:
    settings:
        paths:
            vendor-my_protectedext:
                path: vendor-my_protectedext-*
                read: allow
                write: require
```

Learn more on [Authentication](/Configuration/Authentication/).

## Advanced configuration

### Aliases

Often the full path for a model doesn't fit the clients expectations. To make the paths looking better, path aliases can be defined:

```yaml
# config/sites/{your-site}/settings.yaml or {EXT:my-site-package}/Configuration/Sets/{MySitePackage}/settings.yaml
rest:
    settings:
        aliases:
            the_alias: my_ext-my_model
```
