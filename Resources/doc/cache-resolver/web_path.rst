WebPathResolver
===============

Create resolver
---------------

.. code-block:: yaml

    liip_imagine:
        resolvers:
           profile_photos:
              web_path:
                web_root: %kernel.root_dir%/../web
                cache_prefix: media/cache

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

.. code-block:: yaml

    liip_imagine:
        cache: profile_photos
