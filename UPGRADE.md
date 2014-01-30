Upgrade
=======

0.19.x to 1.0.0
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
* [Filter] `FilterManager::applyFilter` now return instance of `BinaryInterface`.
* [Filter] `FilterManager::applyFilter` first argument was changed from Image instance to BinaryInterface one.
* [Filter] `FilterManager::get` was removed.
