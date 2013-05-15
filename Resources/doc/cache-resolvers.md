# Built-In CacheResolver

* [AmazonS3](cache-resolver/amazons3.md)
* [CacheResolver](cache-resolver/cache.md)

# Custom cache resolver

The ImagineBundle allows you to add your custom cache resolver classes. The only
requirement is that each cache resolver loader implement the following interface:

    Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface

To tell the bundle about your new cache resolver, register it in the service
container and apply the `liip_imagine.cache.resolver` tag to it (example here in XML):

``` xml
<service id="acme_imagine.cache.resolver.my_custom" class="Acme\ImagineBundle\Imagine\Cache\Resolver\MyCustomCacheResolver">
    <tag name="liip_imagine.cache.resolver" resolver="my_custom_cache" />
    <argument type="service" id="filesystem" />
    <argument type="service" id="router" />
    <argument>%liip_imagine.web_root%</argument>
</service>
```

For more information on the service container, see the Symfony2
[Service Container](http://symfony.com/doc/current/book/service_container.html) documentation.

You can set your custom cache reslover by adding it to the your configuration as the new
default resolver as follows:

``` yaml
liip_imagine:
    cache: my_custom_cache
```

Alternatively you can only set the custom cache resolver for just a specific filter set:

``` yaml
liip_imagine:
    filter_sets:
        my_special_style:
            cache: my_custom_cache
            filters:
                my_custom_filter: { }
```

For an example of a cache resolver implementation, refer to
`Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver`.

## CacheClearer

Custom cache resolver classes must implement the ```clear``` method, at worst doing nothing.

When the ```console cache:clear``` command is run, the clear method of all the registered cache
resolvers is automatically called.

[Back to the index](index.md)
