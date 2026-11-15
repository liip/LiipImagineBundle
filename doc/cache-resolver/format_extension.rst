
.. _cache-resolver-format-extension:

FormatExtensionResolver
=======================

The ``FormatExtensionResolver`` cannot be used by itself. Instead, it is a "decorator" for
another resolver. It adds the ability to set the correct file extension when a filter converted the image format.

For example, with a filter set using ``format: webp``, the source image ``image.png`` is
stored and served as ``image.webp`` instead of a WebP image named ``image.png``.

Configuration
-------------

Define a resolver with the ``format_extension`` key, and give it the name of the configured
resolver to decorate. In this example, it decorates the ``default`` resolver, which is the
:ref:`Web Path Resolver <cache-resolver-web-path>` unless you configured another one:

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        resolvers:
            format_extension:
                format_extension:
                    resolver: default

The decorated resolver must be defined in the ``resolvers`` section. The ``default``
resolver is always available, as the bundle defines it when it is not configured.

Usage
-----

After configuring ``FormatExtensionResolver``, you can set it as the default cache resolver
for ``LiipImagineBundle`` using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        cache: format_extension


Usage on a Specific Filter
~~~~~~~~~~~~~~~~~~~~~~~~~~

Alternatively, you can set ``FormatExtensionResolver`` as the cache resolver for a specific
filter set using the following configuration.

.. code-block:: yaml

    # app/config/config.yml

    liip_imagine:
        filter_sets:
            cache: ~
            my_thumb:
                cache: format_extension
                format: webp
                filters:
                    # the filter list

.. caution::

    Changing the cache resolver changes the paths of the cached images. Images already
    stored under their original extension are not found anymore and are generated again.

.. caution::

    Do not combine this resolver with the ``webp.generate`` option on filter sets that
    define a ``format`` other than ``webp``: with ``format: jpeg``, the WebP variant
    ``image.png.webp`` would be stored as ``image.png.jpeg``, a WebP image with the
    extension of the format.

Usage with a Custom Service
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Before the ``format_extension`` resolver configuration was available, the resolver had to be
defined as a service. This still works, for example to decorate a resolver that is defined as
a service instead of in the ``resolvers`` section:

.. code-block:: yaml

    # app/config/services.yml

    services:
        acme.imagine.cache.format_extension:
            class: Liip\ImagineBundle\Imagine\Cache\Resolver\FormatExtensionResolver
            arguments:
                - "@acme.imagine.cache.resolver.custom"
                - "@liip_imagine.filter.configuration"
            tags:
                - { name: "liip_imagine.cache.resolver", resolver: "format_extension" }
