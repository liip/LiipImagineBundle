Upgrade
=======

0.17.x to 1.0.0
---------------

* Required minimum symfony version was updated to 2.3.
* [CacheResolver] first argument request was removed from `resolve` method.
* [CacheResolver] Now resolve method has to return the url of the image.
* [CacheResolver] New `isStored` method was added.
* [CacheResolver] The method `getBrowserPath` was removed.
* [DataLoader] `LoaderInterface::find` now can return string or `RawImage instance.
* [DataLoader] `DataManager::find` now can return `RawImage instance only.
* [Logger] Symfony `LoggerInterface` was replaced with PSR-3 one.
