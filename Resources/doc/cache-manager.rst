

Cache Manager
=============

Cache Removal
-------------

CacheManager allows to remove cached images.

Remove the cache for a specific image and one filter set:

.. code-block:: php

    $cacheManager->remove($path, $filter);

Remove the cache for a specific image in all filter sets:

.. code-block:: php

    $cacheManager->remove($path);

Remove a list of images for a list of filter sets:

.. code-block:: php

    $cacheManager->remove(
        [$pathOne, $pathTwo],
        ['thumbnail_233x233', 'thumbnail_100x100']
    );

Clear the whole cache:

.. code-block:: php

    $cacheManager->remove();
