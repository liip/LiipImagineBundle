
.. _cache-resolver-flysystem:

Flysystem Resolver
==================

The ``FlysystemResolver`` resolver enabled cache resolution using the `Flysystem`_
filesystem abstraction layer.

Dependencies
------------

This cache resolver has a soft dependency on `OneupFlysystemBundle`_, which
can be installed by executing the following command in your project directory:

.. code-block:: bash

    $ composer require oneup/flysystem-bundle

.. note::

    This command requires that `Composer`_ is installed globally, as explained in
    their `installation documentation`_.

Configuration
-------------

The value of the ``filesystem_service`` property must be a service that returns an
instance of ``League\\Flysystem\\Filesystem``.

The following implementation uses `OneupFlysystemBundle`_.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        resolvers:
            profile_photos:
                flysystem:
                    filesystem_service: oneup_flysystem.profile_photos_filesystem
                    root_url:           "http://images.example.com"
                    cache_prefix:       media/cache
                    visibility:         public

    oneup_flysystem:
        adapters:
            profile_photos:
                local:
                    directory:  "path/to/profile/photos"

        filesystems:
            profile_photos:
                adapter: profile_photos

There are several configuration options available:

* ``root_url``: must be a valid url to the target system the flysystem adapter
  points to. This is used to determine how the url should be generated upon request.
  Default value: ``null``
* ``cache_prefix``: this is used for the image path generation. This will be the
  prefix inside the given Flysystem.
  Default value: ``media/cache``
* ``visibility``: one of the two predefined flysystem visibility constants
  (``AdapterInterface::VISIBILITY_PUBLIC`` [``public``] / ``AdapterInterface::VISIBILITY_PRIVATE`` [``private``])
  The visibility is applied, when the objects are stored on a flysystem filesystem.
  You will most probably want to leave the default or explicitly set ``public``.
  Default value: ``public``


Usage
-----

After configuring ``FlysystemResolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: profile_photos


Usage on a Specific Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, you can set it as the cache resolver for a specific filter set using
the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: profile_photos
                filters:
                    # the filter list


.. _`Flysystem`: https://github.com/thephpleague/flysystem
.. _`OneupFlysystemBundle`: https://github.com/1up-lab/OneupFlysystemBundle
.. _`Composer`: https://getcomposer.org/
.. _`installation documentation`: https://getcomposer.org/doc/00-intro.md
