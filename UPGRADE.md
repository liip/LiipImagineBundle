Upgrade
=======

0.17.x to 1.0.0
---------------

* Required minimum symfony version was updated to 2.3.
* [CacheResolver] first argument request was removed from `resolve` method.
* [CacheResolver] Now `resolve` method can return NULL or a string url of the image only.
* [CacheResolver] The method `getBrowserPath` was removed.
* [Logger] Symfony `LoggerInterface` was replaced with PSR-3 one.
