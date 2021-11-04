

Cache Resolvers
===============

A number of built-in cache resolvers are available:

.. toctree::
    :maxdepth: 1
    :glob:

    cache-resolver/*

When the built-in resolvers do not fit your requirements, you can write your own
:doc:`custom cache resolver <cache-resolvers-custom>`.


Configure which Cache Resolver to use
-------------------------------------

The default cache is the :ref:`web path cache resolver <cache-resolver-web-path>`,
which caches images under ``/media/cache/`` within your application web root path.

You can specify the cache resolver to use per individual ``filter_sets`` or globally.
To change the default cache resolver globally, use:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: your_resolver


To change the default configuration, you can redefine the default cache resolver
by explicitly defining a resolver called ``default``:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        resolvers:
            default:
                web_path:
                    cache_prefix: custom_path

To change the cache resolver for a specific ``filter_set``, use the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: your_resolver
                filters:
                    # the filter list
