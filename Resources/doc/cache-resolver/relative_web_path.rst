
.. _cache-resolver-relative-web-path:

Relative Web Path Resolver
=================

This cache resolver (``RelativeWebPathResolver``), as well as
:ref:`web path cache resolver <cache-resolver-web-path>`, enables cache resolution for
local, web-path-based setups. This means images will be cached on your
local filesystem, within the web path of your Symfony application.
The only difference between them is that first one generates relative url paths

Configuration
-------------
.. code-block:: yaml

    liip_imagine:
        resolvers:
           profile_photos:
              relative_web_path:
                # use %kernel.project_dir%/web for Symfony prior to 4.0.0
                web_root: "%kernel.project_dir%/public"
                cache_prefix: "media/cache"

There are several configuration options available:

* ``web_root`` - must be the absolute path to you application's web root. This
  is used to determine where to put generated image files, so that apache
  will pick them up before handing the request to Symfony next time they
  are requested. The default value ends with ``web`` for Symfony prior to
  version ``4.0.0``.
  Default value: ``%kernel.project_dir%/(public|web)``
* ``cache_prefix`` - this is also used in the path for image generation, so
  as to not clutter your web root with cached images. For example by default,
  the images would be written to the ``web/media/cache/`` directory.
  Default value: ``/media/cache``

Usage
-----
After configuring ``RelativeWebPathResolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: profile_photos


Usage on a Specific Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, you can set ``RelativeWebPathResolver`` as the cache resolver for a specific
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
