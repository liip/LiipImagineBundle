
.. _cache-resolver-web-path:

Web Path Resolver
=================

This cache resolver (``WebPathResolver``) enables cache resolution for
local, web-path-based setups. This means images will be cached on your
local filesystem, within the web path of your Symfony application.

The resulting web path is computed by taking a number of factors into
account, including the `request context`_, as provided by the Symfony
HTTP kernel.

.. tip::

    The `request context`_ is most notably used to determine the HTTP
    scheme used for the final URL. If you use a proxy to offload TLS
    traffic decryption and need the resolver to generate secure URLs,
    you will need to appropriately configure Symfony's `trusted proxies`_.
    If you utilize `embedded controllers`_ in your templates, you must
    add ``localhost`` to your trusted proxies configuration.

    Also, the `request context`_ is used to determine the port of the
    resulting URL, should it differ from the standard HTTP/HTTPS (80/443)
    ports.


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


.. _`request context`: http://symfony.com/doc/current/components/http_foundation.html#request
.. _`trusted proxies`: https://symfony.com/doc/current/request/load_balancer_reverse_proxy.html#solution-trusted-proxies
.. _`embedded controllers`: https://symfony.com/doc/current/templating/embedding_controllers.html
