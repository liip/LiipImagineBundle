
.. _cache-resolver-flysystem:

Flysystem Resolver
==================

The ``FlysystemResolver`` resolver enables cache resolution using the `Flysystem`_
filesystem abstraction layer.

Dependencies
------------

This cache resolver uses a ``League\\Flysystem\\Filesystem`` to cache files on any source supported
by `Flysystem`_. Flysystem is provided by the ``league/flysystem`` package, but the easiest way to
set up a service is using one of the flysystem bundles. You can use either `OneupFlysystemBundle`_
or `The League FlysystemBundle`_. Both allow you to define filesystems as services,
LiipImagineBundle does not care which one you use.

To install the `OneupFlysystemBundle`_, run the following composer command:

.. code-block:: bash

    $ composer require oneup/flysystem-bundle

Configuration
-------------

The value of ``filesystem_service`` must be a service id of class ``League\\Flysystem\\Filesystem``.
The service name depends on the naming scheme of the bundle, for `The League FlysystemBundle`_, it
will be different than in the example below.

Using `OneupFlysystemBundle`_, a basic configuration might look as follows:

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
  (``Visibility::PUBLIC`` / ``Visibility::PRIVATE`` or if you use flysystem 1.*
  ``AdapterInterface::VISIBILITY_PUBLIC`` [``public``] / ``AdapterInterface::VISIBILITY_PRIVATE`` [``private``])
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
.. _`The League FlysystemBundle`: https://github.com/thephpleague/flysystem-bundle
