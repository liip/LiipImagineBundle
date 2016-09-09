
.. _cache-resolver-web-path:

Web Path Resolver
=================

The ``WebPathResolver`` resolver enabled cache resolution using the
web path of your application.


Configuration
-------------

.. code-block:: yaml

    liip_imagine:
        resolvers:
           profile_photos:
              web_path:
                web_root: "%kernel.root_dir%/../web"
                cache_prefix: "media/cache"

There are several configuration options available:

* ``web_root`` - must be the absolute path to you application's web root. This
  is used to determine where to put generated image files, so that apache
  will pick them up before handing the request to Symfony2 next time they
  are requested.
  Default value: ``%kernel.root_dir%/../web``
* ``cache_prefix`` - this is also used in the path for image generation, so
  as to not clutter your web root with cached images. For example by default,
  the images would be written to the ``web/media/cache/`` directory.
  Default value: ``/media/cache``


Usage
-----

After configuring ``WebPathResolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: profile_photos


Usage on a Specific Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, you can set ``WebPathResolver`` as the cache resolver for a specific
filter set using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: profile_photos
                filters:
                    # the filter list
