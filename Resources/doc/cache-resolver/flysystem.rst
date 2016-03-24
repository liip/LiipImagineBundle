FlysystemResolver
=================

This resolver lets you load images onto `Flysystem`_ filesystem abstraction layer,
which can be used in Symfony projects by installing, for example, `OneupFlysystemBundle`_.

Value of ``filesystem_service`` property must be a service,
which returns an instance of League\\Flysystem\\Filesystem.

For implementation using `OneupFlysystemBundle`_ look below.

Create resolver
---------------

.. code-block:: yaml

    liip_imagine:
        resolvers:
            profile_photos:
                flysystem:
                    filesystem_service: oneup_flysystem.profile_photos_filesystem
                    root_url: http://images.example.com
                    cache_prefix: media/cache
    oneup_flysystem:
        adapters:
            profile_photos:
                local:
                    directory:  "path/to/profile/photos"

        filesystems:
            profile_photos:
                adapter: profile_photos

There are several configuration options available:

* ``root_url`` - must be a valid url to the target system the flysystem adapter
  points to. This is used to determine how the url should be generated upon request.
  Default value: ``null``
* ``cache_prefix`` - this is used for the image path generation. This will be the
  prefix inside the given Flysystem.
  Default value: ``media/cache``

Usage
-----

.. code-block:: yaml

    liip_imagine:
        cache: profile_photos

Usage on a specific filter
--------------------------

.. code-block:: yaml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: profile_photos
                quality: 75
                filters:
                    thumbnail: { size: [120, 90], mode: outbound }
