
.. _cache-resolver-cache:

PSR Cache Resolver
==============

A caching resolver based on the PSR-6 cache standard.

The ``PsrCacheResolver`` cannot be used by itself. Instead, it is a "wrapper" for
another resolver.


Dependencies
------------

This cache resolver requires a PSR-6 implementation, e.g. `symfony/cache`, which can be installed
by executing the following command in your project directory:

.. code-block:: bash

    $ composer require symfony/cache

Configuration
-------------

First, you need to setup the required services. In this example we're wrapping an
instance of ``AmazonS3Resolver`` inside this resolver.

See the `Symfony documentation on configuring caching`_ for how to configure the PSR-6 caching implementation.

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.amazon_s3:
            class: AmazonS3
            arguments:
                -
                    key:    "%amazon_s3.key%"
                    secret: "%amazon_s3.secret%"

        acme.imagine.cache.resolver.amazon_s3:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"

        acme.imagine.psr_cache.resolver.amazon_s3.cache:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\PsrCacheResolver
            arguments:
                - "@cache.adapter.memcached"
                - "@acme.imagine.cache.resolver.amazon_s3"
                - { prefix: "amazon_s3" }
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "cached_amazon_s3" }

There are three options available:

* ``global_prefix``: A prefix for all keys within the cache. This is useful to
  avoid colliding keys when using the same cache for different systems.
* ``prefix``: A "local" prefix for this wrapper. This is useful when re-using the
  same resolver for multiple filters. This mainly affects the clear method.
* ``index_key``: The name of the index key being used to save a list of created
  cache keys regarding one image and filter pairing.


Usage
-----

After configuring ``PsrCacheResolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: cached_amazon_s3


Usage on a Specific Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, you can set ``CacheResolver`` as the cache resolver for a specific
filter set using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: cached_amazon_s3
                filters:
                    # the filter list


.. _`Doctrine Cache`: https://github.com/doctrine/cache
.. _`Composer`: https://getcomposer.org/
.. _`installation documentation`: https://getcomposer.org/doc/00-intro.md
.. _`Symfony documentation on configuring caching`: https://symfony.com/doc/current/cache.html#configuring-cache-with-frameworkbundle
