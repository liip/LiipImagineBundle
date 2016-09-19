
.. _data-loaders-filesystem:

File System Loader
==================

The ``FileSystem`` data loader allows for loading images from local file system paths.

.. tip::

    If you don't configure anything, this loader is used by default.


Configuration
-------------

To set this loader for a specific context called ``profile_photos``, use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            profile_photos:
                filesystem: ~

You can configure the ``data_root``, used as the root path to search for images:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        loaders:
            profile_photos:
                filesystem:
                    data_root: "%kernel.root_dir%/../web"
