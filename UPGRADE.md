Upgrade
=======

1.0.0-alpha6 to 1.0.0-alpha7
---------------

* [Configuration] `liip_imagine.controller_action` option was removed in favour of an array of actions. See `liip_imagine.controller` config

```diff
-liip_imagine:
-    controller_action: AcmeDemoBundle:Default:filterAction
+liip_imagine:
+    controller:
+        filter_action: AcmeDemoBundle:Default:filterAction
```

1.0.0-alpha5 to 1.0.0-alpha6
---------------

 * [Route] `ImagineLoader` was removed. Please adjust your `app/config/routing.yml` file.

    ```diff
    -_imagine:
    -    resource: .
    -    type:     imagine
    +_liip_imagine:
    +    resource: "@LiipImagineBundle/Resources/config/routing.xml"
    ```

 * [Configuration] `liip_imagine.filter_sets.route` option and sub options were removed.
 * [Configuration] `liip_imagine.cache_prefix` option was removed.

0.19.x to 1.0.0-alpha5
---------------

* [Symfony] Required minimum symfony version was updated to 2.3.
* [Logger] Symfony `LoggerInterface` was replaced with PSR-3 one.
* [Cache] New `isStored` method was added.
* [Cache] The method `ResolverInterface::getBrowserPath` was removed.
* [Cache] The method `ResolverInterface::store` accept `BinaryInterface` as first argument.
* [Cache] The method `ResolverInterface::store` return nothing.
* [Cache] The method `ResolverInterface::remove` return nothing.
* [Cache] The method `ResolverInterface::remove` takes required array of filter as first argument.
* [Cache] The method `ResolverInterface::remove` takes optional path as second argument.
* [Cache] The method `ResolverInterface::clean` was removed.
* [Cache] The method `ResolverInterface::resolve` takes path and filter as arguments.
* [Cache] The method `ResolverInterface::resolve` return absolute url of the cached image.
* [Cache] The method `CacheManager::resolve` may throw OutOfBoundsException if required resolver does not exist.
* [Cache] The method `CacheManager::resolve` return absolute url of the cached image.
* [Cache] The method `CacheManager::store` accept `BinaryInterface` as first argument.
* [Cache] The method `CacheManager::store` return nothing.
* [Cache] The method `CacheManager::clearResolversCache` was removed.
* [Cache] The method `CacheManager::getWebRoot` was removed.
* [Cache] The method `CacheManager::getBrowserPath` third argument was changed, now it is `runtimeConfig`.
* [Cache] The method `CacheManager::generateUrl` third argument was changed, now it is `runtimeConfig`.
* [Cache] `NoCacheResolver` renamed to `NoCacheWebPathResolver`.
* [Cache] `AbstractFilesystemResolver` was removed.
* [Data] `LoaderInterface::find` now can return string or `BinaryInterface` instance.
* [Data] `DataManager::find` now can return `BinaryInterface` instance only.
* [Data] All data loaders moved to `Binary/Loader` folder.
* [Data] Tag name `liip_imagine.data.loader` changed to `liip_imagine.binary.loader`
* [Data] Parameter key `liip_imagine.data.loader.filesystem.class` changed to `liip_imagine.binary.loader.filesystem.class`
* [Data] Parameter key `liip_imagine.data.loader.stream.class` changed to `liip_imagine.binary.loader.stream.class`
* [Data] Service id `liip_imagine.data.loader.prototype.filesystem` changed to `liip_imagine.binary.loader.prototype.filesystem`
* [Data] Service id `liip_imagine.data.loader.prototype.stream` changed to `liip_imagine.binary.loader.prototype.stream`
* [Filter] `FilterManager::applyFilter` now return instance of `BinaryInterface`.
* [Filter] `FilterManager::applyFilter` first argument was changed from Image instance to BinaryInterface one.
* [Filter] `FilterManager::get` was removed.
* [Configuration] `liip_imagine.filter_sets.path` option was removed.
* [Configuration] `liip_imagine.filter_sets.format` option was removed.
* [Configuration] `liip_imagine.cache_mkdir_mode` option was removed.
* [Configuration] `liip_imagine.web_root` option was removed.
* [Configuration] `liip_imagine.cache` default value was changed from `web_path`to `default`.
* [Configuration] `liip_imagine.formats` option was removed.
* [Configuration] `liip_imagine.data_root` option was removed.
