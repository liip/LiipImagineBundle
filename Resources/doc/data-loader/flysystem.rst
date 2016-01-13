FlysystemLoader
===============

This loader lets you load images from `Flysystem`_ filesystem abstraction layer,
which can be used in Symfony projects by installing, for example, `OneupFlysystemBundle`_.

Value of ``filesystem_service`` property must be a service,
which returns an instance of League\\Flysystem\\Filesystem.

For implementation using `OneupFlysystemBundle`_ look below.

Using factory
-------------

.. code-block:: yaml

    liip_imagine:
        loaders:
            profile_photos:
                flysystem:
                    filesystem_service: oneup_flysystem.profile_photos_filesystem

    oneup_flysystem:
        adapters:
            profile_photos:
                local:
                    directory:  "path/to/profile/photos"

        filesystems:
            profile_photos:
                adapter: profile_photos


.. _`Flysystem`: https://github.com/thephpleague/flysystem
.. _`OneupFlysystemBundle`: https://github.com/1up-lab/OneupFlysystemBundle
