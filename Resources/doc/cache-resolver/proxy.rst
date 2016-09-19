
.. _cache-resolver-proxy:

ProxyResolver
=============

The ``ProxyResolver`` cannot be used by itself. Instead, it is a "decorator" for
another resolver. It add the ability to use "Proxy Hosts" for your assets. If no
"Proxy Domains" are set, it behaves like the  underlying cache resolver.

Prerequisites
-------------

Create Service
~~~~~~~~~~~~~~

To use this cache resolver, you must first define the cache resolver it will decorate.
In this example, we will use the :ref:`AWS Cache Resolver <cache-resolver-aws-s3>`.

Next, we need to define a service for this cache resolver and inject an array of domains
and the cache resolver service to decorate.

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.imagine.cache.resolver.proxy:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver
            arguments:
                - "@acme.imagine.cache.resolver.amazon_s3"
                - [ "http://images0.domain.com", "http://images1.domain.com", "http://images2.domain.com" ]
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "proxy" }

With this configuration, the cache resolver will generate paths such as
``//images0.domain.com/.../image.jpg``, ``//images1.domain.com/.../image.jpg``, and
``//images2.domain.com/.../image.jpg`` (instead of the original path
returned from the decorated cache resolver, in this example using AWS,
``//bucket.s3.awsamazoncloud.com/.../image.jpg``).

Usage
-----

After configuring ``ProxyResolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: proxy


Usage on a Specific Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, you can set ``ProxyResolver`` as the cache resolver for a specific
filter set using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: proxy
                filters:
                    # the filter list
