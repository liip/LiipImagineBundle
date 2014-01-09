Upgrade
=======

0.17.x to 1.0.0
---------------

* Required minimum symfony version was updated to 2.3.
* [CacheResolver] first argument request was removed from `resolve` method.
* [CacheResolver] Now resolve method has to return the url of the image.
* [CacheResolver] New `isStored` method was added.
* [CacheResolver] The method `getBrowserPath` was removed.
* [DataLoader] `LoaderInterface::find` now can return string or `BinaryInterface` instance.
* [DataLoader] `DataManager::find` now can return `BinaryInterface` instance only.
* [Filter] `FilterManager::applyFilter` now return instance of `BinaryInterface`.
* [Filter] `FilterManager::applyFilter` first argument was changed from Image instance to BinaryInterface one.
* [Filter] `FilterManager::get` was removed.
* [Logger] Symfony `LoggerInterface` was replaced with PSR-3 one.
