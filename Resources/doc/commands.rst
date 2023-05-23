

Console Commands
================

Remove Cache
------------

All cache for a given paths will be removed:

.. code-block:: bash

    $ php bin/console liip:imagine:cache:remove path1 path

If you use ``--filter`` parameter, all cache for a given filter will be lost:

.. code-block:: bash

    $ php bin/console liip:imagine:cache:remove --filter=thumb1 --filter=thumb2

You can combine these parameters:

.. code-block:: bash

    $ php bin/console liip:imagine:cache:remove path1 path2 --filter=thumb1 --filter=thumb2

Cache for all paths and filters will be lost when executing this command
without parameters:

.. code-block:: bash

    $ php bin/console liip:imagine:cache:remove


Warm up Cache
-------------

.. note::

    To automate cache warming, have a look at the
    :doc:`Symfony Messenger integration <optimizations/resolve-cache-images-in-background>`.

.. code-block:: bash

    $ php bin/console liip:imagine:cache:resolve path1 path2 --filter=thumb1

The cache for those two paths will be warmed up for the specified filter set.
As a result you will get:

.. code-block:: text

    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb1/path2

You can specify which filter sets to warm up:

.. code-block:: bash

    $ php bin/console liip:imagine:cache:resolve path1 --filter=thumb1 --filter=thumb2

As a result you will get:

.. code-block:: text

    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1

If you omit ``--filter``, the image will be warmed up for all available filters:

.. code-block:: bash

    $ php bin/console liip:imagine:cache:resolve path1

As a result you will get:

.. code-block:: text

    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1
