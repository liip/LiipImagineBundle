Filesystem
==========

Using factory
-------------

.. code-block:: yaml

    liip_imagine:
        loaders:
            profile_photos:
                filesystem: ~

If you don't configure anything, this loader is set by default. You can 
also configure a root dir where to look for the origin images:

.. code-block:: yaml

    liip_imagine:
        loaders:
            profile_photos:
                filesystem:
                    data_root: %kernel.root_dir%/../web
