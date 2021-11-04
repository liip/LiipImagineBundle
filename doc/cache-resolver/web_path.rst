
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
                # use %kernel.project_dir%/web for Symfony prior to 4.0.0
                web_root: "%kernel.project_dir%/public"
                cache_prefix: "media/cache"

There are several configuration options available:

* ``web_root`` - must be the absolute path to you application's web root.
  This is used to determine where to put generated image files, so that your
  web server can pick them up instead of forwarding the request to Symfony the
  next time they are requested. The default value is the project directory and
  ``public`` for Symfony >= 4 and ``web`` for old Symfony versions.
  Default value: ``%kernel.project_dir%/(public|web)``
* ``cache_prefix`` - the relative path within the web root where the generated
  images should be cached. This should be a folder to not clutter your web root
  with cached images.
  Default value: ``/media/cache``


Usage
-----

After configuring ``WebPathResolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: profile_photos


Configure resolver on a specific Filter Set
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
