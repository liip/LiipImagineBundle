Upgrade
=======

0.17.x to 1.0.0
---------------

* Required minimum symfony version was updated to 2.3.
* [CacheResolver] first argument request was removed from `resolve` method.
* [CacheResolver] `CacheManager::resolve` may throw OutOfBoundsException if required resolver does not exist.
* [CacheResolver] Now resolve method has to return the url of the image.
* [CacheResolver] New `isStored` method was added.
* [CacheResolver] The method `getBrowserPath` was removed.
* [CacheResolver] The method `ResolverInterface::store` accept `BinaryInterface` as first argument.
* [CacheResolver] The method `ResolverInterface::store` return nothing.
* [CacheResolver] The method `CacheManager::store` accept `BinaryInterface` as first argument.
* [CacheResolver] The method `CacheManager::store` return nothing.
* [DataLoader] `LoaderInterface::find` now can return string or `BinaryInterface` instance.
* [DataLoader] `DataManager::find` now can return `BinaryInterface` instance only.
* [Filter] `FilterManager::applyFilter` now return instance of `BinaryInterface`.
* [Filter] `FilterManager::applyFilter` first argument was changed from Image instance to BinaryInterface one.
* [Filter] `FilterManager::get` was removed.
* [Controller] `ImagineController::filterAction` first argument Request was removed. Now the method takes only two parameters.
* [Logger] Symfony `LoggerInterface` was replaced with PSR-3 one.
