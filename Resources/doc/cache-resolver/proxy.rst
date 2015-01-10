ProxyResolver
=============

The ProxyResolver is a ``decorator`` for every other Resolver

This Resolver adds the possibility to use Proxy Hosts for your Assets. If no
Proxy Domains are set, it behaves like the  underlying ``Resolver``.

Set Proxy Domains
-----------------

In order to use this Resolver you must create a Service and inject some domains
and your underlying Resolver

.. code-block:: yaml

    services:
        acme.imagine.cache.resolver.proxy:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver
            arguments:
                - "@acme.imagine.cache.resolver.amazon_s3"
                - [ 'http://images0.domain.com', 'http://images1.domain.com','http://images2.domain.com' ]
            tags:
                - { name: 'liip_imagine.cache.resolver', resolver: 'proxy' }


Now your Resolver would generate ``http://images0.domain.com/thumbs/article_thumb/foo.jpg``
instead of the original path from the underlying Resolver
``bucket.s3.awsamazoncloud.com/thumbs/article_thumb/foo.jpg`` for every relevant
Action.
