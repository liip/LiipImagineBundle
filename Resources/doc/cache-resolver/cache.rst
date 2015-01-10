CacheResolver
=============

The ``CacheResolver`` requires the `Doctrine Cache`_ library.

This resolver wraps another resolver around a ``Cache``.

Now you can set up the services required; by example using the ``AmazonS3Resolver``.

.. code-block:: yaml

    services:
        acme.amazon_s3:
            class: AmazonS3
            arguments:
                -
                    key: %amazon_s3.key%
                    secret: %amazon_s3.secret%

        acme.imagine.cache.resolver.amazon_s3:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
            arguments:
                - "@acme.amazon_s3"
                - "%amazon_s3.bucket%"

        memcache:
            class: Memcache
            calls:
                - [ 'connect', [ '127.0.0.1', 11211 ] ]

        cache.memcache:
            class: Doctrine\Common\Cache\MemcacheCache
            calls:
                - [ 'setMemcache', [ '@memcache' ] ]

        # The actual
        acme.imagine.cache.resolver.amazon_s3.cache:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\CacheResolver
            arguments:
                - "@cache.memcache"
                - "@acme.imagine.cache.resolver.amazon_s3"
                -
                    prefix: "amazon_s3"
            tags:
                - { name: 'liip_imagine.cache.resolver', resolver: 'cached_amazon_s3' }

There are currently three options available when configuring the ``CacheResolver``:

* ``global_prefix`` A prefix for all keys within the cache. This is useful to
  avoid colliding keys when using the same cache for different systems.
* ``prefix`` A "local" prefix for this wrapper. This is useful when re-using the
  same resolver for multiple filters. This mainly affects the clear method.
* ``index_key`` The name of the index key being used to save a list of created
  cache keys regarding one image and filter pairing.

Now you are ready to use the ``CacheResolver`` by configuring the bundle.
The following example will configure the resolver is default.

.. code-block:: yaml

    liip_imagine:
        cache: 'cached_amazon_s3'

.. _`Doctrine Cache`: https://github.com/doctrine/cache