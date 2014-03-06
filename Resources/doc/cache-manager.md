# CacheManager

## Cache removal

CacheManager allows to remove cache in various ways.

* Single path and filter.

```php
<?php

$cacheManager->remove($path, $filter);
```

* Single path and all filters

```php
<?php

$cacheManager->remove($path);
```

* Some paths and some filters.

```php
<?php

$cacheManager->remove(
    array($pathOne, $pathTwo),
    array('thumbnail_233x233', 'thumbnail_100x100')
);
```

* The whole cache.

```php
<?php

$cacheManager->remove();
```

[Back to the index](index.md)