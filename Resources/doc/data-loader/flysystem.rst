FlysystemLoader
============

This loader lets you load images from `Flysystem`_ filesystem abstraction layer,
which can be used in Symfony projects by installing `OneupFlysystemBundle`_.
This loader is dependent on OneupFlysystemBundle.

Using factory
-------------

.. code-block:: yaml

    liip_imagine:
        loaders:
            profile_photos:
                flysystem:
                    file_system: profile_photos_filesystem

    oneup_flysystem:
        adapters:
            profile_photos:
                local:
                    directory:  "path/to/profile/photos"

        filesystems:
            profile_photos_filesystem:
                adapter: profile_photos


.. _`Flysystem`: https://github.com/thephpleague/flysystem
.. _`OneupFlysystemBundle`: https://github.com/1up-lab/OneupFlysystemBundle