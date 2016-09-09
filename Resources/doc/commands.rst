

Console Commands
================

Remove Cache
------------

All cache for a given paths will be removed:

.. code-block:: bash

    $ php app/console liip:imagine:cache:remove path1 path

If you use ``--filters`` parameter, all cache for a given filters will be lost:

.. code-block:: bash

    $ php app/console liip:imagine:cache:remove --filters=thumb1 --filters=thumb2

You can combine these parameters:

.. code-block:: bash

    $ php app/console liip:imagine:cache:remove path1 path2 --filters=thumb1 --filters=thumb2

Cache for all paths and filters will be lost when executing this command
without parameters:

.. code-block:: bash

    $ php app/console liip:imagine:cache:remove


Resolve Cache
-------------

.. code-block:: bash

    $ php app/console liip:imagine:cache:resolve path1 path2 --filters=thumb1

Cache for the two paths will be resolved using the passed filter.
As a result you will get:

.. code-block:: text

    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb1/path2

You can pass few filters:

.. code-block:: bash

    $ php app/console liip:imagine:cache:resolve path1 --filters=thumb1 --filters=thumb2

As a result you will get:

.. code-block:: text

    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1

If you omit ``--filters`` parameter then to resolve given paths will be used
all configured and available filters in application:

.. code-block:: bash

    $ php app/console liip:imagine:cache:resolve path1

As a result you will get:

.. code-block:: text

    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1
