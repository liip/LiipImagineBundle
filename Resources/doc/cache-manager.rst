

Cache Manager
=============

Cache Removal
-------------

CacheManager allows to remove cache in various ways.

Single path and filter:

.. code-block:: php

    $cacheManager->remove($path, $filter);

Single path and all filters:

.. code-block:: php

    $cacheManager->remove($path);

Some paths and some filters:

.. code-block:: php

    $cacheManager->remove(
        [$pathOne, $pathTwo],
        ['thumbnail_233x233', 'thumbnail_100x100']
    );

The whole cache.

.. code-block:: php

    $cacheManager->remove();
