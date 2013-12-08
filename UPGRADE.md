Upgrade
=======

0.17.x to 1.0.0
---------------

* Required minimum symfony version was updated to 2.3.
* [CacheResolver] first argument request was removed from `resolve` method.
* [CacheResolver] Now resolve method has to return the url of the image.
* [CacheResolver] New `isStored` method was added.
* [CacheResolver] The method `getBrowserPath` was removed.
* [CacheResolver] `store` signature was changed. Now it requires `$path`, before it was `$targetPath`.
* [FilterManager] Method `get` was removed.
* [FilterManager] New, third parameter `config` was added to `applyFilter` method. It allows to change config in runtime.
* [FilterConfiguration] Set method does not return a config anymore.
* [FilterConfiguration] Allow set an empty filter config.
* [FilterConfiguration] Set default `quality` option if not configured equal to 100.
* [FilterConfiguration] Set default `format` option if not configured equal to NULL.
* [FilterConfiguration] Set default `filters` option if not configured equal to empty array.
* [Logger] Symfony `LoggerInterface` was replaced with PSR-3 one.
* [DataLoader] `LoaderInterface::find` now can return string or `BinaryInterface` instance.
* [DataLoader] `DataManager::find` now can return `BinaryInterface` instance only.
