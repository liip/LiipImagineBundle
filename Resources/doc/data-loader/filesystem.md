# Filesystem

## Using factory

``` yaml
liip_imagine:
    loaders:
        profile_photos:
            filesystem: ~
```

If you dont configure anything this loader is set by default. You can also configure a root dir where to look for the origin images:

```yaml
liip_imagine:
    loaders:
        profile_photos:
            filesystem:
                data_root: %kernel.root_dir%/../web
```

- [Back to data loaders](../data-loaders.md)
- [Back to the index](../index.md)
